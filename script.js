// Wait for DOM to load
document.addEventListener('DOMContentLoaded', function() {
    // Initialize step navigation
    let currentStep = 1;
    const totalSteps = 6;
    
    // Activate first step
    activateStep(1);
    
    // Star rating functionality
    initStarRating();
    
    // Radio button for additional work
    initAdditionalWork();
    
    // Add/remove dynamic textareas
    initDynamicTextareas();
    
    // Form submission
    initFormSubmission();
    
    // Input validation for step completion
    initStepValidation();
});

// Activate a specific step
function activateStep(stepNumber) {
    const steps = document.querySelectorAll('.step');
    steps.forEach((step, index) => {
        const stepNum = index + 1;
        step.classList.remove('step-active');
        
        if (stepNum === stepNumber) {
            step.classList.add('step-active');
        } else if (stepNum < stepNumber) {
            step.classList.add('step-good');
        }
    });
}

// Star rating functionality
function initStarRating() {
    const starContainers = document.querySelectorAll('.stars');
    
    starContainers.forEach(container => {
        const stars = container.querySelectorAll('.star');
        const input = container.querySelector('input[type="hidden"]');
        const step = container.closest('.step');
        const starsBad = step.querySelector('.stars-bad');
        const starsGood = step.querySelector('.stars-good');
        
        stars.forEach((star, index) => {
            // Hover effect
            star.addEventListener('mouseenter', function() {
                stars.forEach((s, i) => {
                    if (i <= index) {
                        s.classList.add('hover-active');
                    } else {
                        s.classList.remove('hover-active');
                    }
                });
            });
            
            // Click to select
            star.addEventListener('click', function() {
                const rating = star.getAttribute('data-star');
                input.value = rating;
                
                // Remove all active classes
                stars.forEach(s => {
                    s.classList.remove('active');
                    s.classList.remove('hover-active');
                });
                
                // Add active class to selected stars
                stars.forEach((s, i) => {
                    if (i < rating) {
                        s.classList.add('active');
                    }
                });
                
                // Show feedback section based on rating
                if (rating <= 3) {
                    if (starsBad) starsBad.style.display = 'block';
                    if (starsGood) starsGood.style.display = 'none';
                } else {
                    if (starsBad) starsBad.style.display = 'none';
                    if (starsGood) starsGood.style.display = 'block';
                }
                
                // Mark step as completed and move to next
                markStepCompleted(star);
            });
        });
        
        // Reset hover on mouse leave
        container.addEventListener('mouseleave', function() {
            stars.forEach(s => {
                s.classList.remove('hover-active');
            });
        });
    });
}

// Additional work radio functionality
function initAdditionalWork() {
    const radios = document.querySelectorAll('input[name="additional_work"]');
    const dopsTextarea = document.querySelector('.dops-textarea[data-val="Да"]');
    
    radios.forEach(radio => {
        radio.addEventListener('change', function() {
            if (this.value === 'Да' && this.checked) {
                if (dopsTextarea) {
                    dopsTextarea.style.display = 'block';
                }
            } else if (this.value === 'Нет' && this.checked) {
                if (dopsTextarea) {
                    dopsTextarea.style.display = 'none';
                }
                markStepCompleted(this);
            }
        });
    });
}

// Dynamic textarea addition/removal
function initDynamicTextareas() {
    const dopsAdd = document.querySelector('.dop-add');
    const dopsTextarea = document.querySelector('.dops-textarea');
    
    if (dopsAdd && dopsTextarea) {
        dopsAdd.addEventListener('click', function() {
            const lines = dopsTextarea.querySelectorAll('.line-textarea');
            const newIndex = lines.length + 1;
            const firstLine = lines[0];
            const newLine = firstLine.cloneNode(true);
            
            // Clear textarea values
            const textareas = newLine.querySelectorAll('textarea');
            textareas.forEach(textarea => {
                textarea.value = '';
            });
            
            // Update data-i attribute
            newLine.setAttribute('data-i', newIndex);
            
            // Insert before the add button container
            const dopsAddContainer = dopsTextarea.querySelector('.dops-add');
            dopsAddContainer.parentNode.insertBefore(newLine, dopsAddContainer);
            
            // Initialize remove button for new line
            initRemoveButtons();
        });
    }
    
    // Initialize remove buttons
    initRemoveButtons();
}

function initRemoveButtons() {
    const removeButtons = document.querySelectorAll('.dop-remove');
    
    removeButtons.forEach(button => {
        button.onclick = function() {
            const line = this.closest('.line-textarea');
            const parent = line.parentElement;
            const lines = parent.querySelectorAll('.line-textarea');
            
            // Only remove if there's more than one line
            if (lines.length > 1) {
                line.remove();
            }
        };
    });
}

// Step validation
function initStepValidation() {
    // Monitor all inputs, radios, checkboxes
    const allInputs = document.querySelectorAll('input[data-step], textarea');
    
    allInputs.forEach(input => {
        input.addEventListener('change', function() {
            markStepCompleted(this);
        });
        
        input.addEventListener('input', function() {
            if (this.type === 'text' || this.type === 'textarea') {
                markStepCompleted(this);
            }
        });
    });
}

