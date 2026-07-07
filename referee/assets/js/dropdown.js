document.addEventListener('DOMContentLoaded', function() {
    // Get all dropdowns on the page
    const dropdowns = document.querySelectorAll('.dropdown');

    // Add click event listener to each dropdown toggle
    dropdowns.forEach(dropdown => {
        const toggle = dropdown.querySelector('.dropdown-toggle');
        
        toggle.addEventListener('click', (e) => {
            e.stopPropagation(); // Prevent event from bubbling up
            
            // Close all other dropdowns
            dropdowns.forEach(d => {
                if (d !== dropdown) {
                    d.classList.remove('active');
                }
            });
            
            // Toggle current dropdown
            dropdown.classList.toggle('active');
        });
    });

    // Close dropdown when clicking outside
    document.addEventListener('click', (e) => {
        if (!e.target.closest('.dropdown')) {
            dropdowns.forEach(dropdown => {
                dropdown.classList.remove('active');
            });
        }
    });
});
