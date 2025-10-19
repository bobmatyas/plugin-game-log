/**
 * Game Log Admin JavaScript
 */
(function() {
    'use strict';
    

    // Wait for DOM to be ready
    document.addEventListener('DOMContentLoaded', function() {
        initGameSearch();
        initModal();
    });
    
    /**
     * Initialize game search functionality
     */
    function initGameSearch() {
        const searchBtn = document.getElementById('search-games-btn');
        const searchInput = document.getElementById('game-search-input');
        const searchSubmitBtn = document.getElementById('search-games-submit');
        const searchForm = document.getElementById('game-search-form');
        
        // Search form submission (for Add Game page)
        if (searchForm) {
            searchForm.addEventListener('submit', function(e) {
                e.preventDefault();
                e.stopPropagation();
                const query = searchInput ? searchInput.value.trim() : '';
                if (query) {
                    searchGames(query);
                }
                return false;
            });
        }
        
        // Search button click (for modal)
        if (searchBtn) {
            searchBtn.addEventListener('click', function() {
                const modal = document.getElementById('game-search-modal');
                if (modal) {
                    modal.style.display = 'block';
                }
                if (searchInput) {
                    searchInput.focus();
                }
            });
        }
        
        // Search input Enter key
        if (searchInput) {
            searchInput.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    const query = this.value.trim();
                    if (query) {
                        searchGames(query);
                    }
                }
            });
        }
        
        // Search submit button
        if (searchSubmitBtn) {
            searchSubmitBtn.addEventListener('click', function() {
                const query = searchInput ? searchInput.value.trim() : '';
                if (query.length >= 2) {
                    searchGames(query);
                }
            });
        }
        
    }
    
    /**
     * Initialize modal functionality
     */
    function initModal() {
        const modal = document.getElementById('game-search-modal');
        const closeElements = document.querySelectorAll('.close');
        const modalContent = document.querySelector('.game-search-modal-content');
        
        // Close modal
        function closeModal() {
            if (modal) {
                modal.style.display = 'none';
            }
            const searchInput = document.getElementById('game-search-input');
            const searchResults = document.getElementById('game-search-results');
            if (searchInput) {
                searchInput.value = '';
            }
            if (searchResults) {
                searchResults.innerHTML = '';
            }
        }
        
        // Close modal on close button click
        closeElements.forEach(function(closeBtn) {
            closeBtn.addEventListener('click', closeModal);
        });
        
        // Close modal on backdrop click
        if (modal) {
            modal.addEventListener('click', function(e) {
                if (e.target === this) {
                    closeModal();
                }
            });
        }
        
        // Prevent modal content clicks from closing modal
        if (modalContent) {
            modalContent.addEventListener('click', function(e) {
                e.stopPropagation();
            });
        }
    }
    
    /**
     * Show summary modal
     */
    function showSummaryModal(summary) {
        // Create modal overlay
        const overlay = document.createElement('div');
        overlay.className = 'summary-modal-overlay';
        overlay.style.cssText = 'position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.8); z-index: 10000; display: flex; align-items: center; justify-content: center;';
        
        // Create modal content
        const modal = document.createElement('div');
        modal.className = 'summary-modal';
        modal.style.cssText = 'background: white; padding: 20px; border-radius: 8px; max-width: 600px; max-height: 80%; overflow-y: auto; position: relative;';
        
        modal.innerHTML = `
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                <h3 style="margin: 0;">Game Summary</h3>
                <button type="button" class="close-summary-modal" style="background: none; border: none; font-size: 24px; cursor: pointer; color: #666;">&times;</button>
            </div>
            <div class="summary-content" style="line-height: 1.6;">${summary}</div>
        `;
        
        overlay.appendChild(modal);
        document.body.appendChild(overlay);
        
        // Close modal functionality
        const closeBtn = modal.querySelector('.close-summary-modal');
        closeBtn.addEventListener('click', function() {
            document.body.removeChild(overlay);
        });
        
        overlay.addEventListener('click', function(e) {
            if (e.target === overlay) {
                document.body.removeChild(overlay);
            }
        });
        
        // Close on Escape key
        const handleEscape = function(e) {
            if (e.key === 'Escape') {
                document.body.removeChild(overlay);
                document.removeEventListener('keydown', handleEscape);
            }
        };
        document.addEventListener('keydown', handleEscape);
    }
    
    /**
     * Search games via AJAX
     */
    function searchGames(query) {
        const results = document.getElementById('game-search-results');
        const submitBtn = document.getElementById('search-games-submit');
        
        // Show loading state
        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.textContent = gameLogAjax.strings.searching;
        }
        if (results) {
            results.innerHTML = '<div class="search-loading">' + gameLogAjax.strings.searching + '</div>';
        }
        
        // Prepare form data
        const formData = new FormData();
        formData.append('action', 'game_log_search_games');
        formData.append('query', query);
        formData.append('limit', '20');
        formData.append('nonce', gameLogAjax.nonce);
        
        fetch(gameLogAjax.ajaxUrl, {
            method: 'POST',
            body: formData
        })
        .then(function(response) {
            return response.json();
        })
        .then(function(data) {
            if (data.success) {
                displaySearchResults(data.data.games);
            } else {
                if (results) {
                    results.innerHTML = '<div class="search-error">' + (data.data.message || gameLogAjax.strings.error) + '</div>';
                }
            }
        })
        .catch(function(error) {
            console.error('Search error:', error);
            if (results) {
                results.innerHTML = '<div class="search-error">' + gameLogAjax.strings.error + '</div>';
            }
        })
        .finally(function() {
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.textContent = 'Search';
            }
        });
    }
    
    /**
     * Display search results
     */
    function displaySearchResults(games) {
        const results = document.getElementById('game-search-results');
        
        if (!results) return;
        
        if (games.length === 0) {
            results.innerHTML = '<div class="no-results">' + gameLogAjax.strings.noResults + '</div>';
            return;
        }
        
        let html = '<div class="search-results-grid">';
        
        games.forEach(function(game) {
            // Create game data for database (without summary)
            const gameDataForDB = {
                id: game.id,
                name: game.name,
                release_date: game.release_date,
                cover_url: game.cover_url,
                platforms: game.platforms,
                genres: game.genres
            };
            
            const coverImage = game.cover_url ? 
                '<img src="' + game.cover_url + '" alt="' + game.name + '" class="game-cover" />' : 
                '<div class="no-cover">No Image</div>';
            
            const releaseDate = game.release_date ? 
                new Date(game.release_date).toLocaleDateString() : 
                'TBA';
            
            html += '<div class="game-result-item">';
            html += '<div class="game-cover-container">' + coverImage + '</div>';
            html += '<div class="game-details">';
            html += '<h3 class="game-title">' + game.name + '</h3>';
            html += '<p class="game-release">Release: ' + releaseDate + '</p>';
            html += '<p class="game-platforms">Platforms: ' + (game.platforms ? game.platforms.join(', ') : 'N/A') + '</p>';
            html += '<p class="game-genres">Genres: ' + (game.genres ? game.genres.join(', ') : 'N/A') + '</p>';
            if (game.summary) {
                html += '<p class="game-summary"><a href="#" class="view-summary" data-summary="' + game.summary.replace(/"/g, '&quot;') + '">View Summary</a></p>';
            }
            html += '</div>';
            html += '<div class="game-add-section">';
            html += '<div class="status-selection">';
            html += '<label for="game-status-' + game.id + '">Status:</label>';
            html += '<select id="game-status-' + game.id + '" class="game-status-select" data-game-id="' + game.id + '">';
            html += '<option value="wishlist">Wishlist</option>';
            html += '<option value="backlog">Backlog</option>';
            html += '<option value="playing">Playing</option>';
            html += '<option value="played">Played</option>';
            html += '</select>';
            html += '</div>';
            // Encode JSON data to prevent HTML attribute issues (using encodeURIComponent for Unicode support)
            const encodedGameData = encodeURIComponent(JSON.stringify(gameDataForDB));
            html += '<button type="button" class="button button-primary add-game-btn" data-game-encoded="' + encodedGameData + '">Add Game</button>';
            html += '</div>';
            html += '</div>';
        });
        
        html += '</div>';
        results.innerHTML = html;
        
            // Add click listeners for "View Summary" links
            const summaryLinks = results.querySelectorAll('.view-summary');
            summaryLinks.forEach((link) => {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    const summary = this.getAttribute('data-summary');
                    showSummaryModal(summary);
                });
            });
            
            // Add direct click listeners to Add Game buttons in search results
            const addGameButtons = results.querySelectorAll('.add-game-btn');
            addGameButtons.forEach((button) => {
                button.addEventListener('click', function(e) {
                    e.preventDefault();
                    const gameDataEncoded = this.getAttribute('data-game-encoded');
                    
                    if (!gameDataEncoded) {
                        console.error('No encoded game data found');
                        return;
                    }
                    
                    try {
                        // Decode URI component and parse JSON
                        const decodedData = decodeURIComponent(gameDataEncoded);
                        const gameData = JSON.parse(decodedData);
                        addGame(gameData);
                    } catch (error) {
                        console.error('Error parsing game data:', error);
                    }
                });
            });
    }
    
    /**
     * Add game via AJAX
     */
    function addGame(gameData) {
        
        // Find the button that was clicked by looking for the encoded data
        const encodedData = encodeURIComponent(JSON.stringify(gameData));
        const button = document.querySelector('.add-game-btn[data-game-encoded="' + encodedData + '"]');
        
        if (!button) {
            console.error('Button not found!');
            return;
        }
        
        // Get the selected status from the dropdown
        const gameResultItem = button.closest('.game-result-item');
        const statusSelect = gameResultItem.querySelector('.game-status-select');
        const selectedStatus = statusSelect ? statusSelect.value : 'wishlist';
        
        // Show loading state
        button.disabled = true;
        button.textContent = 'Adding...';
        
        // Prepare form data
        const formData = new FormData();
        formData.append('action', 'game_log_add_game');
        formData.append('game_data', JSON.stringify(gameData));
        formData.append('game_status', selectedStatus);
        formData.append('nonce', gameLogAjax.nonce);
        
        
        fetch(gameLogAjax.ajaxUrl, {
            method: 'POST',
            body: formData
        })
        .then(function(response) {
            return response.json();
        })
        .then(function(data) {
            if (data.success) {
                button.textContent = 'Added!';
                button.classList.add('button-success');
                
                // Fade out the game result item
                const gameResultItem = button.closest('.game-result-item');
                if (gameResultItem) {
                    gameResultItem.style.transition = 'opacity 0.5s ease-out';
                    gameResultItem.style.opacity = '0';
                    setTimeout(function() {
                        gameResultItem.style.display = 'none';
                    }, 500);
                }
                
                // Show success message
                showNotice('success', gameLogAjax.strings.gameAdded);
                
                // Refresh the page after a short delay to show the success state
                setTimeout(function() {
                    window.location.reload();
                }, 1500);
            } else {
                button.disabled = false;
                button.textContent = 'Add Game';
                showNotice('error', data.data.message || gameLogAjax.strings.error);
            }
        })
        .catch(function(error) {
            console.error('Add game error:', error);
            button.disabled = false;
            button.textContent = 'Add Game';
            showNotice('error', gameLogAjax.strings.error);
        });
    }
    
    /**
     * Show admin notice
     */
    function showNotice(type, message) {
        const noticeClass = type === 'success' ? 'notice-success' : 'notice-error';
        const notice = document.createElement('div');
        notice.className = 'notice ' + noticeClass + ' is-dismissible';
        notice.innerHTML = '<p>' + message + '</p>';
        
        const wrapH1 = document.querySelector('.wrap h1');
        if (wrapH1 && wrapH1.parentNode) {
            wrapH1.parentNode.insertBefore(notice, wrapH1.nextSibling);
        }
        
        // Auto-dismiss after 5 seconds
        setTimeout(function() {
            notice.style.transition = 'opacity 0.5s ease-out';
            notice.style.opacity = '0';
            setTimeout(function() {
                if (notice.parentNode) {
                    notice.parentNode.removeChild(notice);
                }
            }, 500);
        }, 5000);
    }

    /**
     * Initialize bulk actions functionality
     */
    function initBulkActions() {
        const bulkActionSelect = document.getElementById('bulk-action-selector-top');
        const newStatusSelect = document.getElementById('new-status-selector');
        const selectAllCheckbox = document.getElementById('cb-select-all-1');
        const gameCheckboxes = document.querySelectorAll('.game-checkbox');
        const bulkForm = document.getElementById('bulk-actions-form');

        if (!bulkActionSelect || !newStatusSelect || !selectAllCheckbox || !bulkForm) {
            return;
        }

        // Handle bulk action selection
        bulkActionSelect.addEventListener('change', function() {
            if (this.value === 'change_status') {
                newStatusSelect.style.display = 'inline-block';
            } else {
                newStatusSelect.style.display = 'none';
            }
        });

        // Handle select all checkbox
        selectAllCheckbox.addEventListener('change', function() {
            gameCheckboxes.forEach(function(checkbox) {
                checkbox.checked = this.checked;
            });
        });

        // Handle individual checkbox changes
        gameCheckboxes.forEach(function(checkbox) {
            checkbox.addEventListener('change', function() {
                updateSelectAllState();
            });
        });

        // Handle form submission
        bulkForm.addEventListener('submit', function(e) {
            const selectedGames = Array.from(gameCheckboxes).filter(cb => cb.checked);
            
            if (selectedGames.length === 0) {
                e.preventDefault();
                alert('Please select at least one game.');
                return;
            }

            const bulkAction = bulkActionSelect.value;
            
            if (bulkAction === 'change_status') {
                const newStatus = newStatusSelect.value;
                if (!newStatus) {
                    e.preventDefault();
                    alert('Please select a new status.');
                    return;
                }
                
                if (!confirm('Are you sure you want to change the status of ' + selectedGames.length + ' game(s)?')) {
                    e.preventDefault();
                    return;
                }
            } else if (bulkAction === 'delete') {
                if (!confirm('Are you sure you want to delete ' + selectedGames.length + ' game(s)? This action cannot be undone.')) {
                    e.preventDefault();
                    return;
                }
            } else if (bulkAction === '-1') {
                e.preventDefault();
                alert('Please select a bulk action.');
                return;
            }
        });
    }

    /**
     * Update select all checkbox state
     */
    function updateSelectAllState() {
        const selectAllCheckbox = document.getElementById('cb-select-all-1');
        const gameCheckboxes = document.querySelectorAll('.game-checkbox');
        
        if (!selectAllCheckbox || !gameCheckboxes.length) {
            return;
        }

        const checkedBoxes = Array.from(gameCheckboxes).filter(cb => cb.checked);
        
        if (checkedBoxes.length === 0) {
            selectAllCheckbox.indeterminate = false;
            selectAllCheckbox.checked = false;
        } else if (checkedBoxes.length === gameCheckboxes.length) {
            selectAllCheckbox.indeterminate = false;
            selectAllCheckbox.checked = true;
        } else {
            selectAllCheckbox.indeterminate = true;
        }
    }

    // Initialize bulk actions when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initBulkActions);
    } else {
        initBulkActions();
    }
    
})();
