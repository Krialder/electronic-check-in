console.log('JavaScript loaded successfully');

// Declare variables for RFID timeout and countdown interval
let rfidTimeout;
let countdownInterval;
let timeLeft;
let onRFIDScan;

function startRFIDScan(userId, jsonOnly = false) 
{
    console.log('startRFIDScan called for user ID:', userId);

    try {
        // Clear any existing timeouts and intervals to avoid conflicts
        clearTimeout(rfidTimeout);
        clearInterval(countdownInterval);
        alert("Please scan RFID within 1 minute.");

        timeLeft = 60;
        const timerDisplay = document.getElementById('timer_' + userId);

        // Check if the timer display element exists
        if (!timerDisplay) 
        {
            console.error('Timer display element not found for user ID:', userId);
            return;
        }

        // Ensure the timer display is visible
        timerDisplay.style.display = 'block';
        timerDisplay.textContent = 'Time left: ' + timeLeft + ' seconds';

        // Update the timer every second until time runs out
        countdownInterval = setInterval(() => 
        {
            try {
                if (timeLeft <= 0) 
                {
                    clearInterval(countdownInterval);
                    timerDisplay.textContent = "RFID scan timed out.";
                } 
                else 
                {
                    timerDisplay.textContent = 'Time left: ' + timeLeft + ' seconds';
                    console.log('Time left:', timeLeft); 

                    timeLeft--;
                }
            } catch (error) {
                console.error('Error updating timer:', error);
            }
        }, 1000);

        // Get the RFID input element
        const rfidInput = document.getElementById('rfid_' + userId);
        onRFIDScan = function(event) 
        {
            try {
                if (!rfidInput) return;

                console.log('Key pressed:', event.key); 

                // Capture the RFID code from input (-> Enter key might finalize input from scanner)
                if (event.key === 'Enter') 
                {
                    // Trim and assign RFID value, then remove event listener and clear interval
                    rfidInput.value = rfidInput.value.trim();
                    console.log('RFID scanned:', rfidInput.value); 
                    alert("RFID scanned and assigned to user.");
                    document.removeEventListener('keydown', onRFIDScan);
                    clearInterval(countdownInterval);
                    timerDisplay.textContent = "RFID scan completed.";

                    // Send the RFID to the server
                    sendRFIDToServer(rfidInput.value, userId);
                }
                else 
                {
                    // Append the key to the RFID input value
                    rfidInput.value += event.key;
                }
            } catch (error) {
                console.error('Error during RFID scan:', error);
            }
        };

        // Add event listener for RFID scan
        document.addEventListener('keydown', onRFIDScan);

        // Timeout after 1 minute if no RFID is scanned
        rfidTimeout = setTimeout(() => 
        {
            try {
                alert("RFID scan timed out.");
                document.removeEventListener('keydown', onRFIDScan);
                clearInterval(countdownInterval);
                timerDisplay.textContent = "RFID scan timed out.";
            } catch (error) {
                console.error('Error during RFID timeout:', error);
            }
        }, 60000);

        if (jsonOnly) 
        {
            document.body.classList.add('json-only');
        }
    } catch (error) {
        console.error('Error in startRFIDScan:', error);
    }
}

// Function to send RFID to the server
function sendRFIDToServer(rfid, userId) 
{
    try {
        const xhr = new XMLHttpRequest();
        xhr.open('POST', 'start_scan.php', true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.onreadystatechange = function() 
        {
            try {
                if (xhr.readyState === 4) 
                {
                    if (xhr.status === 200) 
                    {
                        const response = JSON.parse(xhr.responseText);
                        alert(response.message);
                    } 
                    else 
                    {
                        alert('Error: ' + xhr.status);
                    }
                }
            } catch (error) {
                console.error('Error in sendRFIDToServer onreadystatechange:', error);
            }
        };
        xhr.send('rfid=' + encodeURIComponent(rfid) + '&user_id=' + encodeURIComponent(userId));
    } catch (error) {
        console.error('Error in sendRFIDToServer:', error);
    }
}

// Function to end JSON-only mode
function endJsonOnlyMode() 
{
    try {
        document.body.classList.remove('json-only');
    } catch (error) {
        console.error('Error in endJsonOnlyMode:', error);
    }
}

// Function to cancel RFID scan
function cancelRFIDScan(userId) 
{
    try {
        console.log('cancelRFIDScan called for user ID:', userId);
        clearTimeout(rfidTimeout);
        clearInterval(countdownInterval);
        const timerDisplay = document.getElementById('timer_' + userId);
        if (timerDisplay) 
        {
            timerDisplay.textContent = "RFID scan canceled.";
        }
        document.removeEventListener('keydown', onRFIDScan);
        alert("RFID scan canceled.");
    } catch (error) {
        console.error('Error in cancelRFIDScan:', error);
    }
}

// Function to reset RFID scan
function resetRFIDScan(userId) 
{
    try {
        console.log('resetRFIDScan called for user ID:', userId);
        clearTimeout(rfidTimeout);
        clearInterval(countdownInterval);
        const timerDisplay = document.getElementById('timer_' + userId);
        if (timerDisplay) 
        {
            timerDisplay.textContent = "RFID scan reset.";
        }
        document.removeEventListener('keydown', onRFIDScan);
        alert("RFID scan reset.");
    } catch (error) {
        console.error('Error in resetRFIDScan:', error);
    }
}