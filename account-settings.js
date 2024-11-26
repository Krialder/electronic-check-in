document.addEventListener('DOMContentLoaded', () => {
    const darkModeRadio = document.getElementById('dark-mode');
    const lightModeRadio = document.getElementById('light-mode');
    const body = document.body;

    // Load dark mode setting from localStorage
    if (localStorage.getItem('darkMode') === 'enabled') {
        body.classList.add('dark-mode');
        darkModeRadio.checked = true;
    } else {
        body.classList.remove('dark-mode');
        lightModeRadio.checked = true;
    }

    // Apply dark mode and save it to localStorage
    darkModeRadio.addEventListener('change', () => {
        if (darkModeRadio.checked) {
            body.classList.add('dark-mode');
            body.classList.remove('light-mode');
            localStorage.setItem('darkMode', 'enabled');
        }
    });

    // Apply light mode and save it to localStorage
    lightModeRadio.addEventListener('change', () => {
        if (lightModeRadio.checked) {
            body.classList.remove('dark-mode');
            body.classList.add('light-mode');
            localStorage.setItem('darkMode', 'disabled');
        }
    });

    // Fetch admin_view.php content and insert it into the div
    fetch('admin_view.php')
        .then(response => response.text())
        .then(data => {
            document.getElementById('admin-view-content').innerHTML = data;
            displayErrorMessage();
        })
        .catch(error => console.error('Error fetching admin_view.php:', error));

    function toggleGuestFields(userId) {
        var guestFields = document.getElementById('guest-fields-' + userId);
        if (guestFields.style.display === 'none') {
            guestFields.style.display = 'block';
        } else {
            guestFields.style.display = 'none';
        }
    }

    // Display error message if present in query parameters
    function displayErrorMessage() {
        const urlParams = new URLSearchParams(window.location.search);
        const errorMessage = urlParams.get('error');
        const userId = urlParams.get('user_id');
        if (errorMessage && userId) {
            const errorDiv = document.createElement('div');
            errorDiv.style.color = 'red';
            errorDiv.textContent = decodeURIComponent(errorMessage);
            const guestFields = document.getElementById('guest-fields-' + userId);
            if (guestFields) {
                guestFields.insertBefore(errorDiv, guestFields.firstChild);
                guestFields.style.display = 'block';
            }
            // Clear the query parameters from the URL
            window.history.replaceState({}, document.title, window.location.pathname);
        }
    }
});