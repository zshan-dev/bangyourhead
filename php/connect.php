/**
 * Database Connection Handler
 * 
 * @author Your Name (Your Student Number)
 * @date 2024-04-25
 * 
 * Establishes PDO connection to MySQL database
 * Creates a global $dbh (database handle) variable
 * 
 * Configuration:
 * - host: localhost
 * - database: bombaywz_db
 * - username: root
 * - password: (empty)
 * 
 * @throws PDOException if connection fails
 * @global PDO $dbh Database connection handle
 */

<?php
try {
    $dbh = new PDO(
        "mysql:host=localhost;dbname=bombaywz_db",
        "root",
        ""
    );
    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("ERROR: Couldn't connect. {$e->getMessage()}");
}
