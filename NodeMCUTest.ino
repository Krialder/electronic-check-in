#include <ESP8266WiFi.h>

const char* ssid = "Luftuberwachungssystem";
const char* password = "Ux957Zi%xqbY6vPHCm#4X";

void setup()
{
    Serial.begin(9600);
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
    if (Serial.available())
    {
        String rfidTag = Serial.readStringUntil('\n');
        rfidTag.trim();
        Serial.println("RFID Tag: " + rfidTag);
    }
}