document.addEventListener('DOMContentLoaded', () => {
    const themeToggle = document.getElementById('theme-toggle');
    const body = document.body;

    // Load dark mode setting from localStorage
    if (localStorage.getItem('darkMode') === 'enabled') {
        body.classList.add('dark-mode');
        themeToggle.checked = true;
    }

    // Apply dark mode without saving it
    themeToggle.addEventListener('change', () => {
        if (themeToggle.checked) {
            body.classList.add('dark-mode');
        } else {
            body.classList.remove('dark-mode');
        }
    });

    // Save dark mode setting to localStorage on form submission
    document.getElementById('settings-form').addEventListener('submit', (event) => {
        event.preventDefault();
        const formData = new FormData(event.target);

        if (themeToggle.checked) {
            localStorage.setItem('darkMode', 'enabled');
        } else {
            localStorage.setItem('darkMode', 'disabled');
        }

        fetch('update_settings.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.text())
        .then(data => {
            document.getElementById('error-message').textContent = data;
        })
        .catch(error => {
            console.error('Error:', error);
        });
    });
});

function displayErrorMessage() {
    const urlParams = new URLSearchParams(window.location.search);
    const errorMessage = urlParams.get('error');
    const userId = urlParams.get('user_id');
    if (errorMessage && userId) {
        const errorDiv = document.createElement('div');
        errorDiv.textContent = `Error: ${errorMessage}`;
        document.getElementById('error-message').appendChild(errorDiv);
    }
}