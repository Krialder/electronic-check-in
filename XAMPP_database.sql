CREATE DATABASE IF NOT EXISTS kde_test2;
USE kde_test2;

-- Create Users table
CREATE TABLE IF NOT EXISTS Users 
(
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    rfid_tag VARCHAR(255) NOT NULL UNIQUE, -- Add UNIQUE constraint
    role VARCHAR(50),
    password VARCHAR(255) NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX (email), 
    INDEX (rfid_tag) 
);

-- Create Guest table
CREATE TABLE IF NOT EXISTS Guest
(
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255),
    phone VARCHAR(20),
    rfid_tag VARCHAR(255),
    role VARCHAR(50),
    password VARCHAR(255) NOT NULL,
    INDEX (email), 
    INDEX (rfid_tag) 
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
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX (event_name) 
);

-- Create Check-In table
CREATE TABLE IF NOT EXISTS CheckIn 
(
    checkin_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    event_id INT,
    checkin_time DATETIME DEFAULT CURRENT_TIMESTAMP,
    checkout_time DATETIME,
    status VARCHAR(50),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES Users(user_id),
    FOREIGN KEY (event_id) REFERENCES Events(event_id),
    INDEX (user_id), 
    INDEX (event_id) 
);

-- Create RFID Devices table
CREATE TABLE IF NOT EXISTS RFIDDevices 
(
    device_id INT AUTO_INCREMENT PRIMARY KEY,
    device_name VARCHAR(255),
    location VARCHAR(255),
    ip_address VARCHAR(255),
    INDEX (device_name), 
    INDEX (ip_address) 
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
    FOREIGN KEY (device_id) REFERENCES RFIDDevices(device_id),
    INDEX (user_id),
    INDEX (rfid_tag), 
    INDEX (device_id) 
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
    FOREIGN KEY (event_id) REFERENCES Events(event_id),
    INDEX (event_id)
);
