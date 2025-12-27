import { Controller } from '@hotwired/stimulus';

/**
 * Ingredient Autocomplete Controller
 *
 * Provides autocomplete functionality for ingredient search field with multiple selection.
 * Fetches ingredients from /api/ingredients/search and displays dropdown with results.
 * Allows selecting multiple ingredients which are shown as removable badges.
 */
export default class extends Controller {
    static targets = ['input', 'dropdown', 'selectedList', 'hiddenContainer'];
    static values = {
        url: { type: String, default: '/api/ingredients/search' },
        minChars: { type: Number, default: 2 },
        debounceDelay: { type: Number, default: 300 }
    };

    connect() {
        console.log('Ingredient Autocomplete Controller connected');
        this.debounceTimer = null;
        this.selectedIngredients = new Map(); // Map<id, {id, name}>
        this.isOpen = false;

        // Initialize with pre-selected ingredients from hidden fields
        // Note: We can't fetch ingredient names from API without ID lookup support,
        // so we'll just keep the hidden fields and show empty selection initially
        this.renderSelectedIngredients();

        // Close dropdown when clicking outside
        this.boundCloseOnOutsideClick = this.closeOnOutsideClick.bind(this);
        document.addEventListener('click', this.boundCloseOnOutsideClick);
    }

    disconnect() {
        document.removeEventListener('click', this.boundCloseOnOutsideClick);
        if (this.debounceTimer) {
            clearTimeout(this.debounceTimer);
        }
    }

    /**
     * Handle input changes with debouncing
     */
    onInput(event) {
        const query = event.target.value.trim();
        console.log('Input event triggered, query:', query);

        // Clear existing timer
        if (this.debounceTimer) {
            clearTimeout(this.debounceTimer);
        }

        // If query is too short, hide dropdown
        if (query.length < this.minCharsValue) {
            console.log('Query too short, hiding dropdown');
            this.hideDropdown();
            return;
        }

        // Debounce the search
        this.debounceTimer = setTimeout(() => {
            console.log('Executing search for:', query);
            this.search(query);
        }, this.debounceDelayValue);
    }

    /**
     * Fetch search results from API
     */
    async search(query) {
        try {
            const url = `${this.urlValue}?q=${encodeURIComponent(query)}`;
            console.log('Fetching from URL:', url);

            const response = await fetch(url, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            console.log('Response status:', response.status);

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const results = await response.json();
            console.log('Search results:', results);
            this.displayResults(results);
        } catch (error) {
            console.error('Ingredient search failed:', error);
            this.showError('Ошибка при поиске ингредиентов');
        }
    }

    /**
     * Display search results in dropdown
     */
    displayResults(results) {
        // Clear previous results
        this.dropdownTarget.innerHTML = '';

        if (results.length === 0) {
            this.dropdownTarget.innerHTML = `
                <div class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400">
                    Ингредиенты не найдены
                </div>
            `;
            this.showDropdown();
            return;
        }

        // Filter out already selected ingredients
        const availableResults = results.filter(ingredient =>
            !this.selectedIngredients.has(ingredient.id)
        );

        if (availableResults.length === 0) {
            this.dropdownTarget.innerHTML = `
                <div class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400">
                    Все найденные ингредиенты уже выбраны
                </div>
            `;
            this.showDropdown();
            return;
        }

        // Create result items
        availableResults.forEach(ingredient => {
            const item = document.createElement('div');
            item.className = 'px-4 py-2 cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors text-gray-900 dark:text-white';
            item.textContent = ingredient.name;
            item.dataset.ingredientId = ingredient.id;
            item.dataset.ingredientName = ingredient.name;
            item.addEventListener('click', () => this.selectIngredient(ingredient.id, ingredient.name));
            this.dropdownTarget.appendChild(item);
        });

        this.showDropdown();
    }

    /**
     * Show error message in dropdown
     */
    showError(message) {
        this.dropdownTarget.innerHTML = `
            <div class="px-4 py-3 text-sm text-red-600 dark:text-red-400">
                ${message}
            </div>
        `;
        this.showDropdown();
    }

    /**
     * Select an ingredient from the dropdown
     */
    selectIngredient(id, name) {
        // Add to selected ingredients
        this.selectedIngredients.set(id, { id, name });

        // Clear input field
        this.inputTarget.value = '';

        // Re-render selected ingredients and hidden fields
        this.renderSelectedIngredients();
        this.updateHiddenFields();

        // Hide dropdown
        this.hideDropdown();

        // Focus back on input
        this.inputTarget.focus();
    }

    /**
     * Remove an ingredient from selection
     */
    removeIngredient(id) {
        this.selectedIngredients.delete(id);
        this.renderSelectedIngredients();
        this.updateHiddenFields();
    }

    /**
     * Render selected ingredients as removable badges
     */
    renderSelectedIngredients() {
        this.selectedListTarget.innerHTML = '';

        if (this.selectedIngredients.size === 0) {
            this.selectedListTarget.innerHTML = `
                <div class="text-sm text-gray-500 dark:text-gray-400">
                    Ингредиенты не выбраны
                </div>
            `;
            return;
        }

        this.selectedIngredients.forEach((ingredient) => {
            const badge = document.createElement('div');
            badge.className = 'inline-flex items-center gap-1 px-3 py-1 bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200 rounded-full text-sm';
            badge.innerHTML = `
                <span>${this.escapeHtml(ingredient.name)}</span>
                <button
                    type="button"
                    data-ingredient-id="${ingredient.id}"
                    class="ml-1 hover:bg-blue-200 dark:hover:bg-blue-800 rounded-full p-0.5 transition-colors"
                    title="Удалить"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            `;

            // Add click handler for remove button
            const removeButton = badge.querySelector('button');
            removeButton.addEventListener('click', () => this.removeIngredient(ingredient.id));

            this.selectedListTarget.appendChild(badge);
        });
    }

    /**
     * Update hidden input fields for form submission
     */
    updateHiddenFields() {
        // Clear existing hidden inputs
        this.hiddenContainerTarget.innerHTML = '';

        // Create hidden input for each selected ingredient
        this.selectedIngredients.forEach((ingredient) => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'ingredients[]';
            input.value = ingredient.id;
            this.hiddenContainerTarget.appendChild(input);
        });
    }

