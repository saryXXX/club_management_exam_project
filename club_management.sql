CREATE DATABASE club_management;
USE club_management;

-- Members table
CREATE TABLE members (
    member_id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    phone VARCHAR(20),
    join_date DATE NOT NULL,
    membership_type ENUM('regular', 'premium', 'vip') NOT NULL,
    active BOOLEAN DEFAULT TRUE
);

-- Staff table
CREATE TABLE staff (
    staff_id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    phone VARCHAR(20),
    position VARCHAR(50) NOT NULL,
    hire_date DATE NOT NULL,
    salary DECIMAL(10,2)
);

-- Teams table
CREATE TABLE teams (
    team_id INT AUTO_INCREMENT PRIMARY KEY,
    team_name VARCHAR(50) NOT NULL,
    sport_type VARCHAR(50) NOT NULL,
    coach_id INT,
    created_at DATE NOT NULL,
    FOREIGN KEY (coach_id) REFERENCES staff(staff_id)
);

-- Member-Team junction table (many-to-many relationship)
CREATE TABLE member_team (
    member_id INT,
    team_id INT,
    join_date DATE NOT NULL,
    PRIMARY KEY (member_id, team_id),
    FOREIGN KEY (member_id) REFERENCES members(member_id),
    FOREIGN KEY (team_id) REFERENCES teams(team_id)
);

-- Events table
CREATE TABLE events (
    event_id INT AUTO_INCREMENT PRIMARY KEY,
    event_name VARCHAR(100) NOT NULL,
    description TEXT,
    event_date DATETIME NOT NULL,
    location VARCHAR(100) NOT NULL,
    organizer_id INT,
    team_id INT,
    FOREIGN KEY (organizer_id) REFERENCES staff(staff_id),
    FOREIGN KEY (team_id) REFERENCES teams(team_id)
);

-- Equipment table
CREATE TABLE equipment (
    equipment_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    type VARCHAR(50) NOT NULL,
    purchase_date DATE NOT NULL,
    condition_status ENUM('excellent', 'good', 'fair', 'poor', 'broken') NOT NULL,
    assigned_to INT,
    FOREIGN KEY (assigned_to) REFERENCES teams(team_id)
);

-- Payments table
CREATE TABLE payments (
    payment_id INT AUTO_INCREMENT PRIMARY KEY,
    member_id INT NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    payment_date DATE NOT NULL,
    payment_method ENUM('cash', 'credit', 'bank transfer') NOT NULL,
    period_covered VARCHAR(50) NOT NULL,
    FOREIGN KEY (member_id) REFERENCES members(member_id)
);
-- Users table
CREATE TABLE users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'staff', 'member') NOT NULL
);

-- Insert admin account
INSERT INTO users (email, password, role) 
VALUES ('admin@admin', '$2y$10$8K1p/a0dL1LXMIhuX2G7AOBXn0/KZR7OYXtX4TlHRNsgNfkHWV.Ma', 'admin');
-- Note: password hash is for 'admin123'
-- Add user_id to members table
ALTER TABLE members
ADD COLUMN user_id INT UNIQUE,
ADD FOREIGN KEY (user_id) REFERENCES users(user_id);

-- Add user_id to staff table
ALTER TABLE staff
ADD COLUMN user_id INT UNIQUE,
ADD FOREIGN KEY (user_id) REFERENCES users(user_id);