<?php


require_once "../login.php";
require_once "../student.php";
require_once "../teacher/defs.php";

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
    header("Location: ../?nauth");
    exit;
}

?>
<html>

<head>
    <title>
        XII Sc A - Administrators Portal
    </title>
    <link rel='stylesheet' type='text/css' href='stylesheet.css' />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    </script>
</head>

<body>
    <h1>XII Sc A - Administrators Portal</h1>
    <hr />
    <div>
        <table>
            <tr>
                <td><a href='actions/'>Action Log</a></td>
                <td><a href='details/'>Student Details</a></td>
            </tr>
            <?php

            if ($s->has_privileges("Super Admin") || $is_teacher) {
                echo "
                <tr>
                    <td><a href='broadcast/'>Broadcast to Telegram</a></td>
                </tr>
                ";
            }

            ?>
            <tr>
                <td colspan="2"><br /></td>
            </tr>
            <tr>
                <td colspan="2">
                    <a href='../'>Open Students' Portal</a>
                </td>
            </tr>
        </table>
    </div>
</body>