import { Controller } from '@hotwired/stimulus';

/**
 * Theme switcher controller for applying different themes
 */
export default class extends Controller {
    static values = {
        themeKey: String,
        themeCssUrl: String
    };

    connect() {
        console.log('Theme switcher connected');
    }

    apply() {
        if (!this.hasThemeKeyValue || !this.hasThemeCssUrlValue) {
            console.error('Theme key or CSS URL not provided');
            return;
        }

        // Fetch the theme CSS
        fetch(this.themeCssUrlValue)
            .then(response => {
                if (!response.ok) {
                    throw new Error('Failed to fetch theme CSS');
                }
                return response.text();
            })
            .then(css => {
                this.applyThemeCSS(css);
                this.saveThemePreference();
            })
            .catch(error => {
                console.error('Error applying theme:', error);
            });
    }

    applyThemeCSS(css) {
        // Check if theme style element already exists
        let themeStyle = document.getElementById('theme-custom-variables');
        
        if (!themeStyle) {
            // Create a new style element if it doesn't exist
            themeStyle = document.createElement('style');
            themeStyle.id = 'theme-custom-variables';
            document.head.appendChild(themeStyle);
        }
        
        // Update the style content
        themeStyle.textContent = css;
        
        // Show success message
        this.showNotification(`Theme "${this.themeKeyValue}" applied successfully!`);
    }

    saveThemePreference() {
        // Save the theme preference to localStorage
        localStorage.setItem('app-theme', this.themeKeyValue);
    }

    showNotification(message) {
        // Create a simple notification element
        const notification = document.createElement('div');
        notification.className = 'fixed bottom-4 right-4 bg-primary-600 text-white px-4 py-2 rounded shadow-lg z-50 transform transition-transform duration-300 translate-y-0';
        notification.textContent = message;
        
        // Add to the DOM
        document.body.appendChild(notification);
        
        // Remove after 3 seconds
        setTimeout(() => {
            notification.classList.add('translate-y-full', 'opacity-0');
            setTimeout(() => {
                document.body.removeChild(notification);
            }, 300);
        }, 3000);
    }
} 