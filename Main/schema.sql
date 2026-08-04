CREATE DATABASE IF NOT EXISTS eventhalls_db;
USE eventhalls_db;

CREATE TABLE login (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(50) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  user_type ENUM('admin', 'staff', 'student', 'guest') NOT NULL DEFAULT 'guest'
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
  FOREIGN KEY (login_id) REFERENCES login(id) ON DELETE CASCADE
);

-- Seed admin account: admin@example.com / admin123
INSERT INTO users (name, email, login_id) VALUES
('Admin', 'admin@example.com', 1);

-- Facilities (Dewan Tunku Abdul Rahman, Function Hall, Multi-Purpose Hall, etc.)
CREATE TABLE facilities (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  location VARCHAR(100) NOT NULL,
  capacity INT NOT NULL DEFAULT 1,
  flexible BOOLEAN DEFAULT FALSE,
  description TEXT NULL,
  features TEXT NULL,
  layout_url VARCHAR(500) NULL
);

-- Courts/Halls within a facility
CREATE TABLE courts (
  id INT AUTO_INCREMENT PRIMARY KEY,
  facility_id INT NOT NULL,
  name VARCHAR(50) NOT NULL,
  location VARCHAR(100) NULL,
  FOREIGN KEY (facility_id) REFERENCES facilities(id) ON DELETE CASCADE
);

CREATE TABLE facility_images (
  id INT AUTO_INCREMENT PRIMARY KEY,
  facility_id INT NOT NULL,
  court_id INT NULL,
  image_url VARCHAR(500) NOT NULL,
  description VARCHAR(255) NULL,
  FOREIGN KEY (facility_id) REFERENCES facilities(id) ON DELETE CASCADE,
  FOREIGN KEY (court_id) REFERENCES courts(id) ON DELETE SET NULL
);

INSERT INTO facilities (name, location, capacity, description, flexible, features) VALUES
('Dewan Tunku Abdul Rahman', 'DTAR', 1000,
 'Our premier flagship venue designed for grand-scale events.',
 TRUE,
 '1,000+ Guest Capacity\nLED Screen & PA System\nDedicated VIP Holding Room\nIdeal for Concerts & Graduations');

SET @dtar_id = LAST_INSERT_ID();
INSERT INTO courts (facility_id, name) VALUES
(@dtar_id, 'Main Hall');
INSERT INTO facility_images (facility_id, image_url, description) VALUES
(@dtar_id, '/uploads/Event Halls/DTAR_HALL_A.jpg', 'Audience View'),
(@dtar_id, '/uploads/Event Halls/DTAR_STAGE.jpg', 'View from Stage'),
(@dtar_id, '/uploads/Event Halls/DTAR_FOYER.jpg', 'Foyer'),
(@dtar_id, '/uploads/Event Halls/DTAR-VIP.jpg', 'VIP Holding Room'),
(@dtar_id, '/uploads/Event Halls/DTAR_GRAD.jpg', 'Previous Activity - Graduation Ceremony');

INSERT INTO facilities (name, location, capacity, description, flexible, features) VALUES
('Function Hall', 'Arena, \n1st, 2nd and 5th Floor', 200,
 'Variety layout of halls to accommodate up to 200+ guests for gatherings and product showcases.',
 TRUE,
 'Portable Sound Systems & TV Display\nLarge Windows with KL view\nFlexible Seating Arrangements');
SET @func_hall_id = LAST_INSERT_ID();
INSERT INTO courts (facility_id, name) VALUES 
(@func_hall_id, 'TA-133'),
(@func_hall_id, 'TA-205'),
(@func_hall_id, 'TA-501'),
(@func_hall_id, 'TA-505');
INSERT INTO facility_images (facility_id, image_url, description) VALUES
(@func_hall_id, '/uploads/Event Halls/FH_TA133_1.jpg', 'TA133'),
(@func_hall_id, '/uploads/Event Halls/FH_TA133_2.jpg', 'TA133'),
(@func_hall_id, '/uploads/Event Halls/FH_TA205_1.jpg', 'TA205'),
(@func_hall_id, '/uploads/Event Halls/FH_TA205_2.jpg', 'TA205'),
(@func_hall_id, '/uploads/Event Halls/FH_TA501_1.jpg', 'TA501'),
(@func_hall_id, '/uploads/Event Halls/FH_TA501_2.jpg', 'TA501'),
(@func_hall_id, '/uploads/Event Halls/FH_TA501_3.jpg', 'TA501'),
(@func_hall_id, '/uploads/Event Halls/FH_TA505_1.jpg', 'TA505'),
(@func_hall_id, '/uploads/Event Halls/FH_TA505_2.jpg', 'TA505'),
(@func_hall_id, '/uploads/Event Halls/FH_TA505_3.jpg', 'TA505');

