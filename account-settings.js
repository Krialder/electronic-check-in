// Load dark mode setting from localStorage
const themeToggle = document.getElementById('theme-toggle');
if (localStorage.getItem('darkMode') === 'enabled') {
    document.body.classList.add('dark-mode');
    themeToggle.checked = true;
}

// Apply dark mode and save setting to localStorage
themeToggle.addEventListener('change', () => {
    if (themeToggle.checked) {
        document.body.classList.add('dark-mode');
        localStorage.setItem('darkMode', 'enabled');
    } else {
        document.body.classList.remove('dark-mode');
        localStorage.setItem('darkMode', 'disabled');
    }
});

// Save dark mode setting to localStorage on form submission
document.getElementById('settings-form').addEventListener('submit', (event) => {
    event.preventDefault();
    if (themeToggle.checked) {
        localStorage.setItem('darkMode', 'enabled');
    } else {
        localStorage.setItem('darkMode', 'disabled');
    }
    alert('Settings saved!');
});

// Fetch admin_view.php content and insert it into the div
fetch('admin_view.php')
    .then(response => response.text())
    .then(data => {
        document.getElementById('admin-view-content').innerHTML = data;
        displayErrorMessage();
    })
    .catch(error => console.error('Error fetching admin_view.php:', error));

// Display error message if present in query parameters
function displayErrorMessage() {
    const urlParams = new URLSearchParams(window.location.search);
    const errorMessage = urlParams.get('error');
    if (errorMessage) {
        const errorDiv = document.getElementById('error-message');
        errorDiv.textContent = sanitizeHTML(decodeURIComponent(errorMessage));
        errorDiv.style.display = 'block';
        // Clear the query parameters from the URL
        window.history.replaceState({}, document.title, window.location.pathname);
    }
}

// Function to sanitize HTML
function sanitizeHTML(str) {
    var temp = document.createElement('div');
    temp.textContent = str;
    return temp.innerHTML;
}

// Call displayErrorMessage on page load
document.addEventListener('DOMContentLoaded', displayErrorMessage);

// Toggle guest fields visibility
function toggleGuestFields(userId) {
    var guestFields = document.getElementById('guest-fields-' + userId);
    if (guestFields.style.display === 'none') {
        guestFields.style.display = 'block';
    } else {
        guestFields.style.display = 'none';
    }
}