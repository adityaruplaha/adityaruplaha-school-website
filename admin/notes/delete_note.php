<?php
require_once "../../login.php";

use \ScA\Student\TGLogin\TGLogin;

$s = TGLogin::from_cookie();
if ($s != NULL) {
    $s = new \ScA\Student\Student(NULL, $s->id);
    if (!$s->has_privileges("Admin")) {
        $s = NULL;
    }
}
$is_logged_in = ($s != NULL);

if (!$is_logged_in) {
    header("Location: ../../?nauth");
    exit;
}
?>

<?php

const POST_KEYS = [
    "ID"
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

if (sort(array_keys($_POST)) != POST_KEYS) {
    report_error("Invalid fields supplied.");
    exit;
}

// Check connection
if ($conn->connect_error) {
    report_error("Connection failed: " . $conn->connect_error);
    exit;
}

$_POST["Content"] = $conn->real_escape_string($_POST["Content"]);

$r0 = $conn->query("SELECT PostedBy FROM `notes` WHERE NoteID = {$_POST['ID']};");
if ($s->name != $r0->fetch_row()[0]) {
    report_error("Permission denied.");
    exit;
}

$result = $conn->query("DELETE FROM `notes` WHERE NoteID = {$_POST['ID']};");
if ($result) {
    header("Location: index.php?done=1");
    exit;
} else {
    header("Location: index.php?done=0&error=" . print_r($result, true));
    exit;
}

header("Location: index.php");
exit;