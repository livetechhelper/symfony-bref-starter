import { Controller } from '@hotwired/stimulus';

/**
 * Login form controller
 */
export default class extends Controller {
    static targets = ['email', 'password', 'rememberMe'];

    connect() {
        console.log('Login form controller connected');
    }

    // Example method that could be used to validate the form before submission
    validateForm(event) {
        const email = this.emailTarget.value;
        const password = this.passwordTarget.value;

        if (!email || !password) {
            event.preventDefault();
            this.showError('Please fill in all required fields');
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
} 