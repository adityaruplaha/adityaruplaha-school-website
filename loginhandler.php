<?php

require_once "student.php";
require_once "login.php";

use \ScA\Student\TGLogin\TGLogin;

if (isset($_GET["logout"])) {
    $s = TGLogin::from_cookie();
    (new \ScA\Student\Student(NULL, $s->id))->report_telemetry("LOGOUT");
    TGLogin::logout();
    header("Location: index.php?loggedout");
    exit;
}

if ($o = TGLogin::from_auth_data($_GET)) {
    $o->store();
    (new \ScA\Student\Student(NULL, $o->id))->report_telemetry("LOGIN");
    header("Location: index.php");
    exit;
} else {
    header("Location: index.php?loginfailed");
    exit;
}