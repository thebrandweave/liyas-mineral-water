<!-- Delete Confirmation Modal -->
<div id="deleteConfirmModal" class="delete-confirm-modal">
	<div class="delete-confirm-dialog">
		<div class="delete-confirm-header">
			<h3>
				<i class='bx bx-trash' style="color:#dc2626;"></i>
				<span>Confirm Deletion</span>
			</h3>
			<button type="button" class="close-btn" onclick="closeDeleteModal()" aria-label="Close">
				<i class='bx bx-x'></i>
			</button>
		</div>
		<div class="delete-confirm-body">
			<div class="delete-confirm-warning-icon">
				<i class='bx bx-error'></i>
			</div>
			<p id="deleteConfirmMessage" style="word-break: break-word; hyphens: auto;">
				You are about to delete this item. This action cannot be undone.
			</p>
			<p style="font-size:0.85rem; color:var(--text-secondary); margin-top:0.25rem; word-break: break-word;">
				Please confirm to proceed.
			</p>
		</div>
		<div class="delete-confirm-footer">
			<button type="button" class="btn btn-secondary" onclick="closeDeleteModal()">
				<i class='bx bx-x'></i> Cancel
			</button>
			<button type="button" class="btn-action btn-delete noselect" id="confirmDeleteBtn">
				<span class="text">Delete</span>
				<span class="icon">
					<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><path d="M24 20.188l-8.315-8.209 8.2-8.282-3.697-3.697-8.212 8.318-8.31-8.203-3.666 3.666 8.321 8.24-8.206 8.313 3.666 3.666 8.237-8.318 8.285 8.203z"></path></svg>
				</span>
			</button>
		</div>
	</div>
</div>

