var clipboard = new ClipboardJS('.clipboardjs');
    clipboard.on('success', function(e) 
    { 
        $('#copy-url-button').html('Copied!');
        e.clearSelection();
    }); 

function confirmDelete(event) {
    event.preventDefault();
    if (confirm('Are you sure you want to delete this message?')) {
        document.getElementById('delete-form').submit();
    }
}

function DeleteChat(event) {    
    event.preventDefault();
    if (confirm('Are you sure you want to delete this Conversation?')) {
        document.getElementById('delete-chat').submit();
    }
}