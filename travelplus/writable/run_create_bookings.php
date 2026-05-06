<?php
$mysqli = new mysqli('localhost', 'root', '', 'travelplus_db', 3306);
if ($mysqli->connect_errno) {
    fwrite(STDERR, 'CONNECT_ERROR: ' . $mysqli->connect_error . PHP_EOL);
    exit(1);
}
$sql = file_get_contents('E:/WORK/laragon/www/travelplus/database/sql/2026-05-06_create_bookings_table.sql');
if ($sql === false) {
    fwrite(STDERR, 'SQL_FILE_ERROR' . PHP_EOL);
    exit(1);
}
if (! $mysqli->multi_query($sql)) {
    fwrite(STDERR, 'QUERY_ERROR: ' . $mysqli->error . PHP_EOL);
    exit(1);
}
do {
    if ($result = $mysqli->store_result()) {
        $result->free();
    }
} while ($mysqli->more_results() && $mysqli->next_result());
echo 'OK';
$mysqli->close();
