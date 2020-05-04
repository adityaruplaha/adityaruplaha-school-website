<?php

require '../defs.php';

$db_host = 'localhost';
$db_user = 'prog_access';
$db_pwd = '';

$database = 'school';
$table = 'xii_sc_a_attendance';

$conn = new mysqli($db_host, $db_user, $db_pwd, $database);

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