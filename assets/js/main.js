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