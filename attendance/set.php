<?php

require_once "../login.php";
require_once "../student.php";


use \ScA\Student\TGLogin\TGLogin;




$s = TGLogin::from_cookie();
if ($s != NULL) {
    $s = new \ScA\Student\Student(NULL, $s->id);
    if (!$s->has_privileges("Admin")) {
        $s = NULL;
    }
}

if ($s != NULL) {
    $s->report_url_visit($_SERVER['PHP_SELF']);
}

$is_logged_in = ($s != NULL);

if (!$is_logged_in) {
    header("Location: ../?nauth");
    exit;
}


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

$a = 0;
if ($_GET["add_days"]) {
    $a = $_GET["add_days"];
}

$classes = SchedClass::get_classes_on($conn, strtotime("today") + 86400 * $a);

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
