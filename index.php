<?php

require_once "login.php";
require_once "student.php";


use \ScA\Student\TGLogin\TGLogin;


$s = TGLogin::from_cookie();

$is_logged_in = ($s != NULL);

if ($s != NULL) {
    $s = (new \ScA\Student\Student(NULL, $s->id));
    $s->report_url_visit($_SERVER['PHP_SELF']);
}

?>
<!DOCTYPE html>
<html lang='en'>

<head>
    <title>XII Sc A - Class Portal</title>
    <link rel='stylesheet' type='text/css' href='/sc_a/themes/dark/base.css' />
    <link rel='stylesheet' type='text/css' href='/sc_a/themes/dark/tables.css' />
    <link rel='stylesheet' type='text/css' href='stylesheet.css' />
    <meta name="viewport" content="width=device-width, initial-scale=0.8">
    <script src="https://code.iconify.design/1/1.0.7/iconify.min.js"></script>
</head>

<body>
    <h1>XII Sc A - Class Portal</h1>
    <hr />
    <?php

    if ($s) {
        echo "
        <table class='invbicolumn fullwidth bigfont'>
        <tr>
        <td>
            <a href='member/'>Profile</a>
        </td>
        <td>";
        if ($s->get_telemetry_privacy() >= 2) {
            echo '<span class="iconify hugefont" data-icon="mdi:incognito-circle" data-inline="false"></span>';
        }
        echo "
        {$s->name} <a href='loginhandler.php?logout'>Logout</a></td>
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
    <table class='unbordered centercells reducedwidth bigfont'>
    <tr>
        <td><a href='/go/?url=https://chat.whatsapp.com/I1kdbGC1xfA5ZaPNbSgtCS'>Offical Group</a></td>
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
        <td><a href='comments/'>Comments</a></td>
    </tr>
    <tr>
        <td colspan=\"2\"><br /></td>
    </tr>
    <tr>
        <td><a href='/go/?url=https://github.com/adityaruplaha/adityaruplaha-school-website'>Browse Source Code</a></td>
        <td><a href='policies/'>Policies</a></td>
    </tr>
</table>";

        $telegram_script = '<script async src="https://telegram.org/js/telegram-widget.js?12" data-telegram-login="' . BOT_USERNAME . '" data-size="large" data-auth-url="https://adityaruplaha.ddns.net/sc_a/loginhandler.php" data-request-access="write"></script>';

        $not_logged_in_str = "
        {$status_message}
        <p>Telegram user data is never stored on the server.<br/>Your data is secure.</p><fieldset>You should see a button to login with Telegram.<br/><br/>If you don't, try using 1.1.1.1 or any other VPN from Play Store.<br/>
        <a href='https://play.google.com/store/apps/details?id=com.cloudflare.onedotonedotonedotone'>Get 1.1.1.1 on Google Play</a></fieldset>
        <p align=center id='tglogin'>{$telegram_script}</p>
        ";

        if ($is_logged_in) {
            echo $logged_in_str;
        } else {
            echo $not_logged_in_str;
        }

        ?>
    </div>
    <script id='remove_get'>
    window.history.replaceState(null, '', window.location.href.split('?')[0]);
    document.getElementById('remove_get').remove();
    </script>
</body>

</html>