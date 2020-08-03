<?php

require_once "../../login.php";

use \ScA\Student\TGLogin\TGLogin;

$s = TGLogin::from_cookie();
if ($s != NULL) {
    $s = new \ScA\Student\Student(NULL, $s->id);
}

if ($s != NULL) {
    $s->report_url_visit($_SERVER['PHP_SELF']);
}

$is_logged_in = ($s != NULL);

if (!$is_logged_in) {
    header("Location: ../?nauth");
    exit;
}

var_dump($_POST);

const POST_KEYS = [
    "EMail", "Mobile", "Mobile2"
];

function report_error($error)
{
    header("Location: index.php?done=0&error=" . print_r($error, true));
}


if (array_keys($_POST) != POST_KEYS) {
    report_error("Invalid fields supplied.");
    exit;
}

if (!filter_var($_POST["EMail"], FILTER_VALIDATE_EMAIL)) {
    report_error("Invalid e-mail address supplied.");
    exit;
}

if (!preg_match("/\+91[0-9]{10}/i", $_POST["Mobile"])) {
    report_error("Invalid primary mobile number supplied.");
    exit;
}

if ($_POST["Mobile2"] && !preg_match("/\+91[0-9]{10}/i", $_POST["Mobile2"])) {
    report_error("Invalid alternate mobile number supplied.");
    exit;
}

if ($s->set_contact_info($_POST)) {
    header("Location: index.php?done=1");
    exit;
} else {
    header("Location: index.php?done=0&error=An unknown error occured.");
    exit;
}