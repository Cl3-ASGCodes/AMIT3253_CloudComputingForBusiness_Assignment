CREATE DATABASE IF NOT EXISTS eventhalls_db;
USE eventhalls_db;

CREATE TABLE login (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(50) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  user_type ENUM('admin', 'student', 'guest') NOT NULL DEFAULT 'guest'
);
INSERT INTO login (id, username, password_hash, user_type) VALUES
(1, 'admin', '$2y$10$HI3gLmyD4OGmfNLAGUIL8.eBhhKu5nzL7wTDws.6mUNO9V44kyM5q', 'admin');

CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  email VARCHAR(150) NOT NULL UNIQUE,
  id_number VARCHAR(20) NULL,
  faculty VARCHAR(150) NULL,
  date_of_birth DATE NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  login_id INT NULL,
  FOREIGN KEY (login_id) REFERENCES login(id)
);

-- Seed admin account: admin@example.com / admin123
-- Change this password immediately in any real deployment.
INSERT INTO users (name, email, login_id) VALUES
('Admin', 'admin@example.com', 1);

-- A facility is a SPORT/CATEGORY (e.g. "Badminton"), not a specific physical court.
-- Adding a brand new sport (e.g. Pickleball, Paddleball) is just one row here plus
-- one or more rows in `courts` below - no code changes needed anywhere else.
CREATE TABLE facilities (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  location VARCHAR(100) NOT NULL,
  capacity INT NOT NULL DEFAULT 1,
  description TEXT NULL,
  flexible BOOLEAN DEFAULT FALSE,
  features TEXT NULL,
  image_url VARCHAR(500) NULL,
  layout_url VARCHAR(500) NULL
);

-- A court is a specific bookable instance of a facility (Court A, Lane 3, Table 1...).
-- Bookings/closures reference a court, never a facility directly - that's what lets
-- two courts of the same sport be booked independently for the same date/time.
CREATE TABLE courts (
  id INT AUTO_INCREMENT PRIMARY KEY,
  facility_id INT NOT NULL,
  name VARCHAR(50) NOT NULL,
  location VARCHAR(100) NULL,
  FOREIGN KEY (facility_id) REFERENCES facilities(id)
);

INSERT INTO facilities (name, location, capacity, description, flexible, features) VALUES
('Dewan Tunku Abdul Rahman', 'DTAR', 1000,
 'Our premier flagship venue designed for grand-scale events.',
 TRUE,
 '1,000+ Guest Capacity\nLED Screen & PA System\nDedicated VIP Holding Room\nIdeal for Concerts & Graduations');

SET @dtar_id = LAST_INSERT_ID();
INSERT INTO courts (facility_id, name) VALUES
(@dtar_id, 'Main Hall');

INSERT INTO facilities (name, location, capacity, description, flexible, features) VALUES
('Function Hall', 'Arena, \n1st, 2nd and 5th Floor', 200,
 'Variety layout of halls to accommodate up to 200+ guests for gatherings and product showcases.',
 TRUE,
 'Portable Sound Systems & TV Display\nLarge Windows with KL view\nFlexible Seating Arrangements');
SET @func_hall_id = LAST_INSERT_ID();
INSERT INTO courts (facility_id, name) VALUES 
(@func_hall_id, 'TA-113'),
(@func_hall_id, 'TA-205'),
(@func_hall_id, 'TA-501'),
(@func_hall_id, 'TA-505');

INSERT INTO facilities (name, location, capacity, description, flexible, features) VALUES
('Multi-Purpose Hall', 'Arena, Level 2', 300,
 'A unique double-deck facility that offers massive vertical clearance and flexible floor plans for event hosting.',
 TRUE,
 'LED Screen, PA System and Portable Stage\nHigh Ceiling Architecture\nIdeal for Exhibitions, Gathering and Sports Events');
SET @multi_purpose_id = LAST_INSERT_ID();
INSERT INTO courts (facility_id, name) VALUES (@multi_purpose_id, 'Court 1');

INSERT INTO facilities (name, location, capacity, description, materials, rules) VALUES
('Basketball', 'Sports Complex, Level 1', 10,
 'A full indoor basketball court with adjustable hoops, suitable for 5-a-side games or shooting practice.',
 'Maple hardwood sprung flooring with a polyurethane coating for grip and consistent ball bounce. Height-adjustable breakaway rims with tempered glass backboards, and LED lighting rated for indoor ball sports.',
 'Non-marking indoor court shoes are mandatory.\nNo food or drinks on the court.\nMaximum of 10 players per booked session.\nHanging or swinging on the rim is strictly prohibited.');
SET @basketball_id = LAST_INSERT_ID();
INSERT INTO courts (facility_id, name) VALUES (@basketball_id, 'Court 1');

INSERT INTO facilities (name, location, capacity, description, materials, rules) VALUES
('Volleyball', 'Sports Complex, Level 1', 12,
 'An indoor volleyball court with a regulation-height net, suitable for 6-a-side matches.',
 'Sprung wooden flooring with a textured vinyl finish for traction. Net posts are padded aluminium with a crank-adjustable regulation-height net.',
 'Non-marking sports shoes are mandatory.\nMaximum of 12 players per booked session.\nNo food or drinks inside the court.\nPlease reset the net height if you adjusted it.');
