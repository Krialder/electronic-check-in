// Function to update the username field based on the forname and surname fields
function updateUsername() {
    const forname = document.getElementById('forname').value.trim();
    const surname = document.getElementById('surname').value.trim();
    document.getElementById('name').value = forname + ' ' + surname;
}

// Function to validate the password field
function validatePassword() {
    const password = document.getElementById('password').value;
    const password2 = document.getElementById('password2').value;
    const pattern = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*\W).{9,}$/;

    // Check if the passwords match
    if (password !== password2) {
        alert('Passwords do not match');
        return false;
    }

    // Check if the password meets the requirements
    else if (!pattern.test(password)) {
        alert('Password must contain at least 9 characters, one lowercase letter, one uppercase letter, one number, and one special character');
        return false;
    }
    return true;
}