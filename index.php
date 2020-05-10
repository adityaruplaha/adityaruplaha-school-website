<?php

require_once "login.php";
require_once "teacher/defs.php";

use \ScA\Student\TGLogin\TGLogin;
use \ScA\Teacher;

$is_logged_in = (TGLogin::from_cookie() != NULL) || (Teacher\is_logged_in());

?>
<!DOCTYPE html>
<html lang='en'>

<head>
    <title>XII Sc A - Class Portal</title>
    <link rel='stylesheet' type='text/css' href='stylesheet.css' />
    </script>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>

<body>
    <h1>XII Sc A - Class Portal</h1>
    <hr />
    <div>
        <?php

        $logged_in_str = "
    <table>
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
        <td colspan=\"2\">
            <a href='teacher/'>Open Teachers' Portal</a>
        </td>
    </tr>
    <tr>
        <td colspan=\"2\">
            <a href='loginhandler.php?logout'>Logout</a>
        </td>
    </tr>
</table>";

        $status_message = "";

        if (isset($_GET["loggedout"])) {
            $status_message = "<p><i>Logged out.</i></p>";
        }

        if (isset($_GET["loginfailed"])) {
            $status_message = "<p class='red'><i>Failed to login.</i></p>";
        }

        if (isset($_GET["nauth"])) {
            $status_message = "<p class='red'><i>Please login first.</i></p>";
        }

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
