-- Migration: Add username column to users table
-- Run this script to add username support

-- Add username column
ALTER TABLE users ADD COLUMN username VARCHAR(50) UNIQUE AFTER email;

-- Update existing users with usernames based on email
UPDATE users SET username = 'admin' WHERE email = 'admin@educhad.local';
UPDATE users SET username = 'enseignant' WHERE email = 'teacher@educhad.local';
UPDATE users SET username = 'caissier' WHERE email = 'cashier@educhad.local';
UPDATE users SET username = 'observateur' WHERE email = 'viewer@educhad.local';

-- Make username NOT NULL after populating
ALTER TABLE users MODIFY username VARCHAR(50) NOT NULL UNIQUE;

-- Show updated users
SELECT id, username, name, email, role FROM users;
