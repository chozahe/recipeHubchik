import { Controller } from '@hotwired/stimulus';

/**
 * Author Autocomplete Controller
 *
 * Provides autocomplete functionality for author search field.
 * Fetches users from /api/users/search and displays dropdown with results.
 */
export default class extends Controller {
    static targets = ['input', 'hiddenId', 'dropdown', 'clearButton'];
    static values = {
        url: { type: String, default: '/api/users/search' },
        minChars: { type: Number, default: 2 },
        debounceDelay: { type: Number, default: 400 }
    };

    connect() {
        this.debounceTimer = null;
        this.selectedAuthorId = this.hiddenIdTarget.value || null;
        this.isOpen = false;

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

        // Clear selected author if input is manually changed
        if (this.selectedAuthorId && this.inputTarget.dataset.selectedName !== query) {
            this.clearSelection();
        }

        // Clear existing timer
        if (this.debounceTimer) {
            clearTimeout(this.debounceTimer);
        }

        // If query is too short, hide dropdown
        if (query.length < this.minCharsValue) {
            this.hideDropdown();
            return;
        }

        // Debounce the search
        this.debounceTimer = setTimeout(() => {
            this.search(query);
        }, this.debounceDelayValue);
    }

    /**
     * Fetch search results from API
     */
    async search(query) {
        try {
            const url = `${this.urlValue}?q=${encodeURIComponent(query)}`;
            const response = await fetch(url, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const results = await response.json();
            this.displayResults(results);
        } catch (error) {
            console.error('Author search failed:', error);
            this.showError('Ошибка при поиске авторов');
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
                    Авторы не найдены
                </div>
            `;
            this.showDropdown();
            return;
        }

        // Create result items
        results.forEach(author => {
            const item = document.createElement('div');
            item.className = 'px-4 py-2 cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors';
            item.textContent = author.name;
            item.dataset.authorId = author.id;
            item.dataset.authorName = author.name;
            item.addEventListener('click', () => this.selectAuthor(author.id, author.name));
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
     * Select an author from the dropdown
     */
    selectAuthor(id, name) {
        this.selectedAuthorId = id;
        this.inputTarget.value = name;
        this.inputTarget.dataset.selectedName = name;
        this.hiddenIdTarget.value = id;
        this.hideDropdown();
        this.showClearButton();
    }

    /**
     * Clear selected author
     */
    clearSelection() {
        this.selectedAuthorId = null;
        this.inputTarget.value = '';
        delete this.inputTarget.dataset.selectedName;
        this.hiddenIdTarget.value = '';
        this.hideDropdown();
        this.hideClearButton();
        this.inputTarget.focus();
    }

    /**
     * Handle keyboard navigation
     */
    onKeydown(event) {
        if (!this.isOpen) return;

        const items = this.dropdownTarget.querySelectorAll('[data-author-id]');
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
                    this.selectAuthor(
                        selectedItem.dataset.authorId,
                        selectedItem.dataset.authorName
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
     * Show clear button
     */
    showClearButton() {
        if (this.hasClearButtonTarget) {
            this.clearButtonTarget.classList.remove('hidden');
        }
    }

    /**
     * Hide clear button
     */
    hideClearButton() {
        if (this.hasClearButtonTarget) {
            this.clearButtonTarget.classList.add('hidden');
        }
    }
}
