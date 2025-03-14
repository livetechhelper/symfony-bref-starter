import { Controller } from '@hotwired/stimulus';

/**
 * Dropdown controller for handling dropdown menus
 */
export default class extends Controller {
    static targets = ['menu'];

    connect() {
        // Add event listener to close dropdown when clicking outside
        document.addEventListener('click', this.handleClickOutside.bind(this));
    }

    disconnect() {
        // Remove event listener when controller is disconnected
        document.removeEventListener('click', this.handleClickOutside.bind(this));
    }

    toggle(event) {
        event.stopPropagation();
        this.menuTarget.classList.toggle('hidden');
    }

    handleClickOutside(event) {
        // Close dropdown if clicking outside of it
        if (this.menuTarget && !this.element.contains(event.target) && !this.menuTarget.classList.contains('hidden')) {
            this.menuTarget.classList.add('hidden');
        }
    }
} 