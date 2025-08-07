// Add this to your main JavaScript file
function checkNewItems() {
    // Create XMLHttpRequest to check for new items
    const xhr = new XMLHttpRequest();
    xhr.open('POST', 'check_new_items.php', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

    xhr.onload = function() {
        if (this.status === 200) {
            const response = JSON.parse(this.responseText);
            
            if (response.newProducts > 0 || response.newEvents > 0) {
                showNotification(response.newProducts, response.newEvents);
            }
        }
    };

    // No need to send last login time as it's stored in the session
    xhr.send();
}

function showNotification(newProducts, newEvents) {
    // Create notification box
    const notification = document.createElement('div');
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        background-color: white;
        border: 2px solid #3498db;
        border-radius: 8px;
        padding: 15px;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        z-index: 9999;
        font-family: Arial, sans-serif;
        max-width: 300px;
    `;

    // Create notification content
    let message = '<h3 style="margin: 0 0 10px 0; color: #3498db;">What\'s New!</h3>';
    message += '<div style="margin-right: 20px;">';
    
    if (newProducts > 0) {
        message += `<p style="margin: 5px 0;">• ${newProducts} new product${newProducts > 1 ? 's' : ''}</p>`;
    }
    if (newEvents > 0) {
        message += `<p style="margin: 5px 0;">• ${newEvents} new event${newEvents > 1 ? 's' : ''}</p>`;
    }
    
    message += '</div>';

    // Add close button
    const closeButton = `<span style="
        position: absolute;
        top: 5px;
        right: 10px;
        cursor: pointer;
        font-size: 20px;
        color: #666;"
    >×</span>`;

    notification.innerHTML = closeButton + message;

    // Add click handler for close button
    notification.querySelector('span').onclick = function() {
        document.body.removeChild(notification);
    };

    // Add to page
    document.body.appendChild(notification);

    // Auto-remove after 7 seconds
    setTimeout(() => {
        if (document.body.contains(notification)) {
            document.body.removeChild(notification);
        }
    }, 7000);
}

// Call when page loads
document.addEventListener('DOMContentLoaded', checkNewItems);