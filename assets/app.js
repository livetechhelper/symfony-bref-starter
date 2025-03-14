/*
 * Welcome to your app's main JavaScript file!
 *
 * This file will be included onto the page via the importmap() Twig function,
 * which should already be in your base.html.twig.
 */
import './styles/app.css';

// Import Stimulus controllers
import { startStimulusApp } from '@symfony/stimulus-bridge';
import '@hotwired/turbo';

// Register Stimulus controllers
export const app = startStimulusApp(require.context(
    './controllers',
    true,
    /\.(j|t)sx?$/
));

// You can specify which controllers should be imported
// import HelloController from './controllers/hello_controller';
// app.register('hello', HelloController);

console.log('Symfony Bref Starter - Frontend initialized! 🎉');
