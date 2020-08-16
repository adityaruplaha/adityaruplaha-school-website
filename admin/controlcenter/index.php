<?php

require_once "../../login.php";
require_once "../../teacher/defs.php";

use \ScA\Student\TGLogin\TGLogin;
use \ScA\Teacher;

$is_teacher = Teacher\is_logged_in();

$s = TGLogin::from_cookie();
if ($s != NULL) {
    $s = new \ScA\Student\Student(NULL, $s->id);
    if (!$s->has_privileges("Super Admin")) {
        $s = NULL;
    }
}
$is_logged_in = ($s != NULL) || $is_teacher;

if (!$is_logged_in) {
    header("Location: ../../?nauth");
    exit;
}

require_once "../../defs.php";

use const ScA\DB;
use const ScA\DB_HOST;
use const ScA\DB_PWD;
use const ScA\DB_USER;

$conn = new mysqli(DB_HOST, DB_USER, DB_PWD, DB);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$limit = isset($_GET["LIMIT"]) ? intval($_GET["LIMIT"]) : 200

?>

<!DOCTYPE html>
<html lang='en'>

<head>
    <meta charset="utf-8">
    <title>XII Sc A - Control Center</title>
    <link rel='stylesheet' type='text/css' href='/sc_a/themes/dark/base.css' />
    <link rel='stylesheet' type='text/css' href='/sc_a/themes/dark/tables.css' />
    <link rel='stylesheet' type='text/css' href='/sc_a/themes/dark/slider.css' />
    <link rel='stylesheet' type='text/css' href='telemetry_slider.css' />
    <link rel='stylesheet' type='text/css' href='login_slider.css' />
    <link rel='stylesheet' type='text/css' href='resource_slider.css' />
    <meta name="viewport" content="width=device-width, initial-scale=0.75">
    <script src='/sc_a/scripts/post.js'>
    </script>
    <script src='script.js'>
    </script>
</head>

<body onload="autoload(0)">

    <h1 class='center'>XII Sc A - Control Center</h1>
    <hr />
    <p class='center'>
        <i>
            <?php
            date_default_timezone_set("Asia/Kolkata");
            echo "Report generated on " . date("d M Y h:i:sa") . " IST."
            ?>
        </i>
    </p>
    <hr />

    <div class='tab' id='stats'>
        <table class='semibordered center autowidth hoverable'>
            <tr>
                <th>Member</th>
                <th>Telemetry Mode</th>
                <th>Can login?</th>
                <th>Can access resources?</th>
            </tr>
            <?php
            $result = $conn->query(
                "SELECT Name, LAST_LOGIN(Name) 'LastLogin', LAST_ACTION(Name) 'LastAction', MEMBER_ACTION_RATIO(Name) 'MAR' FROM info"
            );
            while ($member = $result->fetch_assoc()) {
                echo "<tr>";
                echo "<td>" . $member["Name"] . "</td>";
                $stu = new \ScA\Student\Student($member["Name"], NULL);
                $telemetry_privacy = $stu->get_telemetry_privacy();
                $block_login = $stu->can_login ? 0 : 1;
                $block_resource_access = $stu->get_block_resource_access();
                echo "<td class='slider_container'>
                <input type='range' min='0' max='2' value='{$telemetry_privacy}' class='slider mode{$telemetry_privacy}'
                id='telemetry_slider' oninput='telemetry_updated(this, {$stu->tgid})'>
                </td>";
                if (!$stu->has_privileges("Admin")) {
                    echo "<td class='slider_container'>
                    <input type='range' min='0' max='1' value='{$block_login}' class='slider mode{$block_login}'
                    id='login_slider' oninput='login_updated(this, {$stu->tgid})'>
                    </td>";
                    echo "<td class='slider_container'>
                    <input type='range' min='0' max='1' value='{$block_resource_access}' class='slider mode{$block_resource_access}'
                    id='resource_slider' oninput='resource_updated(this, {$stu->tgid})'>
                    </td>";
                } else {
                    echo "<td></td>";
                    echo "<td></td>";
                }
                echo "</tr>";
            }
            $result->free();
            ?>
        </table>
    </div>
</body>

</html>