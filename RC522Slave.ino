#include <SPI.h>
#include <MFRC522.h>

#define SS_PIN 53
#define RST_PIN 9

MFRC522 rfid(SS_PIN, RST_PIN);

void setup() {
    Serial.begin(9600); 
    SPI.begin();
    rfid.PCD_Init();
    Serial.println("RFID Initialized");
}

void loop() 
{
    if (Serial.available()) 
    {
        String command = Serial.readStringUntil('\n');
        command.trim();
        
        if (command == "READ_RFID") 
        {
            if (!rfid.PICC_IsNewCardPresent()) 
            {
                Serial.println("NO_CARD");
                return;
            }
            if (!rfid.PICC_ReadCardSerial()) 
            {
                Serial.println("READ_FAIL");
                return;
            }
            
            String rfidTag = "";
            for (byte i = 0; i < rfid.uid.size; i++) 
            {
                rfidTag += String(rfid.uid.uidByte[i], HEX);
            }
            rfid.PICC_HaltA();
            Serial.println("RFID_TAG:" + rfidTag); 
        }
    }
}