SET @volleyball_id = LAST_INSERT_ID();
INSERT INTO courts (facility_id, name) VALUES (@volleyball_id, 'Court 1');

INSERT INTO facilities (name, location, capacity, description, materials, rules) VALUES
('Swimming', 'Sports Complex, Ground Floor', 30,
 'An 8-lane 25m indoor swimming pool with a dedicated shallow end for beginners.',
 'Reinforced concrete shell with a ceramic tile lining, chlorine-filtered and temperature-controlled to 27-29°C. Lane ropes are anti-wave polypropylene.',
 'Proper swimwear is mandatory; no cotton clothing in the pool.\nShower before entering the pool.\nMaximum of 30 swimmers per booked session.\nNo diving in the shallow end.\nChildren under 12 must be accompanied by an adult.');
SET @swimming_id = LAST_INSERT_ID();
INSERT INTO courts (facility_id, name) VALUES (@swimming_id, 'Main Pool');

UPDATE facilities SET image_url = '/uploads/Event Halls/DTAR_HALL_A.jpg' WHERE name = 'Dewan Tunku Abdul Rahman';
UPDATE facilities SET image_url = '/uploads/sample-futsal.jpg' WHERE name = 'Futsal';
UPDATE facilities SET image_url = '/uploads/sample-squash.jpg' WHERE name = 'Squash';
UPDATE facilities SET image_url = '/uploads/sample-basketball.jpg' WHERE name = 'Basketball';
UPDATE facilities SET image_url = '/uploads/sample-volleyball.jpg' WHERE name = 'Volleyball';
UPDATE facilities SET image_url = '/uploads/sample-swimming.jpg' WHERE name = 'Swimming';
UPDATE facilities SET image_url = '/uploads/sample-gym.jpg' WHERE name = 'Gym';
UPDATE facilities SET image_url = '/uploads/sample-tennis.jpg' WHERE name = 'Tennis';
UPDATE facilities SET image_url = '/uploads/sample-tabletennis.jpg' WHERE name = 'Table Tennis';
UPDATE facilities SET image_url = '/uploads/sample-yoga.jpg' WHERE name = 'Yoga & Aerobics';
UPDATE facilities SET image_url = '/uploads/sample-climbing.jpg' WHERE name = 'Climbing';
UPDATE facilities SET image_url = '/uploads/sample-football.jpg' WHERE name = 'Football';
UPDATE facilities SET image_url = '/uploads/sample-bowling.jpg' WHERE name = 'Bowling';

CREATE TABLE time_slots (
  id INT AUTO_INCREMENT PRIMARY KEY,
  label VARCHAR(20) NOT NULL,
  sort_order INT NOT NULL
);

INSERT INTO time_slots (label, sort_order) VALUES
('08:00 - 09:00', 1),
('09:00 - 10:00', 2),
('10:00 - 11:00', 3),
('11:00 - 12:00', 4),
('14:00 - 15:00', 5),
('15:00 - 16:00', 6),
('16:00 - 17:00', 7),
('17:00 - 18:00', 8),
('18:00 - 19:00', 9),
('19:00 - 20:00', 10),
('20:00 - 21:00', 11);

CREATE TABLE bookings (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  court_id INT NOT NULL,
  time_slot_id INT NOT NULL,
  booking_date DATE NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY unique_slot (court_id, booking_date, time_slot_id),
  FOREIGN KEY (user_id) REFERENCES users(id),
  FOREIGN KEY (court_id) REFERENCES courts(id),
  FOREIGN KEY (time_slot_id) REFERENCES time_slots(id)
);

-- time_slot_id = NULL means the court is closed for the whole day
CREATE TABLE closures (
  id INT AUTO_INCREMENT PRIMARY KEY,
  court_id INT NOT NULL,
  closure_date DATE NOT NULL,
  time_slot_id INT NULL,
  reason VARCHAR(150) NOT NULL,
  FOREIGN KEY (court_id) REFERENCES courts(id),
  FOREIGN KEY (time_slot_id) REFERENCES time_slots(id)
);

-- In-app notices shown to a user on their next visit, e.g. when an admin
-- closure cancels one of their existing bookings. No email/SMS involved.
CREATE TABLE notifications (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  message VARCHAR(500) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  read_at TIMESTAMP NULL,
  FOREIGN KEY (user_id) REFERENCES users(id)
);

CREATE TABLE testimonials (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  facility_id INT NOT NULL,
  comment TEXT NOT NULL,
  rating TINYINT NOT NULL DEFAULT 5,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id),
  FOREIGN KEY (facility_id) REFERENCES facilities(id)
);

-- Public contact form submissions - no login required to send one
CREATE TABLE contact_messages (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  email VARCHAR(150) NOT NULL,
  subject VARCHAR(150) NOT NULL,
  message TEXT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- PHP sessions are stored here instead of on local disk, so that any EC2
-- instance behind an ALB/ASG can read a session written by a different
-- instance. See auth.php's DbSessionHandler.
CREATE TABLE sessions (
  id VARCHAR(128) PRIMARY KEY,
  data MEDIUMTEXT NOT NULL,
  last_activity INT NOT NULL,
  INDEX idx_last_activity (last_activity)
);
