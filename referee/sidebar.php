<!-- Sidebar Component -->
<nav class="sidebar">
    <div class="logo">
        <div class="logo-icon">⚽</div>
    </div>    <ul class="nav-menu">
        <li class="nav-item">
            <a href="index.php" class="nav-link">
                <svg class="nav-icon" viewBox="0 0 24 24">
                    <path d="M10 20v-6h4v6h5v-8h3L12 3 2 12h3v8z"/>
                </svg>
                Home
            </a>
        </li>
        <li class="nav-item">
            <a href="matches.php" class="nav-link">
                <svg class="nav-icon" viewBox="0 0 24 24">
                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                </svg>
                Matches
            </a>
        </li>
        
        
        
        <li class="nav-item">            <a href="cads.php" class="nav-link">
                <svg class="nav-icon" viewBox="0 0 24 24">
                    <path d="M4 17.2l4-4 4 4 8-8-1.4-1.4-6.6 6.6-4-4-4 4V17.2zM20 2H4C2.9 2 2 2.9 2 4v16c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zm0 18H4V4h16v16z"/>
                </svg>
                Give Cards
            </a>
        </li>

        <!-- Logout Section -->
        <li class="nav-item nav-logout">
            <a href="logout.php" class="nav-link logout-link" onclick="return confirm('Are you sure you want to logout?')">
                <svg class="nav-icon" viewBox="0 0 24 24">
                    <path d="M17 7l-1.41 1.41L18.17 11H8v2h10.17l-2.58 2.58L17 17l5-5zM4 5h8V3H4c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h8v-2H4V5z"/>
                </svg>
                Logout
            </a>
        </li>
    </ul>
</nav>

<style>
/* Logout styling for sidebar */
.nav-logout {
    margin-top: auto;
    border-top: 1px solid rgba(255, 255, 255, 0.1);
    padding-top: 15px;
}

.logout-link {
    color: #ff6b6b !important;
    transition: all 0.3s ease;
}

.logout-link:hover {
    background-color: rgba(255, 107, 107, 0.1) !important;
    color: #ff5252 !important;
}

.logout-link .nav-icon {
    fill: #ff6b6b;
}

.logout-link:hover .nav-icon {
    fill: #ff5252;
}
</style>
