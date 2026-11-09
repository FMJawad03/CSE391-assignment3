CREATE TABLE IF NOT EXISTS mechanics (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL
);

CREATE TABLE IF NOT EXISTS admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL
);

CREATE TABLE IF NOT EXISTS appointments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    client_name VARCHAR(100) NOT NULL,
    address VARCHAR(255) NOT NULL,
    phone VARCHAR(25) NOT NULL,
    car_registration VARCHAR(50) NOT NULL,
    engine_number VARCHAR(60) NOT NULL,
    appointment_date DATE NOT NULL,
    mechanic_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_appointment_mechanic
        FOREIGN KEY (mechanic_id) REFERENCES mechanics(id),
    CONSTRAINT unique_car_per_day
        UNIQUE (car_registration, appointment_date),
    CONSTRAINT unique_engine_per_day
        UNIQUE (engine_number, appointment_date)
);

INSERT INTO mechanics (name)
SELECT 'Alex Ross' WHERE NOT EXISTS (SELECT 1 FROM mechanics);
INSERT INTO mechanics (name)
SELECT 'John Surtees' WHERE (SELECT COUNT(*) FROM mechanics) = 1;
INSERT INTO mechanics (name)
SELECT 'Jack Stewart' WHERE (SELECT COUNT(*) FROM mechanics) = 2;
INSERT INTO mechanics (name)
SELECT 'James Hunt' WHERE (SELECT COUNT(*) FROM mechanics) = 3;
INSERT INTO mechanics (name)
SELECT 'Jenson Hill' WHERE (SELECT COUNT(*) FROM mechanics) = 4;