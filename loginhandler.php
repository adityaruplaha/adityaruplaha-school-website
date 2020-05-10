<?php

require_once "login.php";

use \ScA\Student\TGLogin\TGLogin;

if (isset($_GET["logout"])) {
    TGLogin::logout();
    header("Location: index.php?loggedout");
    exit;
}

if ($o = TGLogin::from_auth_data($_GET)) {
    $o->store();
    header("Location: index.php");
    exit;
} else {
    header("Location: index.php?loginfailed");
    exit;
}
