#include <SPI.h>
#include <MFRC522.h>

#define SS_PIN 10 
#define RST_PIN 9 

// RFIDManager class handles the initialization and reading of RFID tags using the MFRC522 module
class RFIDManager 
{
    public:
    void begin() 
    {
        SPI.begin(); // Initialize SPI bus to communicate with RFID reader
        rfid.PCD_Init();
        Serial.println("RFID Initialized"); 
    }

    String readRFID() 
    {
        if (!rfid.PICC_IsNewCardPresent()) 
        {
            return ""; 
        }
        if (!rfid.PICC_ReadCardSerial()) 
        {
            Serial.println("Failed to read card serial"); 
            return ""; 
        }

        String rfidTag = "";
        for (byte i = 0; i < rfid.uid.size; i++) 
        {
            rfidTag += String(rfid.uid.uidByte[i], HEX); // Convert UID bytes to HEX string for readability
        }
        rfid.PICC_HaltA(); // Halt PICC to stop reading
        return rfidTag; 
    }
    private:
    MFRC522 rfid = MFRC522(SS_PIN, RST_PIN); // Create MFRC522 instance with defined pins
};

RFIDManager rfidManager; 

// The setup function initializes serial communication and the RFID manager.
void setup() 
{
    Serial.begin(9600);
    Serial.println("Setup started"); 
    rfidManager.begin();
    Serial.println("Setup completed"); 
}

// Continuously checks for new RFID tags and processes them.
void loop()
{
    String rfidTag = rfidManager.readRFID(); 
    if (rfidTag != "")
    {
        Serial.println("RFID Tag: " + rfidTag);
        delay(5000); 
        Serial.println("Ready for next scan"); 
    }
    else
    {
        delay(1000); 
    }
}