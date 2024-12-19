console.log('JavaScript loaded successfully');

// Declare variables for RFID timeout and countdown interval
let rfidTimeout;
let countdownInterval;
let timeLeft;


function startRFIDScan(userId, jsonOnly = false) 
{
    console.log('startRFIDScan called for user ID:', userId);

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
    }, 1000);

    // Get the RFID input element
    const rfidInput = document.getElementById('rfid_' + userId);
    const onRFIDScan = function(event) 
    {
        if (!rfidInput) return;

        // Capture the RFID code from input (-> Enter key might finalize input from scanner)
        if (event.key === 'Enter') 
        {
            // Trim and assign RFID value, then remove event listener and clear interval
            rfidInput.value = rfidInput.value.trim();
            alert("RFID scanned and assigned to user.");
            document.removeEventListener('keydown', onRFIDScan);
            clearInterval(countdownInterval);
            timerDisplay.textContent = "RFID scan completed.";
        }
        else 
        {
            // Append the key to the RFID input value
            rfidInput.value += event.key;
        }
    };

    // Add event listener for RFID scan
    document.addEventListener('keydown', onRFIDScan);

    // Simulate RFID scanning process
    fetch('http://192.168.2.186/start_scan', 
    {
        method: 'POST',
        headers: 
        {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ rfid_tag: rfidInput.value })
    })
    .then(response => response.json())
    .then(result => 
    {
        console.log('Server Response:', result); 
        if (result.status === 'success') 
        {
            rfidInput.value = result.rfid_tag;
            alert("RFID scanned and assigned to user.");
            document.removeEventListener('keydown', onRFIDScan);
            clearInterval(countdownInterval);
            timerDisplay.textContent = "RFID scan completed.";
        } 
        else 
        {
            timerDisplay.textContent = result.message;
        }
    })
    .catch(error => 
    {
        console.error('Error during RFID scan:', error);
        alert("RFID scan failed.");
        timerDisplay.textContent = "RFID scan failed.";
    });

    // Timeout after 1 minute if no RFID is scanned
    rfidTimeout = setTimeout(() => 
    {
        alert("RFID scan timed out.");
        document.removeEventListener('keydown', onRFIDScan);
        clearInterval(countdownInterval);
        timerDisplay.textContent = "RFID scan timed out.";
    }, 60000);

    if (jsonOnly) 
    {
        document.body.classList.add('json-only');
    }
}

// Function to end JSON-only mode
function endJsonOnlyMode() 
{
    document.body.classList.remove('json-only');
}