document.addEventListener('DOMContentLoaded', function() {
    // Handle Give Cards navigation
    const giveCardsNav = document.querySelector('.give-cards-nav');
    giveCardsNav.addEventListener('click', function(e) {
        e.preventDefault();
        // Remove active class from other nav items
        document.querySelectorAll('.nav-link').forEach(link => link.classList.remove('active'));
        // Add active class to Give Cards nav
        this.classList.add('active');
        
        // TODO: Implement card giving interface
        console.log('Opening card giving interface');
    });    // Dropdown functionality
    const dropdowns = document.querySelectorAll('.dropdown');
    
    dropdowns.forEach(dropdown => {
        const dropdownToggle = dropdown.querySelector('.dropdown-toggle');
        const dropdownMenu = dropdown.querySelector('.dropdown-menu');

        // Toggle dropdown on click
        dropdownToggle.addEventListener('click', function(e) {
            e.stopPropagation();
            e.preventDefault();
            
            // Close all other dropdowns
            dropdowns.forEach(d => {
                if (d !== dropdown) {
                    d.classList.remove('active');
                }
            });
            
            // Toggle current dropdown
            dropdown.classList.toggle('active');
        });

        // Handle dropdown item clicks
        const dropdownItems = dropdown.querySelectorAll('.dropdown-item');
        dropdownItems.forEach(item => {
            item.addEventListener('click', function(e) {
                e.preventDefault();
                const action = this.textContent.trim().toLowerCase();
                
                if (action === 'logout') {
                    console.log('Logging out...');
                    // Add your logout logic here
                } else if (action === 'profile') {
                    console.log('Opening profile...');
                    // Add your profile navigation logic here
                }
                
                dropdown.classList.remove('active');
            });
        });
    });

    // Close dropdowns when clicking outside
    document.addEventListener('click', function(e) {
        dropdowns.forEach(dropdown => {
            if (!dropdown.contains(e.target)) {
                dropdown.classList.remove('active');
            }
        });
    });

    // Handle navigation clicks
    const navLinks = document.querySelectorAll('.nav-link');
    navLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            navLinks.forEach(l => l.classList.remove('active'));
            this.classList.add('active');
        });
    });

    // Add click handlers for cards
    const matchCards = document.querySelectorAll('.match-card');
    matchCards.forEach(card => {
        card.addEventListener('click', function() {
            console.log('Match card clicked:', this.querySelector('span').textContent);
        });
    });

    const refereeCards = document.querySelectorAll('.referee-card');
    refereeCards.forEach(card => {
        card.addEventListener('click', function() {
            console.log('Referee card clicked:', this.querySelector('h4').textContent);
        });
    });    // Handle Give Cards button clicks
    const giveCardButtons = document.querySelectorAll('.give-card-btn');
    giveCardButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const refereeCard = this.closest('.referee-card');
            const refereeName = refereeCard.querySelector('h4').textContent;
            
            // Add a ripple effect
            const ripple = document.createElement('div');
            ripple.className = 'ripple';
            this.appendChild(ripple);
            
            // Get button position
            const rect = this.getBoundingClientRect();
            const x = e.clientX - rect.left;
            const y = e.clientY - rect.top;
            
            ripple.style.left = x + 'px';
            ripple.style.top = y + 'px';
            
            // Remove ripple after animation
            setTimeout(() => ripple.remove(), 1000);

            console.log('Give cards clicked for referee:', refereeName);
            // TODO: Implement card giving functionality - will be added later
        });
    });

    // Update live matches
    function updateLiveMatches() {
        const liveStatuses = document.querySelectorAll('.status-live');
        liveStatuses.forEach(status => {
            status.style.animation = 'pulse 2s infinite';
        });
    }

    updateLiveMatches();

    // Auto-refresh simulation
    setInterval(() => {
        const now = new Date();
        const timeElements = document.querySelectorAll('.assignment-time');
        // This would typically update with real data from an API
        console.log('Auto-refresh triggered at:', now.toLocaleTimeString());
    }, 30000); // Every 30 seconds
});