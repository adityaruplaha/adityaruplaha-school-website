<?php

require_once '../defs.php';
require_once '../classes.php';

use ScA\Classes\SchedClass;

use const ScA\DB;
use const ScA\DB_HOST;
use const ScA\DB_PWD;
use const ScA\DB_USER;

$table = 'attendance';

$conn = new mysqli(DB_HOST, DB_USER, DB_PWD, DB);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$classes = SchedClass::get_classes_on($conn, time());

foreach ($classes as $class) {
    $class = $class->as_colname('`');
    $sql = "ALTER TABLE {$table} ADD {$class} BOOLEAN NULL";
    $r = $conn->query($sql);
    echo "Adding {$class}: {$sql};<br/>";
    if ($r) {
        echo "Added column {$class}.<br/>";
    } else {
        echo "Failed to add column {$class}.<br/>";
    }
    echo "<br/>";
}
