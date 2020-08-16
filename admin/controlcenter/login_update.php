<?php

require_once "../../login.php";
require_once "../../student.php";

use \ScA\Student\TGLogin\TGLogin;

$s = TGLogin::from_cookie();
if ($s != NULL) {
    $s = new \ScA\Student\Student(NULL, $s->id);
}
$is_logged_in = ($s != NULL);

if (!$is_logged_in || !$s->has_privileges("Super Admin")) {
    header("Location: ../../?nauth");
    exit;
}

if (isset($_POST["id"])) {
    $s = new \ScA\Student\Student(NULL, $_POST["id"]);
    if (!$s->is_valid) {
        die("Invalid ID");
    }
}

if (!array_key_exists("block_login", $_POST) || !isset($_POST["block_login"])) {
    die("Invalid parameters.");
}

$v = intval($_POST["block_login"]);

if ($v > 1 || $v < 0) {
    die("Out of range.");
}

$s->set_block_login($v);

echo "Set: " . $v;