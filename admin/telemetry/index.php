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
    <title>XII Sc A - Telemetry</title>
    <script src='/sc_a/scripts/tab.js'>
    </script>
    <link rel='stylesheet' type='text/css' href='/sc_a/themes/dark/base.css' />
    <link rel='stylesheet' type='text/css' href='/sc_a/themes/dark/tables.css' />
    <link rel='stylesheet' type='text/css' href='/sc_a/themes/dark/tabs.css' />
    <link rel='stylesheet' type='text/css' href='/sc_a/themes/dark/slider.css' />
    <link rel='stylesheet' type='text/css' href='telemetry_slider.css' />
    <meta name="viewport" content="width=device-width, initial-scale=0.75">
    <script src='/sc_a/scripts/post.js'>
    </script>
    <script src='telemetry_slider.js'>
    </script>
</head>

<body onload="autoload(0)">

    <h1 class='center'>XII Sc A - Telemetry</h1>
    <hr />
    <p class='center'>
        <i>
            <?php
            date_default_timezone_set("Asia/Kolkata");
            echo "Report generated on " . date("d M Y h:i:sa") . " IST."
            ?>
        </i>
        <br />
        This page is not covered by telemetry.
    </p>
    <table class='nav smallfont'>
        <tr>
            <td onclick="show(this, 'log')" class='tab_button'>
                Logs
                <?php
                if ($limit) {
                    echo " (last {$limit})";
                }
                ?>
            </td>
            <td onclick="show(this, 'stats')" class='tab_button'>Statistics & Controls</td>
        </tr>
    </table>

    <div class='tab' id='log'>
        <table class='semibordered center autowidth hoverable'>
            <tr>
                <th>Timestamp</th>
                <th>Member</th>
                <th>IP Address</th>
                <th>Action</th>
                <th>Data</th>
            </tr>
            <?php
            $lc = $limit ? "LIMIT {$limit}" : "";
            $result = $conn->query("SELECT * FROM telemetry ORDER BY `telemetry`.`Timestamp` DESC {$lc}");
            while ($member = $result->fetch_assoc()) {
                echo "<tr>";

                echo "<td>" . $member["Timestamp"] . "</td>";
                echo "<td>" . $member["Member"] . "</td>";
                echo "<td class='center'>" . $member["IP"] . "</td>";
                echo "<td class='code'>" . $member["Action"] . "</td>";

                $r = $member["Data"];
                if ($r && $r != '{}' && $r != 'null') {
                    $r = str_replace("\n", "\n<br/>", htmlspecialchars($r));
                    echo "<td class='code'>{$r}</td>";
                } else {
                    echo "<td></td>";
                }

                echo "</tr>";
            }
            $result->free();
            ?>
        </table>
    </div>
    <div class='tab' id='stats'>
        <table class='semibordered center autowidth hoverable'>
            <tr>
                <th>Member</th>
                <th>Last Login</th>
                <th>Last Action</th>
                <th>MAR</th>
                <th>Telemetry Mode</th>
            </tr>
            <?php
            $result = $conn->query(
                "SELECT Name, LAST_LOGIN(Name) 'LastLogin', LAST_ACTION(Name) 'LastAction', MEMBER_ACTION_RATIO(Name) 'MAR' FROM info"
            );
            while ($member = $result->fetch_assoc()) {
                echo "<tr>";
                echo "<td>" . $member["Name"] . "</td>";
                echo "<td>" . $member["LastLogin"] . "</td>";
                echo "<td>" . $member["LastAction"] . "</td>";
                echo "<td>" . floatval($member["MAR"]) * 100 . "%</td>";
                $stu = new \ScA\Student\Student($member["Name"], NULL);
                $telemetry_privacy = $stu->get_telemetry_privacy();
                echo "<td class='slider_container'>
                <input type='range' min='0' max='2' value='{$telemetry_privacy}' class='slider mode{$telemetry_privacy}'
        id='telemetry_slider' oninput='telemetry_updated(this, {$stu->tgid})'>
        </td>";
                echo "</tr>";
            }
            $result->free();
            ?>
        </table>
    </div>
</body>

</html>