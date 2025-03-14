import { Controller } from '@hotwired/stimulus';

/**
 * Registration form controller
 */
export default class extends Controller {
    static targets = ['firstName', 'lastName', 'email', 'password', 'confirmPassword', 'agreeTerms'];

    connect() {
        console.log('Registration form controller connected');
    }

    // Example method that could be used to validate the form before submission
    validateForm(event) {
        const firstName = this.hasFirstNameTarget ? this.firstNameTarget.value : null;
        const lastName = this.hasLastNameTarget ? this.lastNameTarget.value : null;
        const email = this.hasEmailTarget ? this.emailTarget.value : null;
        const password = this.hasPasswordTarget ? this.passwordTarget.value : null;
        const confirmPassword = this.hasConfirmPasswordTarget ? this.confirmPasswordTarget.value : null;
        const agreeTerms = this.hasAgreeTermsTarget ? this.agreeTermsTarget.checked : null;

        let isValid = true;
        let errorMessage = '';

        if (!firstName || !lastName || !email) {
            isValid = false;
            errorMessage = 'Please fill in all required fields';
        } else if (password !== confirmPassword) {
            isValid = false;
            errorMessage = 'Passwords do not match';
        } else if (!agreeTerms) {
            isValid = false;
            errorMessage = 'You must agree to the terms and conditions';
        }

        if (!isValid) {
            event.preventDefault();
            this.showError(errorMessage);
            return false;
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