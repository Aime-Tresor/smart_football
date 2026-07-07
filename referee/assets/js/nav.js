document.addEventListener('DOMContentLoaded', function() {
    // Get the current page path
    const currentPath = window.location.pathname;
    
    // Find all navigation links
    const navLinks = document.querySelectorAll('.nav-link');
    
    // Remove any existing active classes
    navLinks.forEach(link => {
        link.classList.remove('active');
        
        // Check if the link's href matches the current path
        if (link.getAttribute('href').includes(currentPath)) {
            link.classList.add('active');
        }
    });
});
