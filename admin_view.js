function toggleGuestFields(userId) {
    console.log('toggleGuestFields called with userId:', userId); // Debugging statement
    var guestFields = document.getElementById('guest-fields-' + userId);

    if (guestFields.style.display === 'block') {
        guestFields.style.display = 'none'; // Hide the fields
    } else {
        guestFields.style.display = 'block'; // Show the fields
    }
}

function hideAllGuestFields() {
    console.log('hideAllGuestFields called'); // Debugging statement
    var guestFields = document.querySelectorAll('.guest-fields');
    guestFields.forEach(function(field) {
        field.classList.remove('open');
        field.style.display = 'none'; // Hide the fields
    });
    var toggleButtons = document.querySelectorAll('.toggle-button');
    toggleButtons.forEach(function(button) {
        button.style.width = '33.33%';
    });
}

function startRFIDScan(userId) {
    var timer = document.getElementById('timer_' + userId);
    timer.style.display = 'block';
    timer.value = 'Scanning...';
    console.log('Sending request to NodeMCU to start scanning'); // Debugging statement
    // Send a request to the NodeMCU to start scanning
    fetch('http://localhost/start_scan')
        .then(response => response.text())
        .then(data => {
            console.log('Received RFID tag from NodeMCU:', data); // Debugging statement
            // Update the RFID input field with the scanned RFID tag
            document.getElementById('rfid_' + userId).value = data;
            timer.style.display = 'none';
            // Send the scanned RFID tag to the RFID_Database.php endpoint
            fetch('RFID_Database.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'rfid_tag=' + encodeURIComponent(data) + '&user_id=' + encodeURIComponent(userId),
            })
                .then(response => response.text())
                .then(data => {
                    console.log('RFID tag saved to database:', data); // Debugging statement
                })
                .catch(error => {
                    console.error('Error saving RFID tag to database:', error);
                });
        })
        .catch(error => {
            console.error('Error scanning RFID tag:', error);
        });
}

function toggleRegistrierung() {
    console.log('toggleRegistrierung called'); // Debugging statement
    hideAllSections(); // Hide all other sections
    var registrierungSection = document.getElementById('registrierung-section');
    if (registrierungSection.style.display === 'none' || registrierungSection.style.display === '') {
        registrierungSection.style.display = 'block';
        console.log('Registrierung section shown'); // Debugging statement
    } else {
        registrierungSection.style.display = 'none';
        console.log('Registrierung section hidden'); // Debugging statement
    }
}

function hideAllSections() {
    console.log('hideAllSections called'); // Debugging statement
    var sections = document.querySelectorAll('.section');
    sections.forEach(function(section) {
        section.style.display = 'none';
    });
}

function confirmSaveSettings(form) {
    return confirm('Are you sure you want to save these settings?');
}

function confirmDeleteGuest(userId) {
    return confirm('Are you sure you want to delete this guest?');
}