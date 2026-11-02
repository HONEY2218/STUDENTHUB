-- Practical 12: Event Management CRUD with Poster Upload and File Validation
CREATE DATABASE IF NOT EXISTS event_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE event_db;

DROP TABLE IF EXISTS events;
DROP TABLE IF EXISTS admins;

CREATE TABLE admins (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    username      VARCHAR(50)  NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL
);

CREATE TABLE events (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    title      VARCHAR(150) NOT NULL,
    event_date DATE         NOT NULL,
    location   VARCHAR(150) NOT NULL,
    category   VARCHAR(50)  NOT NULL,
    status     ENUM('Open','Closed') NOT NULL DEFAULT 'Open',
    poster     VARCHAR(255) NULL,          -- only the FILE NAME is stored; NULL = no poster
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Default admin account: username admin / password admin123  (stored as a bcrypt hash)
INSERT INTO admins (username, password_hash) VALUES ('admin', '$2y$10$neDfjdlh.ZYxOKso1FNwaOyoFi.vW.n.jRLyr4tzdW6.kDD3Stxhu');

-- Sample events (posters are optional, add them through the form)
INSERT INTO events (title, event_date, location, category, status) VALUES
('Tech Summit 2026',        '2026-11-15', 'Convention Center', 'Conference', 'Open'),
('Web Dev Bootcamp',        '2026-11-20', 'CSE Lab 3',         'Workshop',   'Open'),
('Inter-College Cricket',   '2026-12-02', 'University Ground', 'Sports',     'Open'),
('Unplugged Music Night',   '2026-12-10', 'Open Air Theatre',  'Music',      'Open'),
('AI in Healthcare Talk',   '2026-10-25', 'Seminar Hall A',    'Conference', 'Closed'),
('PHP Hands-on Workshop',   '2026-11-05', 'CSE Lab 1',         'Workshop',   'Open'),
('Annual Sports Meet',      '2026-12-18', 'Main Stadium',      'Sports',     'Open'),
('Battle of Bands',         '2026-10-30', 'Auditorium',        'Music',      'Closed');
