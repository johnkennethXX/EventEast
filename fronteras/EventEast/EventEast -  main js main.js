// Main JavaScript for EventEast
document.addEventListener('DOMContentLoaded', function() {
    
    // Auto-hide alerts after 5 seconds
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.transition = 'opacity 0.5s ease';
            alert.style.opacity = '0';
            setTimeout(() => alert.remove(), 500);
        }, 5000);
    });
    
    // Smooth scroll for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });
    
    // Search form real-time filtering (AJAX-like behavior)
    const searchInput = document.querySelector('input[name="search"]');
    if (searchInput) {
        let searchTimeout;
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                // Visual feedback while searching
                const eventCards = document.querySelectorAll('.event-card');
                const searchTerm = this.value.toLowerCase();
                
                eventCards.forEach(card => {
                    const title = card.querySelector('h3').textContent.toLowerCase();
                    const description = card.querySelector('.event-description').textContent.toLowerCase();
                    
                    if (title.includes(searchTerm) || description.includes(searchTerm) || searchTerm === '') {
                        card.style.display = 'block';
                        card.style.animation = 'fadeIn 0.3s ease';
                    } else {
                        card.style.display = 'none';
                    }
                });
            }, 300);
        });
    }
    
    // Event card hover effects
    const eventCards = document.querySelectorAll('.event-card');
    eventCards.forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-8px)';
        });
        
        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
        });
    });
    
    // Confirm delete actions
    const deleteButtons = document.querySelectorAll('[onclick*="confirm"]');
    deleteButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            if (!confirm('Are you sure you want to perform this action? This cannot be undone.')) {
                e.preventDefault();
                return false;
            }
        });
    });
    
    // Form submit loading state
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', function() {
            const submitButton = this.querySelector('button[type="submit"]');
            if (submitButton) {
                submitButton.disabled = true;
                submitButton.textContent = 'Processing...';
                submitButton.style.opacity = '0.6';
                submitButton.style.cursor = 'not-allowed';
            }
        });
    });
    
    // Dynamic event counter
    updateEventCounters();
    setInterval(updateEventCounters, 60000); // Update every minute
    
    // Image lazy loading
    const images = document.querySelectorAll('img[data-src]');
    const imageObserver = new IntersectionObserver((entries, observer) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const img = entry.target;
                img.src = img.dataset.src;
                img.removeAttribute('data-src');
                observer.unobserve(img);
            }
        });
    });
    
    images.forEach(img => imageObserver.observe(img));
    
    // Responsive table for mobile
    makeTablesResponsive();
    
    // Add animations
    addScrollAnimations();
});

// Update event counters dynamically
function updateEventCounters() {
    const eventCards = document.querySelectorAll('.event-card');
    eventCards.forEach(card => {
        const eventId = card.dataset.eventId;
        if (eventId) {
            // In a real application, this would fetch updated data from the server
            // For now, we'll just add visual feedback
            const attendeeInfo = card.querySelector('.detail-item:has(.icon:contains("👥"))');
            if (attendeeInfo) {
                attendeeInfo.style.transition = 'all 0.3s ease';
            }
        }
    });
}

// Make tables responsive on mobile
function makeTablesResponsive() {
    const tables = document.querySelectorAll('table');
    tables.forEach(table => {
        if (window.innerWidth < 768) {
            const thead = table.querySelector('thead');
            if (thead) {
                thead.style.display = 'none';
            }
            
            const rows = table.querySelectorAll('tbody tr');
            rows.forEach(row => {
                row.style.display = 'block';
                row.style.marginBottom = '1rem';
                row.style.border = '1px solid var(--border-color)';
                row.style.borderRadius = '6px';
                row.style.padding = '1rem';
                
                const cells = row.querySelectorAll('td');
                cells.forEach((cell, index) => {
                    cell.style.display = 'block';
                    cell.style.textAlign = 'left';
                    cell.style.borderBottom = '1px solid var(--border-color)';
                    cell.style.padding = '0.5rem 0';
                });
            });
        }
    });
}

// Add scroll animations
function addScrollAnimations() {
    const animateElements = document.querySelectorAll('.event-card, .stat-card, .form-container');
    
    const animateObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
            }
        });
    }, { threshold: 0.1 });
    
    animateElements.forEach(element => {
        element.style.opacity = '0';
        element.style.transform = 'translateY(20px)';
        element.style.transition = 'all 0.6s ease';
        animateObserver.observe(element);
    });
}

// Utility function to format dates
function formatDate(dateString) {
    const options = { year: 'numeric', month: 'long', day: 'numeric' };
    return new Date(dateString).toLocaleDateString('en-US', options);
}

// Utility function to calculate time until event
function getTimeUntilEvent(eventDate, eventTime) {
    const eventDateTime = new Date(`${eventDate} ${eventTime}`);
    const now = new Date();
    const diff = eventDateTime - now;
    
    if (diff < 0) return 'Event has passed';
    
    const days = Math.floor(diff / (1000 * 60 * 60 * 24));
    const hours = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
    
    if (days > 0) return `${days} day${days !== 1 ? 's' : ''} away`;
    if (hours > 0) return `${hours} hour${hours !== 1 ? 's' : ''} away`;
    return 'Starting soon!';
}

// Add CSS for animations
const style = document.createElement('style');
style.textContent = `
    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: scale(0.95);
        }
        to {
            opacity: 1;
            transform: scale(1);
        }
    }
    
    @keyframes slideIn {
        from {
            opacity: 0;
            transform: translateX(-20px);
        }
        to {
            opacity: 1;
            transform: translateX(0);
        }
    }
    
    .page-actions {
        margin-bottom: 2rem;
    }
    
    .fade-in {
        animation: fadeIn 0.5s ease;
    }
    
    .slide-in {
        animation: slideIn 0.5s ease;
    }
`;
document.head.appendChild(style);