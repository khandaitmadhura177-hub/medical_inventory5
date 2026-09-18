/**
 * MIMS Interactive Scripting
 * Theme: Dark Rose Gold & Cyan Premium
 */

document.addEventListener("DOMContentLoaded", function() {
    
    // 1. Smooth Page Entrance
    // Targets the main wrappers to provide a subtle lift effect on load
    const mainContent = document.querySelector('.container, .main-content, .invoice-card');
    if (mainContent) {
        mainContent.classList.add('animate__animated', 'animate__fadeInUp', 'animate__faster');
    }

    // 2. Premium Auto-hide Alerts (Cyan Glow Theme)
    // Automatically fades out notifications after the user has read them
    const alerts = document.querySelectorAll('.alert, .global-alert');
    alerts.forEach(function(alert) {
        // Wait 4 seconds then fade
        setTimeout(function() {
            alert.style.transition = "all 0.8s cubic-bezier(0.4, 0, 0.2, 1)";
            alert.style.opacity = "0";
            alert.style.transform = "translateY(-20px)";
            
            setTimeout(() => {
                if(alert.parentNode) alert.remove();
            }, 800); 
        }, 4000);
    });

    // 3. Delete Confirmation Logic
    // Traditional confirmation but can be styled via CSS
    window.confirmDelete = function(message = "Are you sure? This medical record will be permanently purged.") {
        return confirm(message);
    };

    // 4. Interactive Search Glow (Cyan Accent)
    // Adds a medical "scanning" glow effect when the user interacts with search
    const searchInput = document.querySelector('input[type="search"], input[name="search"]');
    if(searchInput) {
        const parent = searchInput.parentElement;
        
        searchInput.addEventListener('focus', function() {
            parent.style.boxShadow = "0 0 20px rgba(0, 229, 255, 0.15)";
            parent.style.borderColor = "var(--accent-cyan)";
            parent.style.transition = "all 0.4s ease";
        });
        
        searchInput.addEventListener('blur', function() {
            parent.style.boxShadow = "none";
            parent.style.borderColor = "rgba(255,255,255,0.1)";
        });
    }

    // 5. Card Hover Sound/Haptic (Optional Enhancement)
    // Adds a slight lift to medicine cards when hovered
    const cards = document.querySelectorAll('.card');
    cards.forEach(card => {
        card.addEventListener('mouseenter', () => {
            card.style.transform = "translateY(-5px)";
            card.style.transition = "transform 0.3s ease";
        });
        card.addEventListener('mouseleave', () => {
            card.style.transform = "translateY(0)";
        });
    });
});