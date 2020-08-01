<?php

require_once "../../login.php";
require_once "../../student.php";

use \ScA\Student\TGLogin\TGLogin;

$s = TGLogin::from_cookie();
if ($s != NULL) {
    $s = new \ScA\Student\Student(NULL, $s->id);
}
$is_logged_in = ($s != NULL);

if (!$is_logged_in || !$s->has_privileges("Admin")) {
    header("Location: ../../?nauth");
    exit;
}

if (!array_key_exists("telemetry", $_POST) || !isset($_POST["telemetry"])) {
    die("Invalid parameters.");
}

$v = intval($_POST["telemetry"]);

if ($v > 2 || $v < 0) {
    die("Out of range.");
}

$s->set_telemetry_privacy($v);

echo "Set: " . $v;