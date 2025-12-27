import { Controller } from '@hotwired/stimulus';

/**
 * Star Rating Controller
 *
 * Provides interactive star rating selection functionality.
 * Handles click events on stars to update hidden radio buttons and visual state.
 */
export default class extends Controller {
    static targets = ['radioGroup', 'star'];

    connect() {
        console.log('Star Rating Controller connected');
        // Initialize visual state based on current selection
        this.updateStars();
    }

    /**
     * Handle star click - select corresponding rating
     */
    selectRating(event) {
        const clickedStar = event.currentTarget;
        const rating = parseInt(clickedStar.dataset.rating, 10);

        console.log('Star clicked, rating:', rating);

        // Find and check the corresponding radio button
        const radioButton = this.radioGroupTarget.querySelector(`input[value="${rating}"]`);
        if (radioButton) {
            radioButton.checked = true;
            console.log('Radio button checked for rating:', rating);
        }

        // Update visual state
        this.updateStars();
    }

    /**
     * Update visual representation of stars based on current selection
     */
    updateStars() {
        // Find currently selected rating from radio buttons
        const selectedRadio = this.radioGroupTarget.querySelector('input[type="radio"]:checked');
        const selectedRating = selectedRadio ? parseInt(selectedRadio.value, 10) : 0;

        console.log('Updating stars, selected rating:', selectedRating);

        // Update each star's appearance
        this.starTargets.forEach((star) => {
            const starRating = parseInt(star.dataset.rating, 10);
            const svgPath = star.querySelector('svg path');

            if (starRating <= selectedRating) {
                // Filled star (yellow)
                star.classList.remove('text-gray-300', 'dark:text-gray-600');
                star.classList.add('text-yellow-400', 'dark:text-yellow-500');
                if (svgPath) {
                    svgPath.setAttribute('fill', 'currentColor');
                }
            } else {
                // Empty star (gray)
                star.classList.remove('text-yellow-400', 'dark:text-yellow-500');
                star.classList.add('text-gray-300', 'dark:text-gray-600');
                if (svgPath) {
                    svgPath.setAttribute('fill', 'none');
                }
            }
        });
    }
}
