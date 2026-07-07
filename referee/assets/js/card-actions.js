/**
 * Enhanced Card Management System for Referees
 * Handles yellow and red card issuance with proper validation and UI feedback
 */

class CardManager {
    constructor() {
        this.init();
    }

    init() {
        this.bindEvents();
        this.createConfirmationModal();
    }

    bindEvents() {
        // Handle card button clicks
        document.addEventListener('click', (e) => {
            if (e.target.classList.contains('btn-card')) {
                e.preventDefault();
                this.handleCardClick(e.target);
            }
        });
    }

    handleCardClick(button) {
        if (button.disabled) return;

        const form = button.closest('form');
        const playerId = form.querySelector('input[name="player_id"]').value;
        const cardType = form.querySelector('input[name="card"]').value;
        const playerName = this.getPlayerName(button);

        // Show confirmation dialog
        this.showConfirmation(playerId, cardType, playerName, form);
    }

    getPlayerName(button) {
        // Try to find player name from the surrounding elements
        const playerContainer = button.closest('.player-info, .player-card, [data-player]');
        if (playerContainer) {
            const nameElement = playerContainer.querySelector('.player-name, [data-player-name]');
            if (nameElement) {
                return nameElement.textContent.trim();
            }
        }
        return 'Player';
    }

    showConfirmation(playerId, cardType, playerName, form) {
        const modal = document.getElementById('cardConfirmationModal');
        const title = modal.querySelector('.confirmation-title');
        const message = modal.querySelector('.confirmation-message');
        const confirmBtn = modal.querySelector('.btn-confirm');

        // Set modal content based on card type
        if (cardType === 'yellow') {
            title.textContent = 'Issue Yellow Card';
            message.innerHTML = `Are you sure you want to issue a <strong>yellow card</strong> to <strong>${playerName}</strong>?<br><small class="text-muted">Note: A second yellow card will result in an automatic red card.</small>`;
            confirmBtn.className = 'btn-confirm yellow-confirm';
            confirmBtn.textContent = 'Issue Yellow Card';
        } else if (cardType === 'red') {
            title.textContent = 'Issue Red Card';
            message.innerHTML = `Are you sure you want to issue a <strong>red card</strong> to <strong>${playerName}</strong>?<br><small class="text-muted">This will result in immediate ejection from the match.</small>`;
            confirmBtn.className = 'btn-confirm red-confirm';
            confirmBtn.textContent = 'Issue Red Card';
        }

        // Store form reference for submission
        modal.dataset.formData = JSON.stringify({
            playerId: playerId,
            cardType: cardType,
            formAction: form.action
        });

        // Show modal
        modal.classList.add('show');
    }

    hideConfirmation() {
        const modal = document.getElementById('cardConfirmationModal');
        modal.classList.remove('show');
    }

    async submitCard(playerId, cardType, formAction) {
        try {
            // Show loading state
            this.showLoading();

            const formData = new FormData();
            formData.append('player_id', playerId);
            formData.append('card', cardType);
            formData.append('ajax', '1');
            
            // Add match context if available
            const matchId = this.getCurrentMatchId();
            if (matchId) {
                formData.append('match_id', matchId);
            }

            const response = await fetch(formAction, {
                method: 'POST',
                body: formData
            });

            const result = await response.json();

            if (result.success) {
                this.showSuccess(result.message);
                this.updatePlayerCardDisplay(playerId, result.card_type);
                this.refreshCardButtons(playerId);
            } else {
                this.showError(result.message);
            }

        } catch (error) {
            console.error('Card submission error:', error);
            this.showError('An error occurred while issuing the card. Please try again.');
        } finally {
            this.hideLoading();
            this.hideConfirmation();
        }
    }

    getCurrentMatchId() {
        // Try to get match ID from various sources
        const matchIdElement = document.querySelector('[data-match-id]');
        if (matchIdElement) {
            return matchIdElement.dataset.matchId;
        }
        
        // Check URL parameters
        const urlParams = new URLSearchParams(window.location.search);
        return urlParams.get('match_id');
    }

    updatePlayerCardDisplay(playerId, cardType) {
        // Find and update the player's card display
        const playerElements = document.querySelectorAll(`[data-player-id="${playerId}"]`);
        
        playerElements.forEach(element => {
            const cardContainer = element.querySelector('.player-cards, .card-display');
            if (cardContainer) {
                // Add new card indicator
                const cardElement = document.createElement('span');
                cardElement.className = `card ${cardType === 'double_yellow' ? 'red' : cardType}`;
                cardContainer.appendChild(cardElement);
            }
        });
    }