INSERT INTO facilities (name, location, capacity, description, flexible, features) VALUES
('Multi-Purpose Hall', 'Arena, Level 2', 300,
 'A unique double-deck facility that offers massive vertical clearance and flexible floor plans for event hosting.',
 TRUE,
 'LED Screen, PA System and Portable Stage\nHigh Ceiling Architecture\nIdeal for Exhibitions, Gathering and Sports Events');
SET @multi_purpose_id = LAST_INSERT_ID();
INSERT INTO courts (facility_id, name) VALUES (@multi_purpose_id, 'Court 1');
INSERT INTO facility_images (facility_id, image_url, description) VALUES
(@multi_purpose_id, '/uploads/Event Halls/MPH_CSS_1.jpg', 'Consert/Seminar Setting'),
(@multi_purpose_id, '/uploads/Event Halls/MPH_RTS_1.jpg', 'Round Table Setting'),
(@multi_purpose_id, '/uploads/Event Halls/MPH_RTS_2.jpg', 'Round Table Setting'),
(@multi_purpose_id, '/uploads/Event Halls/MPH_RTS_3.jpg', 'Round Table Setting');

INSERT INTO facilities (name, location, capacity, description, features) VALUES
('RED BRICKS THEATRE', 'Arena, Level 5', 352,
 'A premium performance auditorium that offers maximum comfort for attendees.',
 '352 Sofa Seating\nLED Screen, PA System, Lighting and Portable Stage\nHolding Areas for Performers and Guests\nPerfect for Movie Screenings, Talks and Acoustic Performances');
SET @RB_Theatre_id = LAST_INSERT_ID();
INSERT INTO courts (facility_id, name) VALUES (@RB_Theatre_id, 'Court 1');
INSERT INTO facility_images (facility_id, image_url, description) VALUES
(@RB_Theatre_id, '/uploads/Event Halls/RBTA_AUD.jpg','Audiences View'),
(@RB_Theatre_id, '/uploads/Event Halls/RBTA_STG.jpg','View from Stage'),
(@RB_Theatre_id, '/uploads/Event Halls/RBTA_GHA.jpg','Guest Holding Area'),
(@RB_Theatre_id, '/uploads/Event Halls/RBTA_PA_1.jpg','Previous Activity (1)'),
(@RB_Theatre_id, '/uploads/Event Halls/PBTA_PA_2.jpg','Previous Activity (2)');

INSERT INTO facilities (name, location, capacity, description, flexible, features) VALUES
('SMALL SCALE FUNCTION ROOM', 'Club House, Level 2', 150,
 'Accommodates 150+ guests featuring a pre-function area for networking and catering.',
 TRUE,
 'LED Screen, PA System and Stage\nFlexible Floor Plan Arrangements\nIdeal for Seminars and Workshops');
SET @ssfr_id = LAST_INSERT_ID();
INSERT INTO courts (facility_id, name) VALUES (@ssfr_id, 'Function Room 1');
INSERT INTO facility_images (facility_id, image_url, description) VALUES
(@ssfr_id, '/uploads/Event Halls/SSFR_PIC_1.jpg', NULL),
(@ssfr_id, '/uploads/Event Halls/SSFR_PIC_2.jpg', NULL),
(@ssfr_id, '/uploads/Event Halls/SSFR_PIC_3.jpg', NULL),
(@ssfr_id, '/uploads/Event Halls/SSFR_PFA.jpg', 'Pre-function Area'),
(@ssfr_id, '/uploads/Event Halls/SSFR_VIP.jpg', 'VIP Holding Room / Pre-function Room');

CREATE TABLE time_slots (
  id INT AUTO_INCREMENT PRIMARY KEY,
  label VARCHAR(20) NOT NULL,
  sort_order INT NOT NULL
);

INSERT INTO time_slots (id, label, sort_order) VALUES
(1, '08:00 - 09:00', 1),
(2, '09:00 - 10:00', 2),
(3, '10:00 - 11:00', 3),
(4, '11:00 - 12:00', 4),
(5, '14:00 - 15:00', 5),
(6, '15:00 - 16:00', 6),
(7, '16:00 - 17:00', 7),
(8, '17:00 - 18:00', 8),
(9, '18:00 - 19:00', 9),
(10, '19:00 - 20:00', 10),
(11, '20:00 - 21:00', 11);

CREATE TABLE bookings (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  court_id INT NOT NULL,
  booking_date DATE NOT NULL,
  full_day BOOLEAN NOT NULL DEFAULT FALSE,
  start_datetime DATETIME NOT NULL,
  end_datetime DATETIME NOT NULL,
  reason TEXT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (court_id) REFERENCES courts(id) ON DELETE CASCADE
);

