<?php

require_once "../../login.php";


use \ScA\Student\TGLogin\TGLogin;




$s = TGLogin::from_cookie();
if ($s != NULL) {
    $s = new \ScA\Student\Student(NULL, $s->id);
    if (!$s->has_privileges("Super Admin")) {
        $s = NULL;
    }
}
$is_logged_in = ($s != NULL);

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
    <script src='/sc_a/scripts/renderjson.js'>
    </script>
    <link rel='stylesheet' type='text/css' href='/sc_a/themes/dark/base.css' />
    <link rel='stylesheet' type='text/css' href='/sc_a/themes/dark/tables.css' />
    <link rel='stylesheet' type='text/css' href='/sc_a/themes/dark/tabs.css' />
    <link rel='stylesheet' type='text/css' href='/sc_a/themes/dark/cards.css' />
    <link rel='stylesheet' type='text/css' href='/sc_a/themes/dark/modals.css' />
    <link rel='stylesheet' type='text/css' href='stylesheet.css' />
    <meta name="viewport" content="width=device-width, initial-scale=0.75">
    <script src='script.js'>
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
    <table class='nav mediumfont'>
        <tr>
            <td onclick="show(this, 'log')" class='tab_button'>
                Logs
                <?php
                if ($limit) {
                    echo " (last {$limit})";
                }
                ?>
            </td>
            <td onclick="show(this, 'stats')" class='tab_button'>Statistics</td>
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
            $lc = $limit ? "LIMIT {$limit}" : "";
            $result = $conn->query("SELECT * FROM telemetry ORDER BY `telemetry`.`Timestamp` DESC {$lc}");
            while ($member = $result->fetch_assoc()) {
                echo "<tr class='hoverable'>";

                echo "<td>" . $member["Timestamp"] . "</td>";
                echo "<td>" . $member["Member"] . "</td>";
                echo "<td class='center'>" . $member["IP"] . "</td>";
                echo "<td class='code'>" . $member["Action"] . "</td>";

                $r = $member["Data"];
                if ($r && $r != '{}' && $r != 'null') {
                    //$r = str_replace("\n", "\n<br/>", htmlspecialchars($r));
                    echo "<td class='code' onclick='render_json({$r});'>{$r}</td>";
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
        <table class='semibordered center autowidth'>
            <tr>
                <th>Member</th>
                <th>Last Login</th>
                <th>Last Action</th>
                <th>MAR</th>
            </tr>
            <?php
            $result = $conn->query(
                "SELECT Name, LAST_LOGIN(Name) 'LastLogin', LAST_ACTION(Name) 'LastAction', MEMBER_ACTION_RATIO(Name) 'MAR' FROM info ORDER BY Name"
            );
            while ($member = $result->fetch_assoc()) {
                echo "<tr class='hoverable'>";
                echo "<td>" . $member["Name"] . "</td>";
                echo "<td>" . $member["LastLogin"] . "</td>";
                echo "<td>" . $member["LastAction"] . "</td>";
                echo "<td>" . floatval($member["MAR"]) * 100 . "%</td>";
                echo "</tr>";
            }
            $result->free();
            ?>
        </table>
    </div>
</body>

</html>
