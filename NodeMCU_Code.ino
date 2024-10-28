#include <ESP8266WiFi.h>
#include <ESP8266HTTPClient.h>
#include <NTPClient.h>
#include <WiFiUdp.h>

// Wi-Fi credentials
const char* ssid = "Luftuberwachungssystem";
const char* password = "Ux957Zi%xqbY6vPHCm#4X";

// Server URL
const char* serverName = "http://192.168.2.150"; 

// Baud rate for serial communication with Mega 2560
#define BAUD_RATE 9600

// NTP Client to get time
WiFiUDP ntpUDP;
NTPClient timeClient(ntpUDP, "pool.ntp.org", 3600 * 1); // CET is UTC+1

WiFiClient wifiClient;

void setup() 
{
    Serial.begin(BAUD_RATE); // Initialize Serial for communication with Mega 2560
    WiFi.begin(ssid, password); // Connect to Wi-Fi

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

        if (rfidTag.length() > 0) 
        {
            // Send RFID data to the server
            if (WiFi.status() == WL_CONNECTED) 
            {
                HTTPClient http;
                http.begin(wifiClient, serverName);
                http.addHeader("Content-Type", "application/x-www-form-urlencoded");
              //Nicht gesperrt!!! Fatal
                String httpRequestData = "rfid=" + rfidTag;
                int httpResponseCode = http.POST(httpRequestData);

                if (httpResponseCode > 0) 
                {
                    String response = http.getString();
                    Serial.println("Server Response: " + response);
                } 
                else 
                {
                  Serial.println("Error sending POST request");
                }
                // Close the connection
                http.end(); 
            } 
            else 
            {
                Serial.println("Wi-Fi not connected");
            }
        }
    }
}

void autoLogout() 
{
    if (WiFi.status() == WL_CONNECTED) 
    {
        HTTPClient http;
        http.begin(wifiClient, serverName);
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