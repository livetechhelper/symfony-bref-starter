import { Controller } from '@hotwired/stimulus';

/**
 * Reset password form controller
 */
export default class extends Controller {
    static targets = ['email', 'password', 'confirmPassword'];

    connect() {
        console.log('Reset password form controller connected');
    }

    // Example method that could be used to validate the form before submission
    validateForm(event) {
        // For password reset request form
        if (this.hasEmailTarget) {
            const email = this.emailTarget.value;
            if (!email) {
                event.preventDefault();
                this.showError('Please enter your email address');
                return false;
            }
        }

        // For password reset form
        if (this.hasPasswordTarget && this.hasConfirmPasswordTarget) {
            const password = this.passwordTarget.value;
            const confirmPassword = this.confirmPasswordTarget.value;

            if (!password || !confirmPassword) {
                event.preventDefault();
                this.showError('Please fill in all required fields');
                return false;
            }

            if (password !== confirmPassword) {
                event.preventDefault();
                this.showError('Passwords do not match');
                return false;
            }
        }

        return true;
    }

    // Example method to show error messages
    showError(message) {
        const errorDiv = document.createElement('div');
        errorDiv.className = 'bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4';
        errorDiv.innerHTML = `<span class="block sm:inline">${message}</span>`;
        
        this.element.prepend(errorDiv);
        
        // Remove the error message after 5 seconds
        setTimeout(() => {
            errorDiv.remove();
        }, 5000);
    }

    // Example method to check password strength
    checkPasswordStrength() {
        if (!this.hasPasswordTarget) return;
        
        const password = this.passwordTarget.value;
        let strength = 0;
        
        if (password.length >= 8) strength += 1;
        if (password.match(/[a-z]+/)) strength += 1;
        if (password.match(/[A-Z]+/)) strength += 1;
        if (password.match(/[0-9]+/)) strength += 1;
        if (password.match(/[^a-zA-Z0-9]+/)) strength += 1;
        
        let strengthText = '';
        let strengthClass = '';
        
        switch (strength) {
            case 0:
            case 1:
                strengthText = 'Weak';
                strengthClass = 'text-red-600';
                break;
            case 2:
            case 3:
                strengthText = 'Medium';
                strengthClass = 'text-yellow-600';
                break;
            case 4:
            case 5:
                strengthText = 'Strong';
                strengthClass = 'text-green-600';
                break;
        }
        
        // You would need to add a password strength indicator element to your form
        // const strengthIndicator = document.getElementById('password-strength');
        // if (strengthIndicator) {
        //     strengthIndicator.textContent = strengthText;
        //     strengthIndicator.className = strengthClass;
        // }
    }
} 