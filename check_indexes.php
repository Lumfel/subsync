<?php
$db = new PDO('sqlite:C:\laragon\www\subsync\database\database.sqlite');
$stmt = $db->query('SELECT name FROM sqlite_master WHERE type="index" AND name NOT LIKE "sqlite_%"');
while ($row = $stmt->fetch()) {
    echo $row['name'] . PHP_EOL;
}