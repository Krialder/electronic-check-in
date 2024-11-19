function toggleGuestFields(userId) 
{
    var guestFields = document.getElementById('guest-fields-' + userId);
    if (guestFields) 
    {
        guestFields.style.display = (guestFields.style.display === 'none') ? 'block' : 'none';
    }
}

function startRFIDScan(userId) 
{
    var timer = document.getElementById('timer_' + userId);
    timer.style.display = 'block';
    timer.value = 'Scanning...';

    console.log('Sending request to NodeMCU to start scanning'); // Debugging statement

    // Send a request to the NodeMCU to start scanning
    fetch('http://localhost/start_scan')
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.text();
        })
        .then(data => {
            console.log('Received RFID tag from NodeMCU:', data); // Debugging statement

            // Update the RFID input field with the scanned RFID tag
            document.getElementById('rfid_' + userId).value = data;
            timer.style.display = 'none';

            // Send the scanned RFID tag to the RFID_Database.php endpoint
            return fetch('RFID_Database.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: 'rfid=' + encodeURIComponent(data)
            });
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.text();
        })
        .then(result => {
            console.log('Server Response:', result); // Debugging statement
        })
        .catch(error => {
            console.error('Error:', error); // Debugging statement
            timer.value = 'Scan failed';
        });
}