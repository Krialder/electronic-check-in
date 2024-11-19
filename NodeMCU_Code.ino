#include <ESP8266WiFi.h>
#include <ESP8266HTTPClient.h>
#include <NTPClient.h>
#include <WiFiUdp.h>
#include <ESP8266WebServer.h>
#include "config.h" // Include configuration file for Wi-Fi credentials and server URL

// Baud rate for serial communication with Mega 2560
#define BAUD_RATE 9600

// NTP Client to get time
WiFiUDP ntpUDP;
NTPClient timeClient(ntpUDP, "pool.ntp.org", 3600 * 1); // CET is UTC+1

WiFiClient wifiClient;
ESP8266WebServer server(80);

void setup() 
{
    Serial.begin(BAUD_RATE); // Initialize Serial for communication with Mega 2560
    WiFi.begin(ssid, password); // Connect to Wi-Fi

    // Wait for the Wi-Fi to connect
    int attempts = 0;
    while (WiFi.status() != WL_CONNECTED && attempts < 20) 
    {
        delay(2000);
        Serial.print("Connecting to Wi-Fi...");
        Serial.print("Attempt: ");
        Serial.println(attempts + 1);
        Serial.println("Wi-Fi Status: ");
        Serial.println(WiFi.status()); //printing Wi-Fi status
        Serial.println(getWiFiStatusMeaning(WiFi.status())); //Print Wi-Fi status meaning
        attempts++;
    }

    if (WiFi.status() == WL_CONNECTED) 
    {
        Serial.println("Connected to Wi-Fi");
        long rssi = WiFi.RSSI();
        Serial.print("Signal strength (RSSI): ");
        Serial.print(rssi);
        Serial.println(" dBm");
    } 
    else 
    {
        Serial.println("Failed to connect to Wi-Fi");
        return; // Exit setup if Wi-Fi connection fails
    }

    // Initialize NTP Client
    timeClient.begin();
    server.on("/start_scan", HTTP_GET, handleStartScan);
    server.begin();
}

void loop() 
{
    timeClient.update();
    int currentHour = timeClient.getHours();
    int currentMinute = timeClient.getMinutes();

    // Auto-logout at 16:00 CET
    if (currentHour == 16 && currentMinute == 0) 
    {
        autoLogout();
        delay(60000); // Wait for a minute to avoid multiple logout requests
    }

    if (Serial.available()) 
    {
        // Read RFID data from Mega 2560
        String rfidTag = Serial.readStringUntil('\n');
        rfidTag.trim(); // Remove any whitespace/newline characters

        if (rfidTag.length() > 0 && rfidTag.length() <= 10) // Validate RFID tag length
        {
            // Send RFID data to the server
            if (WiFi.status() == WL_CONNECTED) 
            {
                HTTPClient http;
                http.begin(wifiClient, serverName); // Use the specific database endpoint
                http.addHeader("Content-Type", "application/x-www-form-urlencoded");

                String httpRequestData = "rfid=" + rfidTag;
                int httpResponseCode = http.POST(httpRequestData);

                if (httpResponseCode > 0) 
                {
                    String response = http.getString();
                    Serial.println("Server Response: " + response);
                } 
                else 
                {
                    Serial.printf("Error sending POST request: %s\n", http.errorToString(httpResponseCode).c_str());
                }
                // Close the connection
                http.end(); 
            } 
            else 
            {
                Serial.println("Wi-Fi not connected");
            }
        }
        else
        {
            Serial.println("Invalid RFID tag length");
        }
    }
    server.handleClient();
}

void autoLogout() 
{
    if (WiFi.status() == WL_CONNECTED) 
    {
        HTTPClient http;
        http.begin(wifiClient, serverName); // Use the specific database endpoint
        http.addHeader("Content-Type", "application/x-www-form-urlencoded");

        String httpRequestData = "auto_logout=true";
        int httpResponseCode = http.POST(httpRequestData);

        if (httpResponseCode > 0) 
        {
            String response = http.getString();
            Serial.println("Auto-logout Response: " + response);
        } 
        else 
        {
            Serial.printf("Error sending auto-logout POST request: %s\n", http.errorToString(httpResponseCode).c_str());
        }

        http.end(); // Close the connection
    } 
    else 
    {
        Serial.println("Wi-Fi not connected for auto-logout");
    }
}

String getWiFiStatusMeaning(int status) 
{
    switch (status) 
    {
        case WL_IDLE_STATUS:
            return "Idle";
        case WL_NO_SSID_AVAIL:
            return "No SSID Available";
        case WL_SCAN_COMPLETED:
            return "Scan Completed";
        case WL_CONNECTED:
            return "Connected";
        case WL_CONNECT_FAILED:
            return "Connect Failed";
        case WL_CONNECTION_LOST:
            return "Connection Lost";
        case WL_DISCONNECTED:
            return "Disconnected";
        default:
            return "Unknown Status";
    }
}

void handleStartScan() 
{
    Serial.println("Received /start_scan request"); // Debugging statement
    String rfidTag = "";
    unsigned long startTime = millis();
    while (rfidTag == "" && millis() - startTime < 10000) // Wait for up to 10 seconds
    {
        if (Serial.available()) 
        {
            rfidTag = Serial.readStringUntil('\n');
            rfidTag.trim();
            Serial.println("RFID Tag: " + rfidTag); // Debugging statement
        }
    }
    if (rfidTag != "") 
    {
        server.send(200, "text/plain", rfidTag);
    } 
    else 
    {
        Serial.println("No RFID tag found"); // Debugging statement
        server.send(200, "text/plain", "No RFID tag found");
    }
}