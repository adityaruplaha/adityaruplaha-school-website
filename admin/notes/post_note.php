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

const POST_KEYS = [
    "Content", "MinPrivilegeLevel"
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

try {
    $_ = new \ScA\Student\Privilege($_POST['MinPrivilegeLevel']);
} catch (Exception $e) {
    report_error("Invalid permission model.");
    exit;
}

// Check connection
if ($conn->connect_error) {
    report_error("Connection failed: " . $conn->connect_error);
}

$_POST["Content"] = $conn->real_escape_string($_POST["Content"]);

$result = $conn->query("INSERT INTO `notes` VALUES (
    123456789, CURRENT_TIMESTAMP(), CURRENT_TIMESTAMP(), '{$s->name}', '{$_POST['Content']}', '{$_POST['MinPrivilegeLevel']}'
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