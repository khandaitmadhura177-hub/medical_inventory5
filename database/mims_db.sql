CREATE DATABASE IF NOT EXISTS medicaldata;
USE medicaldata;

CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) PRIMARY KEY AUTO_INCREMENT,
  `username` varchar(50),
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` int(1) DEFAULT 0
);

-- Plain text password
INSERT INTO `users` (`username`, `email`, `password`, `role`) VALUES 
('admin', 'admin@test.com', 'admin123', 1);