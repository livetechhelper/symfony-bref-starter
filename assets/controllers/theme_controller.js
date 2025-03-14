import { Controller } from '@hotwired/stimulus';

/**
 * Theme controller for handling dark/light mode
 */
export default class extends Controller {
    static targets = ['toggle'];
    static values = {
        theme: { type: String, default: 'light' }
    };

    connect() {
        // Check for saved theme preference or respect OS preference
        const savedTheme = localStorage.getItem('theme');
        const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
        
        if (savedTheme === 'dark' || (!savedTheme && prefersDark)) {
            this.enableDarkMode();
        } else {
            this.enableLightMode();
        }
    }

    toggle() {
        if (document.documentElement.classList.contains('dark')) {
            this.enableLightMode();
        } else {
            this.enableDarkMode();
        }
    }

    enableDarkMode() {
        document.documentElement.classList.add('dark');
        this.themeValue = 'dark';
        localStorage.setItem('theme', 'dark');
        this.updateToggle();
    }

    enableLightMode() {
        document.documentElement.classList.remove('dark');
        this.themeValue = 'light';
        localStorage.setItem('theme', 'light');
        this.updateToggle();
    }

    updateToggle() {
        if (this.hasToggleTarget) {
            // Update toggle state if needed
            // This could update an icon or checkbox state
        }
    }
} 