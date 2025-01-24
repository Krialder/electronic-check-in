#include <ESP8266WiFi.h>

const char* ssid = "Luftuberwachungssystem";
const char* password = "Ux957Zi%xqbY6vPHCm#4X";

void setup() 
{
    Serial.begin(9600);
    WiFi.mode(WIFI_STA);
    WiFi.begin(ssid, password);
    while (WiFi.status() != WL_CONNECTED) 
    {
        delay(1000);
        Serial.println("Connecting to WiFi...");
    }
    Serial.println("Connected to WiFi");
}

void loop() 
{
    Serial.println("READ_RFID");
    delay(1000);

    if (Serial.available()) 
    {
        String response = Serial.readStringUntil('\n');
        response.trim();
        
        if (response.startsWith("RFID_TAG:")) 
        {
            String rfidTag = response.substring(9);
            Serial.println("RFID Tag: " + rfidTag);
        } 
        else 
        {
            Serial.println("No RFID tag found or read failed");
        }
    }
    delay(5000); 
}