function markStepCompleted(element) {
    const step = element.closest('.step');
    if (!step) return;
    
    const stepNumber = parseInt(step.getAttribute('data-step'));
    const stepType = step.getAttribute('data-type');
    
    // Check if step is completed
    let isCompleted = false;
    
    if (stepType === 'input') {
        // Check if at least one input has value
        const inputs = step.querySelectorAll('input[data-step]');
        inputs.forEach(input => {
            if (input.value.trim() !== '') {
                isCompleted = true;
            }
        });
    } else if (stepType === 'radio') {
        // Check if radio is selected
        const radios = step.querySelectorAll('input[type="radio"][data-step]');
        radios.forEach(radio => {
            if (radio.checked) {
                isCompleted = true;
            }
        });
    } else if (stepType === 'stars') {
        // Check if rating is given
        const hiddenInput = step.querySelector('input[type="hidden"]');
        if (hiddenInput && hiddenInput.value !== '') {
            isCompleted = true;
        }
    } else if (stepType === 'bigcheckbox') {
        // Check if at least one checkbox is selected or "nothing needed" radio
        const checkboxes = step.querySelectorAll('input[type="checkbox"][data-step]');
        const nothingRadio = step.querySelector('input[name="no_discounts"]');
        
        checkboxes.forEach(checkbox => {
            if (checkbox.checked) {
                isCompleted = true;
            }
        });
        
        if (nothingRadio && nothingRadio.checked) {
            isCompleted = true;
        }
    }
    
    // Hide error if completed
    if (isCompleted) {
        const errorDiv = step.querySelector('.red-error');
        if (errorDiv) {
            errorDiv.style.display = 'none';
        }
        
        // Move to next step
        if (stepNumber < 6) {
            setTimeout(() => {
                activateStep(stepNumber + 1);
            }, 300);
        }
    }
}

// Form submission
function initFormSubmission() {
    const form = document.getElementById('warrantyForm');
    
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Validate all steps
        let allValid = true;
        const steps = document.querySelectorAll('.step');
        
        steps.forEach(step => {
            const stepNumber = parseInt(step.getAttribute('data-step'));
            const stepType = step.getAttribute('data-type');
            const errorDiv = step.querySelector('.red-error');
            let isValid = false;
            
            if (stepType === 'input') {
                const inputs = step.querySelectorAll('input[data-step]');
                inputs.forEach(input => {
                    if (input.value.trim() !== '') {
                        isValid = true;
                    }
                });
            } else if (stepType === 'radio') {
                const radios = step.querySelectorAll('input[type="radio"][data-step]');
                radios.forEach(radio => {
                    if (radio.checked) {
                        isValid = true;
                    }
                });
            } else if (stepType === 'stars') {
                const hiddenInput = step.querySelector('input[type="hidden"]');
                if (hiddenInput && hiddenInput.value !== '') {
                    isValid = true;
                }
            } else if (stepType === 'bigcheckbox') {
                const checkboxes = step.querySelectorAll('input[type="checkbox"][data-step]');
                const nothingRadio = step.querySelector('input[name="no_discounts"]');
                
                checkboxes.forEach(checkbox => {
                    if (checkbox.checked) {
                        isValid = true;
                    }
                });
                
                if (nothingRadio && nothingRadio.checked) {
                    isValid = true;
                }
            }
            
            if (!isValid) {
                allValid = false;
                if (errorDiv) {
                    errorDiv.style.display = 'block';
                }
                step.scrollIntoView({ behavior: 'smooth', block: 'center' });
            } else {
                if (errorDiv) {
                    errorDiv.style.display = 'none';
                }
            }
        });
        
        if (allValid) {
            // All steps are valid, show success message
            showSuccessMessage();
        }
    });
}

function showSuccessMessage() {
    const form = document.querySelector('form');
    const endMessage = document.querySelector('.end');
    
    // Hide form with animation
    form.style.transition = 'opacity 0.5s ease';
    form.style.opacity = '0';
    
    setTimeout(() => {
        form.style.display = 'none';
        endMessage.style.display = 'block';
        endMessage.style.opacity = '0';
        
        setTimeout(() => {
            endMessage.style.transition = 'opacity 0.5s ease';
            endMessage.style.opacity = '1';
        }, 100);
    }, 500);
}

// "Nothing needed" radio functionality
document.addEventListener('change', function(e) {
    if (e.target.name === 'no_discounts') {
        const checkboxes = document.querySelectorAll('input[name="discounts[]"]');
        if (e.target.checked) {
            checkboxes.forEach(checkbox => {
                checkbox.checked = false;
            });
            markStepCompleted(e.target);
        }
    }
    
    if (e.target.name === 'discounts[]') {
        const nothingRadio = document.querySelector('input[name="no_discounts"]');
        if (e.target.checked && nothingRadio) {
            nothingRadio.checked = false;
        }
        markStepCompleted(e.target);
    }
});
