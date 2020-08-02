<?php
require_once "../../login.php";
require_once "../../teacher/defs.php";

use \ScA\Student\TGLogin\TGLogin;
use \ScA\Teacher;

$is_teacher = Teacher\is_logged_in();

$s = TGLogin::from_cookie();
if ($s != NULL) {
    $s = new \ScA\Student\Student(NULL, $s->id);
    if (!$s->has_privileges("Admin")) {
        $s = NULL;
    }
}
$is_logged_in = ($s != NULL) || $is_teacher;

if (!$is_logged_in) {
    header("Location: ../../?nauth");
    exit;
}
?>

<?php

const RLOOKUP = [
    "English" => 'en',
    "Mathematics" => 'math',
    "Computer Science" => 'cs',
    "Physics" => 'phy',
    "Chemistry" => 'chem',
    "Physical Education" => 'pe',
    "Bengali" => 'bn',
    "Hindi" => 'hi'
];

const POST_KEYS = [
    "Name", "AssignedOn", "DueOn", "Subject", "URL", "Notes"
];

function report_error($error)
{
    header("Location: index.php?done=0&error=" . print_r($error, true));
}

require_once "../../defs.php";

use const ScA\DB;
use const ScA\DB_HOST;
use const ScA\DB_PWD;
use const ScA\DB_USER;

$conn = new mysqli(DB_HOST, DB_USER, DB_PWD, DB);;

if (array_keys($_POST) != POST_KEYS) {
    report_error("Invalid fields supplied.");
    exit;
}

if (!isset(RLOOKUP[$_POST["Subject"]])) {
    report_error("Invalid subject");
    exit;
} else {
    $_POST["Subject"] = RLOOKUP[$_POST["Subject"]];
}

if (!$_POST["DueOn"]) {
    $_POST["DueOn"] = "NULL";
} else {
    $_POST["DueOn"] = "'{$_POST["DueOn"]}'";
}

if (!$_POST["URL"]) {
    $_POST["URL"] = $conn->real_escape_string($_POST["URL"]);
    $_POST["URL"] = "NULL";
} else {
    $_POST["URL"] = "'{$_POST["URL"]}'";
}

var_dump($_POST);

// Check connection
if ($conn->connect_error) {
    report_error("Connection failed: " . $conn->connect_error);
}

$_POST["Name"] = $conn->real_escape_string($_POST["Name"]);
$_POST["Notes"] = $conn->real_escape_string($_POST["Notes"]);

$result = $conn->query("INSERT INTO `assignments` VALUES (
    '{$_POST['Name']}', '{$_POST['AssignedOn']}', {$_POST['DueOn']}, '{$_POST['Subject']}', {$_POST['URL']}, '{$_POST['Notes']}'
    );");
if ($result) {
    header("Location: index.php?done=1");
    exit;
} else {
    header("Location: index.php?done=0&error=" . print_r($result, true));
    exit;
}

header("Location: index.php");
exit;