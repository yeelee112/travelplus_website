<?php
$mysqli = new mysqli('localhost', 'root', '', 'travelplus_db', 3306);
$result = $mysqli->query("SHOW TABLES LIKE 'bookings'");
var_dump($result ? $result->num_rows : 0);
if ($result) { $result->free(); }
$result2 = $mysqli->query("SHOW COLUMNS FROM bookings");
while ($row = $result2->fetch_assoc()) { echo $row['Field'] . '|' . $row['Type'] . PHP_EOL; }
$result2->free();
$mysqli->close();