    refreshCardButtons(playerId) {
        // Refresh the state of card buttons for the player
        const playerForms = document.querySelectorAll(`form input[name="player_id"][value="${playerId}"]`);
        
        playerForms.forEach(input => {
            const form = input.closest('form');
            const cardType = form.querySelector('input[name="card"]').value;
            const button = form.querySelector('.btn-card');
            
            // Disable buttons based on card logic
            if (cardType === 'yellow') {
                // Check if player now has 2 yellows or a red
                const cardDisplay = form.closest('.player-info, .player-card').querySelector('.card-display, .player-cards');
                const redCards = cardDisplay ? cardDisplay.querySelectorAll('.card.red').length : 0;
                const yellowCards = cardDisplay ? cardDisplay.querySelectorAll('.card.yellow').length : 0;
                
                if (redCards > 0 || yellowCards >= 2) {
                    button.disabled = true;
                }
            } else if (cardType === 'red') {
                // Disable red button if player already has a red
                const cardDisplay = form.closest('.player-info, .player-card').querySelector('.card-display, .player-cards');
                const redCards = cardDisplay ? cardDisplay.querySelectorAll('.card.red').length : 0;
                
                if (redCards > 0) {
                    button.disabled = true;
                }
            }
        });
    }

    createConfirmationModal() {
        // Create modal if it doesn't exist
        if (document.getElementById('cardConfirmationModal')) return;

        const modal = document.createElement('div');
        modal.id = 'cardConfirmationModal';
        modal.className = 'card-confirmation';
        modal.innerHTML = `
            <div class="confirmation-dialog">
                <h3 class="confirmation-title">Confirm Card</h3>
                <div class="confirmation-message">Are you sure?</div>
                <div class="confirmation-buttons">
                    <button type="button" class="btn-confirm" onclick="cardManager.confirmCard()">Confirm</button>
                    <button type="button" class="btn-cancel" onclick="cardManager.hideConfirmation()">Cancel</button>
                </div>
            </div>
        `;

        document.body.appendChild(modal);

        // Close modal when clicking outside
        modal.addEventListener('click', (e) => {
            if (e.target === modal) {
                this.hideConfirmation();
            }
        });
    }

    confirmCard() {
        const modal = document.getElementById('cardConfirmationModal');
        const formData = JSON.parse(modal.dataset.formData);
        
        this.submitCard(formData.playerId, formData.cardType, formData.formAction);
    }

    showLoading() {
        // Show loading indicator
        const modal = document.getElementById('cardConfirmationModal');
        const dialog = modal.querySelector('.confirmation-dialog');
        dialog.innerHTML = `
            <div class="loading-spinner">
                <div class="spinner"></div>
                <p>Processing card...</p>
            </div>
        `;
    }

    hideLoading() {
        // Loading will be hidden when modal is closed
    }

    showSuccess(message) {
        this.showNotification(message, 'success');
    }

    showError(message) {
        this.showNotification(message, 'error');
    }

    showNotification(message, type) {
        // Create and show notification
        const notification = document.createElement('div');
        notification.className = `notification ${type}`;
        notification.innerHTML = `
            <div class="notification-content">
                <span class="notification-icon">${type === 'success' ? '✓' : '✗'}</span>
                <span class="notification-message">${message}</span>
            </div>
        `;

        document.body.appendChild(notification);

        // Show notification
        setTimeout(() => notification.classList.add('show'), 100);

        // Hide and remove notification
        setTimeout(() => {
            notification.classList.remove('show');
            setTimeout(() => notification.remove(), 300);
        }, 4000);
    }
}

// Initialize card manager when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    window.cardManager = new CardManager();
});

// Add notification styles
const notificationStyles = `
    .notification {
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 16px 20px;
        border-radius: 8px;
        color: white;
        font-weight: 500;
        z-index: 1001;
        transform: translateX(100%);
        transition: transform 0.3s ease;
        max-width: 400px;
    }
    
    .notification.show {
        transform: translateX(0);
    }
    
    .notification.success {
        background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
    }
    
    .notification.error {
        background: linear-gradient(135deg, #dc3545 0%, #fd7e14 100%);
    }
    
    .notification-content {
        display: flex;
        align-items: center;
        gap: 12px;
    }
    
    .notification-icon {
        font-size: 18px;
        font-weight: bold;
    }
    
    .loading-spinner {
        text-align: center;
        padding: 20px;
    }
    
    .spinner {
        width: 40px;
        height: 40px;
        border: 4px solid #f3f3f3;
        border-top: 4px solid #007bff;
        border-radius: 50%;
        animation: spin 1s linear infinite;
        margin: 0 auto 16px;
    }
    
    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
`;

// Inject styles
const styleSheet = document.createElement('style');
styleSheet.textContent = notificationStyles;
document.head.appendChild(styleSheet);
