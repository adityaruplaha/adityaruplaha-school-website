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

if ($s != NULL) {
    $s->report_url_visit($_SERVER['PHP_SELF']);
}

$is_logged_in = ($s != NULL) || $is_teacher;

if (!$is_logged_in) {
    header("Location: ../../?nauth");
    exit;
}

?>
<!DOCTYPE html>
<html lang='en'>

<head>
    <title>
        XII Sc A - CBSE Info
    </title>
    <link rel='stylesheet' type='text/css' href='/sc_a/themes/dark/base.css' />
    <link rel='stylesheet' type='text/css' href='/sc_a/themes/dark/tables.css' />
</head>

<body>
    <h1>XII Sc A - CBSE Info</h1>
    <hr />
    <div align='center'>
        <i>
            <?php
            date_default_timezone_set("Asia/Kolkata");
            echo "Report generated on " . date("d M Y h:i:sa") . " IST."
            ?>
        </i>
        <br />
    </div>
    <div>
        <table class='autowidth semibordered center'>
            <tr>
                <th>Name</th>
                <th>Gender</th>
                <th>Single girl child?</th>
                <th>Religion</th>
                <th>Caste</th>
                <th>Games played</th>
                <th colspan="2">Email</th>
                <th colspan="2">Mobile No.</th>
            </tr>

            <?php

            require_once "../../defs.php";

            use const ScA\DB;
            use const ScA\DB_HOST;
            use const ScA\DB_PWD;
            use const ScA\DB_USER;

            $conn = new mysqli(DB_HOST, DB_USER, DB_PWD, DB);;

            // Check connection
            if ($conn->connect_error) {
                die("Connection failed: " . $conn->connect_error);
            }

            $sql = "SELECT Name FROM info";
            $result = $conn->query($sql);
            if (!$result) {
                die("Failed to get names.");
            }

            function ver_string(bool $ver = NULL)
            {
                if ($ver === NULL) {
                    // Manual verification.
                    return "<td class='yellow'><b>O</b></td>";
                }
                if ($ver) {
                    return "<td class='green'><b>&#x2611;</b></td>";
                } else {
                    return "<td class='red'><b>?</b></td>";
                }
            }

            while ($row = $result->fetch_assoc()) {
                $s = new \ScA\Student\Student($row['Name']);
                $contact = $s->get_contact_cbse();
                $info = $s->get_basic_info();
                $games = $s->get_games();
                echo "<tr>";
                echo "<td>{$contact['Name']}</td>";
                echo "<td>{$info['Gender']}</td>";
                echo "<td class='center'>{$info['SingleGirlChild']}</td>";
                echo "<td>{$info['Religion']}</td>";
                echo "<td>{$info['Caste']}</td>";
                echo "<td>{$games}</td>";
                echo "<td>{$contact['EMail']}</td>";
                echo ver_string($contact['EMail_verified']);
                echo "<td>{$contact['Mobile']}</td>";
                echo ver_string($contact['Mobile_verified']);
                echo "</tr>";
            }
            $result->free();
            $conn->close();
            ?>
        </table>
    </div>
</body>

</html>