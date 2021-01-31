<?php

require_once "../login.php";

use \ScA\Student\TGLogin\TGLogin;

$s = TGLogin::from_cookie();

$is_logged_in = ($s != NULL);

Deprecate\disable_page();

if ($s != NULL) {
    $s = (new \ScA\Student\Student(NULL, $s->id));
    $s->report_url_visit($_SERVER['PHP_SELF']);
}

if (!$is_logged_in) {
    header("Location: ../?nauth");
    exit;
}

require_once '../classes.php';

if (!(isset($_GET['timestamp']) && isset($_GET['subject']))) {
    die("Invalid fields supplied.");
}

$class = new \ScA\Classes\SchedClass(intval($_GET['timestamp']), $_GET['subject']);

echo json_encode($class->get_card());
