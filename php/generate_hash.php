<?php
$hash = password_hash('admin', PASSWORD_DEFAULT);
echo "Use this hash in your SQL command: " . $hash;
?> 