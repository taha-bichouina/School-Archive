document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('search-input');
    const suggestionsContainer = document.getElementById('suggestions-container');
    let debounceTimer;

    if (searchInput && suggestionsContainer) {
        searchInput.addEventListener('input', function(e) {
            clearTimeout(debounceTimer);
            const searchTerm = e.target.value.trim();
            
            if (searchTerm.length > 2) {
                debounceTimer = setTimeout(() => {
                    fetchSuggestions(searchTerm);
                }, 300);
            } else {
                hideSuggestions();
            }
        });

        // Hide suggestions when clicking outside
        document.addEventListener('click', function(e) {
            if (!searchInput.contains(e.target) && !suggestionsContainer.contains(e.target)) {
                hideSuggestions();
            }
        });

        // Handle keyboard navigation
        searchInput.addEventListener('keydown', function(e) {
            if (e.key === 'ArrowDown' && !suggestionsContainer.classList.contains('d-none')) {
                e.preventDefault();
                const firstItem = suggestionsContainer.querySelector('.suggestion-item:not(.empty)');
                if (firstItem) firstItem.focus();
            }
        });
    }

    function fetchSuggestions(searchTerm) {
        fetch(`search_suggestions.php?search=${encodeURIComponent(searchTerm)}`)
            .then(response => {
                if (!response.ok) throw new Error('Network response was not ok');
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    displaySuggestions(data.results);
                } else {
                    showNoResults();
                }
            })
            .catch(error => {
                console.error('Error fetching suggestions:', error);
                showNoResults();
            });
    }

    function displaySuggestions(suggestions) {
        suggestionsContainer.innerHTML = '';
        
        if (suggestions.length === 0) {
            showNoResults();
            return;
        }
        
        suggestions.forEach(suggestion => {
            const item = document.createElement('div');
            item.className = 'suggestion-item';
            item.tabIndex = 0;
            item.innerHTML = `
                <div class="suggestion-avatar">
                    ${suggestion.initials}
                </div>
                <div class="suggestion-text">
                    <div class="suggestion-title">${suggestion.text}</div>
                    <div class="suggestion-subtext">${suggestion.subtext}</div>
                </div>
                <div class="suggestion-badge badge-${suggestion.class.toLowerCase()}">
                    ${suggestion.class}
                </div>
            `;
            
            item.addEventListener('click', () => {
                window.location.href = `student_view.php?id=${suggestion.id}`;
            });
            
            item.addEventListener('keydown', (e) => {
                if (e.key === 'Enter') {
                    window.location.href = `student_view.php?id=${suggestion.id}`;
                }
            });
            
            suggestionsContainer.appendChild(item);
        });
        
        showSuggestions();
    }

    function showNoResults() {
        suggestionsContainer.innerHTML = `
            <div class="suggestion-item empty">
                Aucun résultat trouvé
            </div>
        `;
        showSuggestions();
    }

    function showSuggestions() {
        suggestionsContainer.classList.remove('d-none');
    }

    function hideSuggestions() {
        suggestionsContainer.classList.add('d-none');
    }
});