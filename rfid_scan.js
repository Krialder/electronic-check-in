// Debug log to ensure JavaScript is loaded
console.log('JavaScript loaded successfully');

// Declare variables for RFID timeout and countdown interval
let rfidTimeout;
let countdownInterval;
let timeLeft;

// Function to start RFID scan for a specific user
function startRFIDScan(userId) 
{
    // Debug log
    console.log('startRFIDScan called for user ID:', userId);

    // Clear any existing timeouts and intervals
    clearTimeout(rfidTimeout);
    clearInterval(countdownInterval);
    alert("Please scan RFID within 1 minute.");

    // Set the countdown time to 60 seconds
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

    // Update the timer every second
    countdownInterval = setInterval(() => 
    {
        if (timeLeft <= 0) 
        {
            // Clear interval and update timer display when time is up
            clearInterval(countdownInterval);
            timerDisplay.textContent = "RFID scan timed out.";
        } 
        else 
        {
            // Update timer display and decrement time left
            timerDisplay.textContent = 'Time left: ' + timeLeft + ' seconds';
            console.log('Time left:', timeLeft); // Debug log

            // Show a pop-up message every 15 seconds
            if (timeLeft % 15 === 0) 
            {
                alert('Time left: ' + timeLeft + ' seconds');
            }

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

    // Timeout after 1 minute if no RFID is scanned
    rfidTimeout = setTimeout(() => 
    {
        // Alert timeout, remove event listener, and clear interval
        alert("RFID scan timed out.");
        document.removeEventListener('keydown', onRFIDScan);
        clearInterval(countdownInterval);
        timerDisplay.textContent = "RFID scan timed out.";
    }, 60000);
}