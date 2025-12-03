/**
 * Delete Confirmation Modal Handler
 * Reusable delete confirmation functionality
 */
(function() {
	'use strict';
	
	let deleteItemId = null;
	let deleteCallback = null;
	let modal = null;
	let confirmBtn = null;
	let message = null;
	
	// Initialize modal handlers
	function init() {
		modal = document.getElementById('deleteConfirmModal');
		confirmBtn = document.getElementById('confirmDeleteBtn');
		message = document.getElementById('deleteConfirmMessage');
		
		if (!modal || !confirmBtn || !message) return;
		
		// Confirm button click
		confirmBtn.addEventListener('click', handleConfirm);
		
		// Close modal on overlay click
		modal.addEventListener('click', function(e) {
			if (e.target === this) {
				closeDeleteModal();
			}
		});
		
		// Close modal on ESC key (single listener for all modals)
		document.addEventListener('keydown', function(e) {
			if (e.key === 'Escape' && modal && modal.classList.contains('active')) {
				closeDeleteModal();
			}
		});
	}
	
	// Handle confirm button click
	function handleConfirm() {
		if (deleteItemId !== null && deleteCallback) {
			deleteCallback(deleteItemId);
		}
	}
	
	// Public function to show delete confirmation
	window.showDeleteConfirm = function(itemId, itemName, callback, customMessage) {
		if (!modal || !message) {
			console.error('Delete confirmation modal not found. Make sure delete_confirm_modal.php is included.');
			return;
		}
		
		deleteItemId = itemId;
		deleteCallback = callback || defaultDeleteCallback;
		
		// Set message
		message.textContent = customMessage || `Are you sure you want to delete "${itemName}"?\n\nThis action cannot be undone!`;
		
		// Show modal
		modal.classList.add('active');
		document.body.classList.add('modal-active');
	};
	
	// Public function to close modal
	window.closeDeleteModal = function() {
		if (modal) {
			modal.classList.remove('active');
			document.body.classList.remove('modal-active');
		}
		deleteItemId = null;
		deleteCallback = null;
	};
	
	// Default delete callback (redirects to delete URL)
	function defaultDeleteCallback(itemId) {
		const url = new URL(window.location.href);
		url.searchParams.set('delete', itemId);
		window.location.href = url.toString();
	}
	
	// Initialize when DOM is ready
	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', init);
	} else {
		init();
	}
})();

