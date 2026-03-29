-- ============================================
-- Quiz Academy — Security Migration
-- Run this in phpMyAdmin or MySQL CLI
-- ============================================

-- 1. Add userId column to scores table (links scores to users)
ALTER TABLE scores 
ADD COLUMN userId INT NOT NULL AFTER subjectId,
ADD FOREIGN KEY (userId) REFERENCES users(userId);

-- 2. Increase userPassword column size to fit bcrypt hashes (60 chars)
--    Existing plain text passwords will auto-upgrade on next login.
ALTER TABLE users 
MODIFY COLUMN userPassword VARCHAR(255) NOT NULL;
