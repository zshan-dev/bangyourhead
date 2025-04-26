/**
 * Password Hash Generator
 * 
 * @author Your Name (Your Student Number)
 * @date 2024-04-25
 * 
 * Development utility to generate secure password hashes
 * Currently configured to hash the word 'admin'
 * 
 * WARNING: This file should not be accessible in production
 * 
 * @uses PASSWORD_DEFAULT Current best algorithm (currently bcrypt)
 * @return string Hashed password for database storage
 */

<?php
$hash = password_hash('admin', PASSWORD_DEFAULT);
echo "Use this hash in your SQL command: " . $hash;
?> 