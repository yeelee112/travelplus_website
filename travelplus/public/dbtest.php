<?php
$db = \Config\Database::connect();

var_dump($db->getUsername());
var_dump($db->getDatabase());