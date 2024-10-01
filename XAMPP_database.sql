CREATE DATABASE IF NOT EXISTS kde_test2;
USE kde_test2;

-- Create Users table
CREATE TABLE IF NOT EXISTS Users 
(
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    rfid_tag VARCHAR(255) NOT NULL,
    role VARCHAR(50),
    password VARCHAR(255) NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Create Events table
CREATE TABLE IF NOT EXISTS Events 
(
    event_id INT AUTO_INCREMENT PRIMARY KEY,
    event_name VARCHAR(255) NOT NULL,
    location VARCHAR(255),
    start_time DATETIME,
    end_time DATETIME,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Create Check-In table
CREATE TABLE IF NOT EXISTS CheckIn 
(
    checkin_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    event_id INT,
    checkin_time DATETIME DEFAULT CURRENT_TIMESTAMP,
    status VARCHAR(50),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES Users(user_id),
    FOREIGN KEY (event_id) REFERENCES Events(event_id)
);

-- Create RFID Devices table
CREATE TABLE IF NOT EXISTS RFIDDevices 
(
    device_id INT AUTO_INCREMENT PRIMARY KEY,
    device_name VARCHAR(255),
    location VARCHAR(255),
    ip_address VARCHAR(255)
);

-- Create AccessLogs table
CREATE TABLE IF NOT EXISTS AccessLogs 
(
    log_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    rfid_tag VARCHAR(255),
    device_id INT,
    access_time DATETIME DEFAULT CURRENT_TIMESTAMP,
    status VARCHAR(50),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES Users(user_id),
    FOREIGN KEY (device_id) REFERENCES RFIDDevices(device_id)
);

-- Create Reports table
CREATE TABLE IF NOT EXISTS Reports 
(
    report_id INT AUTO_INCREMENT PRIMARY KEY,
    event_id INT,
    total_checkins INT,
    avg_checkin_time TIME,
    generated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (event_id) REFERENCES Events(event_id)
);

-- Insert example data into Users table
INSERT INTO Users (name, email, phone, rfid_tag, role, password)
VALUES 
('John Doe', 'john.doe@example.com', '1234567890', 'RFID123456', 'admin', PASSWORD('password123')),
('Jane Smith', 'jane.smith@example.com', '0987654321', 'RFID654321', 'user', PASSWORD('password456'));

-- Insert example data into Events table
INSERT INTO Events (event_name, location, start_time, end_time)
VALUES 
('Tech Conference', 'Conference Hall A', '2023-10-01 09:00:00', '2023-10-01 17:00:00');

-- Insert example data into CheckIn table
INSERT INTO CheckIn (user_id, event_id, checkin_time, status)
VALUES 
(1, 1, '2023-10-01 09:05:00', 'checked-in'),
(2, 1, '2023-10-01 09:10:00', 'checked-in');

-- Insert example data into RFIDDevices table
INSERT INTO RFIDDevices (device_name, location, ip_address)
VALUES 
('Entrance Scanner', 'Main Entrance', '192.168.1.10'),
('Conference Room Scanner', 'Conference Hall A', '192.168.1.11');

-- Insert example data into AccessLogs table
INSERT INTO AccessLogs (user_id, rfid_tag, device_id, access_time, status)
VALUES 
(1, 'RFID123456', 1, '2023-10-01 09:00:00', 'granted'),
(2, 'RFID654321', 1, '2023-10-01 09:05:00', 'granted'),
(1, 'RFID123456', 2, '2023-10-01 09:05:00', 'granted');

-- Insert example data into Reports table
INSERT INTO Reports (event_id, total_checkins, avg_checkin_time)
VALUES 
(1, 2, '00:05:00');