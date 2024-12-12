#include <ESP8266WiFi.h>
#include <ESP8266HTTPClient.h>
#include <NTPClient.h>
#include <WiFiUdp.h>
#include <ESP8266WebServer.h>

// Wi-Fi credentials
const char* ssid = "Luftuberwachungssystem";
const char* password = "Ux957Zi%xqbY6vPHCm#4X";

// Server URLs
const char* startScanUrl = "http://localhost/start_scan.php";
const char* logAccessUrl = "http://localhost/RFID_Database.php";

// Baud rate for serial communication with Mega 2560
#define BAUD_RATE 9600 

// NTP Client to get time
WiFiUDP ntpUDP;
NTPClient timeClient(ntpUDP, "pool.ntp.org", 3600 * 1); // CET is UTC+1

WiFiClient wifiClient;
ESP8266WebServer server(80);

// This function initializes the serial communication, connects to Wi-Fi, initializes the NTP client, and starts the web server.
void setup() 
{
    Serial.begin(BAUD_RATE);
    WiFi.begin(ssid, password); 

    Serial.println("Setup completed"); 
    Serial.println("Connecting to Wi-Fi..."); 

    // Wait for the Wi-Fi to connect
    int attempts = 0;
    while (WiFi.waitForConnectResult() != WL_CONNECTED && attempts < 20) 
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
    }

    // Initialize NTP Client
    timeClient.begin();
    server.on("/start_scan", HTTP_GET, handleStartScan); // Define route for start_scan
    server.begin(); 
    Serial.println("Server started"); 
}

// This function runs continuously, updating the NTP client, checking for auto-logout, reading RFID data, and handling client requests.
void loop() 
{
    timeClient.update(); 
    int currentHour = timeClient.getHours();
    int currentMinute = timeClient.getMinutes();

    if (currentHour == 16 && currentMinute == 0) 
    {
        Serial.println("Auto-logout triggered"); 
        autoLogout();
        delay(60000); 
    }

    if (Serial.available()) 
    {
        String rfidTag = Serial.readStringUntil('\n');
        rfidTag.trim(); 

        if (rfidTag.length() > 0) 
        {
            Serial.println("RFID Tag received: " + rfidTag); 
            logAccess(rfidTag);
        }
    }
    server.handleClient(); 
}

// This function logs the access by sending the RFID tag to the server.
void logAccess(String rfidTag) 
{
    if (WiFi.status() == WL_CONNECTED) 
    {
        HTTPClient http;
        http.begin(wifiClient, logAccessUrl); // Use the specific database endpoint
        http.addHeader("Content-Type", "application/x-www-form-urlencoded");

        String httpRequestData = "rfid=" + rfidTag;
        int httpResponseCode = http.POST(httpRequestData);

        if (httpResponseCode > 0) 
        {
            String response = http.getString();
            Serial.println("Log Access Response: " + response);
        } 
        else 
        {
            Serial.println("Error sending POST request to RFID_Database.php");
        }

        http.end(); 
    } 
    else 
    {
        Serial.println("Wi-Fi not connected for logging access");
    }
}

// This function sends an auto-logout request to the server at a specified time.
void autoLogout() 
{
    if (WiFi.status() == WL_CONNECTED) 
    {
        HTTPClient http;
        http.begin(wifiClient, logAccessUrl); // Use the specific database endpoint
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
            Serial.println("Error sending auto-logout POST request");
        }

        http.end(); 
    } 
    else 
    {
        Serial.println("Wi-Fi not connected for auto-logout");
    }
}

// This function translates Wi-Fi status codes into human-readable strings for easier debugging.
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

// This function handles the /start_scan HTTP GET request and attempts to read an RFID tag within 60 seconds.
void handleStartScan() 
{
    Serial.println("Received /start_scan request"); 
    String rfidTag = "";
    unsigned long startTime = millis();
    while (rfidTag == "" && millis() - startTime < 60000) 
    {
        if (Serial.available()) 
        {
            rfidTag = Serial.readStringUntil('\n');
            rfidTag.trim();
            Serial.println("RFID Tag: " + rfidTag); 
            break; 
        }
    }
    if (rfidTag != "") 
    {
        server.send(200, "text/plain", rfidTag);
    } 
    else 
    {
        Serial.println("No RFID tag found"); 
        server.send(200, "text/plain", "No RFID tag found");
    }
}