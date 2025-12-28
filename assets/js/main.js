// Initialize AOS (Animate On Scroll)
AOS.init({
    duration: 1000,
    once: true
});

// Navbar scroll behavior
window.addEventListener('scroll', function() {
    const navbar = document.querySelector('.navbar');
    if (window.scrollY > 50) {
        navbar.classList.add('shadow-sm');
    } else {
        navbar.classList.remove('shadow-sm');
    }
});

// Smooth scrolling for anchor links
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function(e) {
        e.preventDefault();
        const target = document.querySelector(this.getAttribute('href'));
        window.scrollTo({
            top: target.offsetTop - 75,
            behavior: 'smooth'
        });
    });
});

// Form submission handler
const contactForm = document.querySelector('form.contact-form');
if (contactForm) {
    contactForm.addEventListener('submit', function(e) {
        // You can add form validation here if needed
        // This is just a basic example
        const name = this.querySelector('[name="name"]').value;
        const email = this.querySelector('[name="email"]').value;
        const phone = this.querySelector('[name="phone"]').value;
        const message = this.querySelector('[name="message"]').value;

        if (!name || !email || !phone || !message) {
            e.preventDefault();
            alert('Mohon lengkapi semua field yang diperlukan');
            return false;
        }
        // Let the form submit normally if validation passes
    });
}

// Scroll to contact section if status parameter exists
window.addEventListener('DOMContentLoaded', function() {
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.has('status')) {
        // Wait a bit for page to fully load
        setTimeout(function() {
            const contactSection = document.querySelector('#contact');
            if (contactSection) {
                window.scrollTo({
                    top: contactSection.offsetTop - 75,
                    behavior: 'smooth'
                });
            }
        }, 100);
    }
});

// Dark Mode Toggle
(function() {
    const themeToggle = document.getElementById('themeToggle');
    const themeToggleMobile = document.getElementById('themeToggleMobile');
    const html = document.documentElement;
    
    // Get saved theme preference or default to light
    const currentTheme = localStorage.getItem('theme') || 'light';
    
    // Apply saved theme
    if (currentTheme === 'dark') {
        html.setAttribute('data-theme', 'dark');
        updateToggleIcons(true);
    } else {
        html.setAttribute('data-theme', 'light');
        updateToggleIcons(false);
    }
    
    // Function to update toggle icons (both desktop and mobile)
    function updateToggleIcons(isDark) {
        const toggles = [themeToggle, themeToggleMobile];
        toggles.forEach(toggle => {
            if (toggle) {
                const icon = toggle.querySelector('i');
                if (icon) {
                    if (isDark) {
                        icon.classList.remove('fa-sun');
                        icon.classList.add('fa-moon');
                    } else {
                        icon.classList.remove('fa-moon');
                        icon.classList.add('fa-sun');
                    }
                }
            }
        });
    }
    
    // Function to handle theme toggle
    function toggleTheme() {
        const currentTheme = html.getAttribute('data-theme');
        const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
        
        html.setAttribute('data-theme', newTheme);
        localStorage.setItem('theme', newTheme);
        updateToggleIcons(newTheme === 'dark');
    }
    
    // Add event listeners to both toggle buttons
    if (themeToggle) {
        themeToggle.addEventListener('click', toggleTheme);
    }
    
    if (themeToggleMobile) {
        themeToggleMobile.addEventListener('click', toggleTheme);
    }
    
    // Detect system preference on first visit (if no saved preference)
    if (!localStorage.getItem('theme')) {
        const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
        if (prefersDark) {
            html.setAttribute('data-theme', 'dark');
            localStorage.setItem('theme', 'dark');
            updateToggleIcons(true);
        }
    }
})();