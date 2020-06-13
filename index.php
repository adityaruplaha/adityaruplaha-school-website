<?php

require_once "login.php";
require_once "student.php";
require_once "teacher/defs.php";

use \ScA\Student\TGLogin\TGLogin;
use \ScA\Teacher;

$s = TGLogin::from_cookie();
$is_teacher = Teacher\is_logged_in();
$is_logged_in = ($s != NULL) || $is_teacher;

?>
<!DOCTYPE html>
<html lang='en'>

<head>
    <title>XII Sc A - Class Portal</title>
    <link rel='stylesheet' type='text/css' href='stylesheet.css' />
    <meta name="viewport" content="width=device-width, initial-scale=0.8">
</head>

<body>
    <h1>XII Sc A - Class Portal</h1>
    <hr />
    <?php

    if ($s) {
        $stu = new \ScA\Student\Student(NULL, $s->id);
        if ($stu->has_privileges("Member")) {

            $n = $stu->name;
            $greet = "Hello, {$n}.";

            echo "
            <table class='head'>
            <tr>
            <td style='text-align: left;'>
                <a href='member/'>Profile</a>
            </td>
            <td>{$greet} <a href='loginhandler.php?logout'>Logout</a></td>
            </tr>
            </table>
            <hr/>
            ";
        } else {
            echo "
            <div class='head'>
            Hello, {$stu->name}. <a href='loginhandler.php?logout'>Logout</a>
            </div>
            <hr/>
            ";
        }
    } elseif ($is_teacher) {
        echo "
        <table class='head'>
        <tr>
        <td style='text-align: left;'><a href='teacher/'>Open Teachers' Portal</a></td>
        <td>Hello, Teacher.</td>
        </tr>
        </table>
        <hr/>
        ";
    }

    ?>
    <div>
        <?php


        $status_message = "";

        if (isset($_GET["loggedout"])) {
            $status_message = "<p><i>Logged out.</i></p>";
        }

        if (isset($_GET["loginfailed"])) {
            $status_message = "<p class='red'><i>Failed to login.</i></p>";
        }

        if (isset($_GET["nauth"])) {
            $status_message = "<p class='red'><i>You are not authorized to access that page.</i></p>";
        }

        $logged_in_str = "
        {$status_message}
    <table class='nav'>
    <tr>
        <td><a href='name_list/'>Name List</a></td>
        <td><a href='contact/'>Contact Teachers</a></td>
    </tr>
    <tr>
        <td><a href='/go/?url=http://schoolatweb.byethost7.com/bdmi/online_index.php' class='insecure'>School
                Portal</a>
        </td>
        <td><a href='/go/?url=https://play.google.com/store/apps/details?id=com.bdmi.vawsum'>School App</a>
        </td>
    </tr>
    <tr>
        <td><a href='schedule/'>Class Schedule</a></td>
        <td><a href='attendance/'>Attendance</a></td>
    </tr>
    <tr>
        <td><a href='resources/'>Resources</a></td>
        <td><a href='assignments/'>Assignments</a></td>
    </tr>
    <tr>
        <td><a href='/go/?url=https://t.me/joinchat/AAAAAEhiLVecUgh9hZynzw'>Telegram Channel</a></td>
        <td><a href='/go/?url=https://trello.com/b/xS4L8vFx/'>Trello Board</a></td>
    </tr>
    <tr>
        <td colspan=\"2\"><br /></td>
    </tr>
    <tr>
        <td colspan=\"2\"><a href='/go/?url=https://github.com/adityaruplaha/adityaruplaha-school-website'>Browse Source Code</a></td>
    </tr>
</table>";

        $not_logged_in_str = "
        {$status_message}
        <p>Telegram user data is never stored on the server.<br/>Your data is secure.</p><fieldset>You should see a button to login with Telegram.<br/><br/>If you don't, try using 1.1.1.1 or any other VPN from Play Store.<br/>
        <a href='https://play.google.com/store/apps/details?id=com.cloudflare.onedotonedotonedotone'>Get 1.1.1.1 on Google Play</a></fieldset>
        <p align=center id='tglogin'><script async src=\"https://telegram.org/js/telegram-widget.js?2\" data-telegram-login='" . BOT_USERNAME . "' data-size='large' data-auth-url='loginhandler.php'></script></p>
        ";

        if ($is_logged_in) {
            echo $logged_in_str;
        } else {
            echo $not_logged_in_str;
        }

        ?>
    </div>
</body>

</html>