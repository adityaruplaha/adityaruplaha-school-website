<?php


require_once "../../login.php";
require_once "../../student.php";

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
    header("Location: ../../?nauth");
    exit;
}

$telemetry_privacy = $s->get_telemetry_privacy();
?>

<!DOCTYPE html>
<html lang='en'>

<head>
    <title>
        XII Sc A - Edit Profile
    </title>
    <link rel='stylesheet' type='text/css' href='/sc_a/themes/dark/base.css' />
    <link rel='stylesheet' type='text/css' href='/sc_a/themes/dark/tables.css' />
    <link rel='stylesheet' type='text/css' href='/sc_a/themes/dark/slider.css' />
    <link rel='stylesheet' type='text/css' href='telemetry_slider.css' />
    <meta name="viewport" content="width=device-width, initial-scale=0.75">
    <script src='/sc_a/scripts/post.js'>
    </script>
    <script src='telemetry_slider.js'>
    </script>
</head>

<body>
    <h1>XII Sc A - Edit Profile</h1>
    <hr />
    <div>
        <table class='center bigfont bicolumn unbordered'>
            <?php

            if ($s->has_privileges("Admin")) {
                echo "
            <tr>
                <td>Telemetry Mode</td>
                <td class='slider_container'>
                    <input type='range' min='0' max='2' value='{$telemetry_privacy}' class='slider mode{$telemetry_privacy}'
            id='telemetry_slider' oninput='telemetry_updated(this)'></td>
            </tr>";
            }

            ?>
        </table>
    </div>
</body>

</html>