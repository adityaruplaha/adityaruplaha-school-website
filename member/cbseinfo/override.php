<?php

require_once "../../login.php";
require_once "../../student.php";

use \ScA\Student\TGLogin\TGLogin;

$s = TGLogin::from_cookie();
if ($s != NULL) {
    $s = new \ScA\Student\Student(NULL, $s->id);
}
$is_logged_in = ($s != NULL);

Deprecate\disable_page();

if (!$is_logged_in) {
    header("Location: ../../?nauth");
    exit;
}

$v = (int) isset($_GET["engage"]);
$s->set_manual_confirm_contact_cbse($v);

if ($v) {
    echo "Override engaged.";
} else {
    echo "Override disengaged.";
}