<?php include 'session_check.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="navbar">
        <a href="/dashboard.html">📊 Dashboard</a>
        <a href="#my-checkins">🕒 My Check-ins</a>
        <a href="#my-analytics">📈 My Analytics</a>
        <a href="#events-attended">📅 Events Attended</a>
        <a href="#rfid-devices">📟 RFID Devices</a>
        <a href="/account-settings.html">⚙️ Account Settings</a>
        <a href="#notifications">🔔 Notifications</a>
        <a href="#download-data">💾 Download Data</a>
        <a href="#help-support">❓ Help & Support</a>
        <a href="logout.php">🔓 Log Out</a>
   </div>

   <div class="content">
    <h2>Recent Check-ins</h2>
    <ul id="recent-checkins"></ul>

    <h2>Key Analytics</h2>
    <p id="total-checkins"></p>
    <p id="avg-checkin-time"></p>
</div>

<script>
    // Fetch data and populate the dashboard
    fetch('/dashboard_fetch_data.php')
        .then(response => response.json())
        .then(data => 
        {
            console.log('Fetched Data:', data); // Debugging line

            if (data.error) 
            {
                console.error('Error:', data.error);
                return;
            }

            const recentCheckinsList = document.getElementById('recent-checkins');
            data.recent_checkins.forEach(checkin => 
            {
                const listItem = document.createElement('li');
                listItem.textContent = `${checkin.name} checked into ${checkin.event_name} at ${checkin.checkin_time}`;
                recentCheckinsList.appendChild(listItem);
            });

            document.getElementById('total-checkins').textContent = `Total Check-ins: ${data.analytics.total_checkins}`;
            document.getElementById('avg-checkin-time').textContent = `Average Check-in Time: ${data.analytics.avg_checkin_time}`;
        });
// Load dark mode setting from localStorage
    if (localStorage.getItem('darkMode') === 'enabled') 
    {
        document.body.classList.add('dark-mode');
    }

</script>
</body>
</html>