CREATE TABLE closures (
  id INT AUTO_INCREMENT PRIMARY KEY,
  court_id INT NOT NULL,
  closure_date DATE NOT NULL,
  time_slot_id INT NULL,
  reason VARCHAR(150) NOT NULL,
  FOREIGN KEY (court_id) REFERENCES courts(id) ON DELETE CASCADE,
  FOREIGN KEY (time_slot_id) REFERENCES time_slots(id)
);

CREATE TABLE notifications (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  message VARCHAR(500) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  read_at TIMESTAMP NULL,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE contact_messages (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  email VARCHAR(150) NOT NULL,
  subject VARCHAR(150) NOT NULL,
  message TEXT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE sessions (
  id VARCHAR(128) PRIMARY KEY,
  data MEDIUMTEXT NOT NULL,
  last_activity INT NOT NULL,
  INDEX idx_last_activity (last_activity)
);

-- ============================================================================
-- TICKETING & EVENT SYSTEM TABLES
-- ============================================================================

CREATE TABLE events (
  id INT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(200) NOT NULL,
  description TEXT NOT NULL,
  facility_id INT NOT NULL,
  court_id INT NULL,
  start_datetime DATETIME NOT NULL,
  end_datetime DATETIME NOT NULL,
  image_url VARCHAR(500) NULL,
  organizer_id INT NOT NULL,
  status ENUM('draft', 'published', 'cancelled') NOT NULL DEFAULT 'published',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (facility_id) REFERENCES facilities(id) ON DELETE CASCADE,
  FOREIGN KEY (court_id) REFERENCES courts(id) ON DELETE SET NULL,
  FOREIGN KEY (organizer_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE ticket_types (
  id INT AUTO_INCREMENT PRIMARY KEY,
  event_id INT NOT NULL,
  name VARCHAR(100) NOT NULL,
  price DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  total_quantity INT NOT NULL DEFAULT 100,
  remaining_quantity INT NOT NULL DEFAULT 100,
  description TEXT NULL,
  FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE
);

CREATE TABLE orders (
  id INT AUTO_INCREMENT PRIMARY KEY,
  order_ref VARCHAR(30) NOT NULL UNIQUE,
  user_id INT NULL,
  buyer_name VARCHAR(100) NOT NULL,
  buyer_email VARCHAR(150) NOT NULL,
  total_amount DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  payment_status ENUM('paid', 'pending', 'refunded') NOT NULL DEFAULT 'paid',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

CREATE TABLE tickets (
  id INT AUTO_INCREMENT PRIMARY KEY,
  order_id INT NOT NULL,
  ticket_type_id INT NOT NULL,
  ticket_code VARCHAR(64) NOT NULL UNIQUE,
  attendee_name VARCHAR(100) NOT NULL,
  attendee_email VARCHAR(150) NOT NULL,
  is_checked_in BOOLEAN DEFAULT FALSE,
  checked_in_at DATETIME NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
  FOREIGN KEY (ticket_type_id) REFERENCES ticket_types(id) ON DELETE CASCADE
);

-- Seed Sample Events & Ticket Types
INSERT INTO events (title, description, facility_id, court_id, start_datetime, end_datetime, image_url, organizer_id, status) VALUES
('TAR UMT Annual Symphony Orchestra Concert 2026',
 'Join us for a magical evening of classical and contemporary orchestral performances hosted by TAR UMT Symphony Orchestra at Dewan Tunku Abdul Rahman.',
 1, 1, '2026-09-15 19:00:00', '2026-09-15 22:00:00', '/uploads/Event Halls/DTAR_HALL_A.jpg', 1, 'published'),
('TARCIAN Tech & AI Innovation Summit 2026',
 'A premier technology conference featuring keynote speakers, startup showcases, and AI workshops in the Red Bricks Theatre.',
 4, 6, '2026-10-05 09:00:00', '2026-10-05 17:00:00', '/uploads/Event Halls/RBTA_AUD.jpg', 1, 'published');

SET @evt1_id = 1;
INSERT INTO ticket_types (event_id, name, price, total_quantity, remaining_quantity, description) VALUES
(@evt1_id, 'VIP Orchestra Seat', 50.00, 100, 95, 'Front-row prime seating with complimentary refreshment pass.'),
(@evt1_id, 'General Admission', 25.00, 500, 480, 'Standard hall seating for public attendees.'),
(@evt1_id, 'TARCIAN Student Pass', 10.00, 300, 270, 'Discounted ticket tier for current TAR UMT students.');

SET @evt2_id = 2;
INSERT INTO ticket_types (event_id, name, price, total_quantity, remaining_quantity, description) VALUES
(@evt2_id, 'Delegate Pass', 35.00, 150, 140, 'Full-day access to all keynote sessions and networking lunch.'),
(@evt2_id, 'Student Pass', 15.00, 100, 90, 'Discounted access for students with valid ID.');