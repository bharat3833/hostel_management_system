/*===== SHOW NAVBAR  =====*/ 
const showNavbar = (toggleId, navId, bodyId, headerId) =>{
    const toggle = document.getElementById(toggleId),
    nav = document.getElementById(navId),
    bodypd = document.getElementById(bodyId),
    headerpd = document.getElementById(headerId)

    // Validate that all variables exist
    if(toggle && nav && bodypd && headerpd){
        toggle.addEventListener('click', () =>{
            // show navbar
            nav.classList.toggle('showa')
            // change icon
            toggle.classList.toggle('bx-x')
            // add padding to body
            bodypd.classList.toggle('body-pd')
            // add padding to header
            headerpd.classList.toggle('body-pd')
        })
    }
}

showNavbar('header-toggle','nav-bar','body-pd','header')

/*===== CONFIRMATION DIALOGS =====*/
function showConfirmationDialog(message, onConfirm, onCancel) {
    // Remove any existing dialogs first
    const existingDialog = document.querySelector('.confirmation-dialog');
    if (existingDialog) {
        existingDialog.remove();
    }
    
    const dialog = document.createElement('div');
    dialog.className = 'confirmation-dialog';
    
    // Create unique IDs for this dialog
    const dialogId = 'dialog_' + Date.now();
    const confirmId = 'confirm_' + Date.now();
    const cancelId = 'cancel_' + Date.now();
    
    dialog.innerHTML = `
        <div class="confirmation-content">
            <h4>Confirm Action</h4>
            <p>${message}</p>
            <div class="confirmation-buttons">
                <button class="btn btn-danger" id="${confirmId}">Delete</button>
                <button class="btn btn-secondary" id="${cancelId}">Cancel</button>
            </div>
        </div>
    `;
    
    document.body.appendChild(dialog);
    
    // Add event listeners
    document.getElementById(confirmId).addEventListener('click', function() {
        onConfirm();
        dialog.remove();
    });
    
    document.getElementById(cancelId).addEventListener('click', function() {
        if (onCancel) onCancel();
        dialog.remove();
    });
    
    // Close dialog when clicking outside
    dialog.addEventListener('click', function(e) {
        if (e.target === dialog) {
            if (onCancel) onCancel();
            dialog.remove();
        }
    });
}

// Add confirmation to all delete buttons
document.addEventListener('DOMContentLoaded', function() {
    const deleteButtons = document.querySelectorAll('button[name="removeUser"], button[name="removeRoom"], button[name="removecourse"], button[name="removestudetails"]');
    
    deleteButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            
            const form = this.closest('form');
            const action = this.name;
            let message = 'Are you sure you want to delete this item?';
            
            if (action === 'removeUser') message = 'Are you sure you want to delete this user?';
            else if (action === 'removeRoom') message = 'Are you sure you want to delete this room?';
            else if (action === 'removecourse') message = 'Are you sure you want to delete this course?';
            else if (action === 'removestudetails') message = 'Are you sure you want to delete this student record?';
            
            // Use simple browser confirm instead of custom dialog
            if (confirm(message)) {
                form.submit();
            }
        });
    });
});
