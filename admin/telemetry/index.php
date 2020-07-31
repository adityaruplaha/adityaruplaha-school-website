<?php

require_once "../../login.php";
require_once "../../teacher/defs.php";

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
            <td onclick="show(this, 'log')" class='tab_button'>Show telemetry logs.</td>
            <td onclick="show(this, 'lastlogin')" class='tab_button'>Last Logins</td>
        </tr>
    </table>

    <div class='tab' id='log'>
        <table class='semibordered center autowidth'>
            <tr>
                <th>Timestamp</th>
                <th>Member</th>
                <th>IP Address</th>
                <th>Action</th>
                <th>Data</th>
            </tr>
            <?php
            $result = $conn->query("SELECT * FROM telemetry ORDER BY `telemetry`.`Timestamp` DESC");
            while ($action = $result->fetch_assoc()) {
                echo "<tr>";

                echo "<td>" . $action["Timestamp"] . "</td>";
                echo "<td>" . $action["Member"] . "</td>";
                echo "<td class='center'>" . $action["IP"] . "</td>";
                echo "<td class='code'>" . $action["Action"] . "</td>";

                $r = $action["Data"];
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
    <div class='tab' id='lastlogin'>
        <table class='semibordered center autowidth'>
            <tr>
                <th>Member</th>
                <th>Last Login</th>
            </tr>
            <?php
            $result = $conn->query("SELECT * FROM last_logins");
            while ($action = $result->fetch_assoc()) {
                echo "<tr>";
                echo "<td>" . $action["Member"] . "</td>";
                echo "<td>" . $action["LastLogin"] . "</td>";
                echo "</tr>";
            }
            $result->free();
            ?>
        </table>
    </div>
</body>

</html>