#include <SPI.h>
#include <MFRC522.h>
#include <ESP8266WiFi.h>
#include <ESP8266HTTPClient.h>
#include <ESP8266WebServer.h>

// Define the static IP address, gateway, and subnet mask
IPAddress staticIP(192, 168, 2, 186); 
IPAddress gateway(192, 168, 2, 1);    
IPAddress subnet(255, 255, 255, 0);   

// Wi-Fi credentials
const char* ssid = "Luftuberwachungssystem";
const char* password = "Ux957Zi%xqbY6vPHCm#4X";

// Server URL
const char* logAccessUrl = "http://192.168.2.186/RFID_Database.php";

// Baud rate for serial communication
#define BAUD_RATE 9600 

#define SS_PIN D3  // GPIO 0
#define RST_PIN D4 // GPIO 2

MFRC522 rfid(SS_PIN, RST_PIN);
WiFiClient wifiClient;
ESP8266WebServer server(80);

String lastRFID = "";
unsigned long lastProcessedTime = 0;
const unsigned long rfidCooldown = 10000; // Cooldown period in milliseconds

void handleStatus() 
{
    String response = "RFID ready";
    if (lastRFID != "") 
    {
        response += " - Last RFID: " + lastRFID;
    }
    server.send(200, "text/plain", response);
}

void handleGetRFID() 
{
    if (lastRFID != "") 
    {
        server.send(200, "text/plain", lastRFID);
        lastRFID = ""; // Reset RFID after it got requested
    } 
    else 
    {
        server.send(200, "text/plain", "No RFID");
    }
}

void handleResetRFID() 
{
    lastRFID = "";
    server.send(200, "text/plain", "RFID reset");
}

void handleRoot() 
{
    server.send(200, "text/plain", "Welcome to the RFID Reader Web Server");
}

void sendRFIDToServer(String rfid) 
{
    if (WiFi.status() == WL_CONNECTED) 
    {
        HTTPClient http;
        http.setTimeout(10000); // Set timeout to 10 seconds
        http.begin(wifiClient, logAccessUrl);
        http.addHeader("Content-Type", "application/x-www-form-urlencoded");
        String postData = "rfid=" + rfid;
        int httpResponseCode = http.POST(postData);
        if (httpResponseCode > 0) {
            String response = http.getString();
            Serial.println("Server response: " + response);
        } else {
            Serial.println("Error sending RFID to server: " + String(httpResponseCode));
        }
        http.end();
    } 
    else 
    {
        Serial.println("WiFi not connected");
    }
}

void setup() 
{
    Serial.begin(BAUD_RATE);
    SPI.begin();
    rfid.PCD_Init();
    Serial.println("RFID Initialized");

    // Configure static IP
    if (!WiFi.config(staticIP, gateway, subnet)) 
    {
        Serial.println("STA Failed to configure");
    }

    WiFi.begin(ssid, password); 
    Serial.println("Connecting to Wi-Fi..."); 

    // Wait for the Wi-Fi to connect
    unsigned long startAttemptTime = millis();
    while (WiFi.status() != WL_CONNECTED && millis() - startAttemptTime < 30000) // 30 seconds timeout
    {
        delay(1000);
        Serial.print(".");
    }

    if (WiFi.status() != WL_CONNECTED) 
    {
        Serial.println("\nFailed to connect to Wi-Fi");
        return;
    }

    Serial.println("\nConnected to Wi-Fi");
    Serial.print("NodeMCU IP Address: ");
    Serial.println(WiFi.localIP());

    // Start the web server
    server.on("/", handleRoot); 
    server.on("/status", handleStatus);
    server.on("/getRFID", handleGetRFID);
    server.on("/resetRFID", handleResetRFID); 
    server.begin();
    Serial.println("Web server started");
}

void loop() 
{
    server.handleClient();

    if (!rfid.PICC_IsNewCardPresent() || !rfid.PICC_ReadCardSerial()) 
    {
        delay(50);
        return;
    }

    String currentRFID = "";
    for (byte i = 0; i < rfid.uid.size; i++) 
    {
        currentRFID += String(rfid.uid.uidByte[i], HEX);
    }
    currentRFID.toUpperCase();
    Serial.println("RFID Tag: " + currentRFID);

    unsigned long currentTime = millis();
    if (currentRFID == lastRFID && (currentTime - lastProcessedTime) < rfidCooldown) 
    {
        Serial.println("RFID " + currentRFID + " ignored due to cooldown");
        delay(50);
        return;
    }

    lastRFID = currentRFID;
    lastProcessedTime = currentTime;

    sendRFIDToServer(currentRFID);

    // Wait for the RFID tag to be removed
    while (rfid.PICC_IsNewCardPresent() && rfid.PICC_ReadCardSerial()) 
    {
        delay(50);
    }

    delay(1000);
}