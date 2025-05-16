CREATE DATABASE IF NOT EXISTS gradeviewer;
USE gradeviewer;

CREATE TABLE Teachers (
    ID INT PRIMARY KEY AUTO_INCREMENT,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    avatar VARCHAR(255)
);

CREATE TABLE Students (
    ID INT PRIMARY KEY AUTO_INCREMENT,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    avatar VARCHAR(255)
);

CREATE TABLE Subjects (
    ID INT PRIMARY KEY AUTO_INCREMENT,
    subject_name VARCHAR(100) UNIQUE NOT NULL
);

CREATE TABLE Grades (
    ID INT PRIMARY KEY AUTO_INCREMENT,
    student_id INT NOT NULL,
    subject_id INT NOT NULL,
    grade INT NOT NULL,
    date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES Students(ID) ON DELETE CASCADE,
    FOREIGN KEY (subject_id) REFERENCES Subjects(ID) ON DELETE CASCADE
);

INSERT INTO Teachers (first_name, last_name, username, password) 
VALUES ('John', 'Doe', 'john.doe', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'); -- password: password