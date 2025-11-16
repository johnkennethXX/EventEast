// Real-time form validation

document.addEventListener('DOMContentLoaded', function() {
    // Login Form Validation
    const loginForm = document.getElementById('loginForm');
    if (loginForm) {
        loginForm.addEventListener('submit', function(e) {
            const username = document.getElementById('username').value.trim();
            const password = document.getElementById('password').value;
            
            if (username === '') {
                e.preventDefault();
                showError('Please enter username or email');
                return false;
            }
            
            if (password === '') {
                e.preventDefault();
                showError('Please enter password');
                return false;
            }
        });
    }
    
    // Register Form Validation
    const registerForm = document.getElementById('registerForm');
    if (registerForm) {
        const password = document.getElementById('password');
        const confirmPassword = document.getElementById('confirm_password');
        const email = document.getElementById('email');
        const phone = document.getElementById('phone');
        
        // Real-time password matching
        if (confirmPassword) {
            confirmPassword.addEventListener('input', function() {
                if (this.value !== password.value) {
                    this.setCustomValidity('Passwords do not match');
                    this.style.borderColor = '#ef4444';
                } else {
                    this.setCustomValidity('');
                    this.style.borderColor = '#10b981';
                }
            });
        }
        
        // Email validation
        if (email) {
            email.addEventListener('input', function() {
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailRegex.test(this.value)) {
                    this.setCustomValidity('Please enter a valid email address');
                    this.style.borderColor = '#ef4444';
                } else {
                    this.setCustomValidity('');
                    this.style.borderColor = '#10b981';
                }
            });
        }
        
        // Phone validation
        if (phone) {
            phone.addEventListener('input', function() {
                const phoneRegex = /^[0-9\-\+\(\)\s]+$/;
                if (!phoneRegex.test(this.value)) {
                    this.setCustomValidity('Please enter a valid phone number');
                    this.style.borderColor = '#ef4444';
                } else {
                    this.setCustomValidity('');
                    this.style.borderColor = '#10b981';
                }
            });
        }
        
        registerForm.addEventListener('submit', function(e) {
            const fullName = document.getElementById('full_name').value.trim();
            const username = document.getElementById('username').value.trim();
            const passwordVal = password.value;
            const confirmPasswordVal = confirmPassword.value;
            
            if (fullName === '') {
                e.preventDefault();
                showError('Please enter your full name');
                return false;
            }
            
            if (username.length < 3) {
                e.preventDefault();
                showError('Username must be at least 3 characters');
                return false;
            }
            
            if (passwordVal.length < 6) {
                e.preventDefault();
                showError('Password must be at least 6 characters');
                return false;
            }
            
            if (passwordVal !== confirmPasswordVal) {
                e.preventDefault();
                showError('Passwords do not match');
                return false;
            }
        });
    }
    
    // Create Event Form Validation
    const createEventForm = document.getElementById('createEventForm');
    if (createEventForm) {
        const eventDate = document.getElementById('event_date');
        const maxAttendees = document.getElementById('max_attendees');
        const imageInput = document.getElementById('image');
        
        // Date validation - must be future date
        if (eventDate) {
            const today = new Date().toISOString().split('T')[0];
            eventDate.setAttribute('min', today);
            
            eventDate.addEventListener('change', function() {
                if (this.value < today) {
                    this.setCustomValidity('Event date must be in the future');
                    this.style.borderColor = '#ef4444';
                } else {
                    this.setCustomValidity('');
                    this.style.borderColor = '#10b981';
                }
            });
        }
        
        // Max attendees validation
        if (maxAttendees) {
            maxAttendees.addEventListener('input', function() {
                if (parseInt(this.value) < 1) {
                    this.setCustomValidity('Must have at least 1 attendee');
                    this.style.borderColor = '#ef4444';
                } else if (parseInt(this.value) > 10000) {
                    this.setCustomValidity('Maximum 10,000 attendees allowed');
                    this.style.borderColor = '#ef4444';
                } else {
                    this.setCustomValidity('');
                    this.style.borderColor = '#10b981';
                }
            });
        }
        
        // Image file validation
        if (imageInput) {
            imageInput.addEventListener('change', function() {
                const file = this.files[0];
                if (file) {
                    const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
                    const maxSize = 5 * 1024 * 1024; // 5MB
                    
                    if (!allowedTypes.includes(file.type)) {
                        this.value = '';
                        showError('Only JPG, PNG, and GIF images are allowed');
                        return false;
                    }
                    
                    if (file.size > maxSize) {
                        this.value = '';
                        showError('Image size must be less than 5MB');
                        return false;
                    }
                }
            });
        }
        
        createEventForm.addEventListener('submit', function(e) {
            const title = document.getElementById('title').value.trim();
            const description = document.getElementById('description').value.trim();
            const category = document.getElementById('category').value;
            const location = document.getElementById('location').value.trim();
            
            if (title === '') {
                e.preventDefault();
                showError('Please enter event title');
                return false;
            }
            
            if (description.length < 20) {
                e.preventDefault();
                showError('Event description must be at least 20 characters');
                return false;
            }
            
            if (category === '') {
                e.preventDefault();
                showError('Please select a category');
                return false;
            }
            
            if (location === '') {
                e.preventDefault();
                showError('Please enter event location');
                return false;
            }
        });
    }
    
    // Add input validation styling to all inputs
    const allInputs = document.querySelectorAll('input:not([type="submit"]), textarea, select');
    allInputs.forEach(input => {
        input.addEventListener('blur', function() {
            if (this.validity.valid && this.value !== '') {
                this.style.borderColor = '#10b981';
            } else if (!this.validity.valid) {
                this.style.borderColor = '#ef4444';
            }
        });
        
        input.addEventListener('focus', function() {
            this.style.borderColor = '#2563eb';
        });
    });
});

// Helper function to show error messages
function showError(message) {
    // Remove existing error if any
    const existingError = document.querySelector('.alert-error');
    if (existingError) {
        existingError.remove();
    }
    
    // Create new error alert
    const errorDiv = document.createElement('div');
    errorDiv.className = 'alert alert-error';
    errorDiv.textContent = message;
    errorDiv.style.animation = 'slideIn 0.3s ease';
    
    // Insert at the top of the form
    const form = document.querySelector('form');
    if (form) {
        form.insertBefore(errorDiv, form.firstChild);
        
        // Auto-remove after 5 seconds
        setTimeout(() => {
            errorDiv.style.animation = 'slideOut 0.3s ease';
            setTimeout(() => errorDiv.remove(), 300);
        }, 5000);
    }
}

// Helper function to show success messages
function showSuccess(message) {
    const successDiv = document.createElement('div');
    successDiv.className = 'alert alert-success';
    successDiv.textContent = message;
    successDiv.style.animation = 'slideIn 0.3s ease';
    
    const form = document.querySelector('form');
    if (form) {
        form.insertBefore(successDiv, form.firstChild);
        
        setTimeout(() => {
            successDiv.style.animation = 'slideOut 0.3s ease';
            setTimeout(() => successDiv.remove(), 300);
        }, 5000);
    }
}

// Add CSS animations
const style = document.createElement('style');
style.textContent = `
    @keyframes slideIn {
        from {
            opacity: 0;
            transform: translateY(-20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    @keyframes slideOut {
        from {
            opacity: 1;
            transform: translateY(0);
        }
        to {
            opacity: 0;
            transform: translateY(-20px);
        }
    }
`;
document.head.appendChild(style);