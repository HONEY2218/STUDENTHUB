
CREATE DATABASE IF NOT EXISTS dashboard_db;
USE dashboard_db;

CREATE TABLE IF NOT EXISTS events (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(100) NOT NULL,
    category VARCHAR(50) NOT NULL,
    event_date DATE NOT NULL,
    venue VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO events (title, category, event_date, venue)
VALUES
('Tech Fest', 'Technology', '2026-11-15', 'CHARUSAT Campus'),
('Workshop', 'Education', '2026-11-20', 'CSPIT'),
('Cultural Night', 'Cultural', '2026-12-01', 'University Auditorium');
