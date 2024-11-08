#include <SPI.h>
#include <MFRC522.h>

#define SS_PIN 10
#define RST_PIN 9

class RFIDManager 
{
    public:
    void begin() 
    {
        SPI.begin();
        rfid.PCD_Init();
        Serial.println("RFID Initialized"); // Debugging statement
    }

    String readRFID() 
    {
        if (!rfid.PICC_IsNewCardPresent()) 
        {
            return "";
        }
        if (!rfid.PICC_ReadCardSerial()) 
        {
            Serial.println("Failed to read card serial"); // Debugging statement
            return "";
        }

        String rfidTag = "";
        for (byte i = 0; i < rfid.uid.size; i++) 
        {
            rfidTag += String(rfid.uid.uidByte[i], HEX);
        }
        rfid.PICC_HaltA();
        return rfidTag;
    }
    private:
    MFRC522 rfid = MFRC522(SS_PIN, RST_PIN);
};

RFIDManager rfidManager;

void setup() 
{
    Serial.begin(9600); // Initialize Serial communication with NodeMCU
    Serial.println("Setup started"); // Debugging statement
    rfidManager.begin();
    Serial.println("Setup completed"); // Debugging statement
}

void loop()
{
    String rfidTag = rfidManager.readRFID();
    if (rfidTag != "")
    {
        Serial.println("RFID Tag: " + rfidTag); // Send RFID tag to NodeMCU via Serial
        delay(5000); // Wait for 5 seconds after a successful scan
        Serial.println("Ready for next scan"); // Debugging statement
    }
    else
    {
        delay(1000); // Scan every second
    }
}