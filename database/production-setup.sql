-- Production Database Setup Script for Fidelity Voting System
-- Run this script on your production MySQL server

-- Create the production database
CREATE DATABASE IF NOT EXISTS fidelity_voting_production
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

-- Create a dedicated database user for the application
-- Replace 'your_secure_password' with a strong password
CREATE USER IF NOT EXISTS 'fidelity_app'@'localhost' IDENTIFIED BY 'your_secure_password';
CREATE USER IF NOT EXISTS 'fidelity_app'@'%' IDENTIFIED BY 'your_secure_password';

-- Grant necessary privileges
GRANT SELECT, INSERT, UPDATE, DELETE, CREATE, ALTER, INDEX, DROP, REFERENCES
ON fidelity_voting_production.* TO 'fidelity_app'@'localhost';
GRANT SELECT, INSERT, UPDATE, DELETE, CREATE, ALTER, INDEX, DROP, REFERENCES
ON fidelity_voting_production.* TO 'fidelity_app'@'%';

-- Flush privileges to apply changes
FLUSH PRIVILEGES;

-- Use the production database
USE fidelity_voting_production;

-- Display success message
SELECT 'Production database setup completed successfully!' AS status;

-- Instructions for deployment:
-- 1. Run this script on your production MySQL server
-- 2. Update your .env.production file with the correct database credentials
-- 3. Run: php artisan migrate --env=production
-- 4. Run: php artisan db:seed --env=production (to create admin user)
-- 5. Run: php artisan config:cache --env=production
-- 6. Run: php artisan route:cache --env=production
-- 7. Run: php artisan view:cache --env=production