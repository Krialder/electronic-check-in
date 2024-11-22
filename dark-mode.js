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

    // Save dark mode setting to cookies on form submission
    document.getElementById('settings-form').addEventListener('submit', (event) => {
        event.preventDefault();
        const formData = new FormData(event.target);

        if (themeToggle.checked) {
            document.cookie = "darkMode=enabled; path=/";
        } else {
            document.cookie = "darkMode=disabled; path=/";
        }

        fetch('update_settings.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.text())
        .then(data => {
            document.getElementById('error-message').textContent = data;
            // Redirect to account-settings.html after saving
            window.location.href = 'http://localhost/account-settings.html';
        })
        .catch(error => {
            console.error('Error:', error);
        });
    });
});