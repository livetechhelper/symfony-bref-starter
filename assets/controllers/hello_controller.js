import { Controller } from '@hotwired/stimulus';

/**
 * Basic Stimulus controller example
 */
export default class extends Controller {
    static targets = ['output'];
    static values = {
        name: String
    };

    connect() {
        console.log('Hello controller connected');
        if (this.hasOutputTarget && this.hasNameValue) {
            this.outputTarget.textContent = `Hello, ${this.nameValue}!`;
        }
    }
} 