    /**
     * Handle keyboard navigation
     */
    onKeydown(event) {
        if (!this.isOpen) return;

        const items = this.dropdownTarget.querySelectorAll('[data-ingredient-id]');
        if (items.length === 0) return;

        let currentIndex = Array.from(items).findIndex(item =>
            item.classList.contains('bg-gray-100') || item.classList.contains('dark:bg-gray-700')
        );

        switch (event.key) {
            case 'ArrowDown':
                event.preventDefault();
                currentIndex = currentIndex < items.length - 1 ? currentIndex + 1 : 0;
                this.highlightItem(items, currentIndex);
                break;
            case 'ArrowUp':
                event.preventDefault();
                currentIndex = currentIndex > 0 ? currentIndex - 1 : items.length - 1;
                this.highlightItem(items, currentIndex);
                break;
            case 'Enter':
                event.preventDefault();
                if (currentIndex >= 0) {
                    const selectedItem = items[currentIndex];
                    this.selectIngredient(
                        selectedItem.dataset.ingredientId,
                        selectedItem.dataset.ingredientName
                    );
                }
                break;
            case 'Escape':
                event.preventDefault();
                this.hideDropdown();
                break;
        }
    }

    /**
     * Highlight item during keyboard navigation
     */
    highlightItem(items, index) {
        items.forEach((item, i) => {
            if (i === index) {
                item.classList.add('bg-gray-100');
                item.classList.add('dark:bg-gray-700');
                item.scrollIntoView({ block: 'nearest' });
            } else {
                item.classList.remove('bg-gray-100');
                item.classList.remove('dark:bg-gray-700');
            }
        });
    }

    /**
     * Show dropdown
     */
    showDropdown() {
        this.dropdownTarget.classList.remove('hidden');
        this.isOpen = true;
    }

    /**
     * Hide dropdown
     */
    hideDropdown() {
        this.dropdownTarget.classList.add('hidden');
        this.isOpen = false;
    }

    /**
     * Close dropdown when clicking outside
     */
    closeOnOutsideClick(event) {
        if (!this.element.contains(event.target)) {
            this.hideDropdown();
        }
    }

    /**
     * Escape HTML to prevent XSS
     */
    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
}
