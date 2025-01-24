#include <SPI.h>
#include <MFRC522.h>

#define SS_PIN 53
#define RST_PIN 9

MFRC522 rfid(SS_PIN, RST_PIN);

void setup()
{
    Serial.begin(9600);
    SPI.begin();
    rfid.PCD_Init();
    Serial.println("RFID Initialized");
}

void loop()
{
    if (!rfid.PICC_IsNewCardPresent())
    {
        return;
    }
    if (!rfid.PICC_ReadCardSerial())
    {
        Serial.println("Failed to read card serial");
        return;
    }
    
    String rfidTag = "";
    for (byte i = 0; i < rfid.uid.size; i++)
    {
        rfidTag += String(rfid.uid.uidByte[i], HEX);
    }
    rfid.PICC_HaltA();
    Serial.println("Sending RFID Tag: " + rfidTag);
    Serial.println(rfidTag);
    delay(5000); 
    Serial.println("Ready for next scan");
}