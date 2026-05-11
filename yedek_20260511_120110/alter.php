<?php
require_once 'db.php';
$conn->query("ALTER TABLE users ADD COLUMN sms_code VARCHAR(50) NULL");
echo "OK";
?>