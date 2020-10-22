<?php

require_once "../../login.php";

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
    header("Location: ../?nauth");
    exit;
}

require_once '../../classes.php';
require_once '../../student.php';

$subjects = isset($_GET['subs']) ? explode(',', $_GET['subs']) : [];

$from = isset($_GET['from']) ? $_GET['from'] : "2020-04-03";
$from = strtotime($from);
$from = max($from, strtotime("2020-04-03"));

$to = isset($_GET['to']) ? $_GET['to'] : "today";
$to = strtotime($to);
$to = min($to, strtotime("today"));

use const ScA\DB;
use const ScA\DB_HOST;
use const ScA\DB_PWD;
use const ScA\DB_USER;

$conn = new mysqli(DB_HOST, DB_USER, DB_PWD, DB);

$classes = ScA\Classes\SchedClass::get_classes_between($conn, $from, $to, $subjects);

use const ScA\Classes\SCHEDULE_BEAUTY_SINGLELINE;

?>

<!DOCTYPE html>
<html lang='en'>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=0.8">
    <title>XII Sc A - Attendance Stats</title>
    <script src='script.js'>
    </script>
    <link rel='stylesheet' type='text/css' href='/sc_a/themes/dark/base.css' />
    <link rel='stylesheet' type='text/css' href='/sc_a/themes/dark/tables.css' />
</head>

<body onload="beautify()">

    <h1 class='center'>XII Sc A - Attendance Stats</h1>
    <hr />
    <p class='center'>
        <i>
            <?php
            date_default_timezone_set("Asia/Kolkata");
            echo "Report generated on " . date("d M Y h:i:sa") . " IST.";
            ?>
        </i>
        <br />
        <?php
        if ($subjects) {
            $subs = array();
            foreach ($subjects as $subject) {
                array_push($subs, \ScA\Classes\SUBCODES[$subject]);
            }
            $subs = implode(', ', $subs);
            echo "<br/>Viewing data for {$subs}.";
        }
        if (isset($_GET['from']) && isset($_GET['to'])) {
            $f = strftime("%d %B %Y", $from);
            $t = strftime("%d %B %Y", $to);
            echo "<br/>Viewing data for Date Range: {$f} - {$t}.";
        } else if (isset($_GET['from'])) {
            $f = strftime("%d %B %Y", $from);
            echo "<br/>Viewing data for Date Range: {$f} onwards.";
        }
        ?>
    </p>

    <hr />
    <br />
    <table class='center autowidth semibordered bicolumn'>
        <?php
        $info = $s->get_attendance_summary($classes);
        echo "<tr>";
        echo "<td style='text-class: right;'>Present:</td><td style='text-class: left;'>{$info['P']}/{$info['Total']}</td>";
        echo "</tr>";
        echo "<tr>";
        echo "<td style='text-class: right;'>Absent:</td><td style='text-class: left;'>{$info['A']}/{$info['Total']}</td>";
        echo "</tr>";
        echo "<tr>";
        echo "<td style='text-class: right;'>Attendance %:</td>";
        $n = round($info['Attendance %'] * 100, 2);
        if ($n > 75) {
            $n = number_format($n, 2);
            echo "<td class ='green' style='text-class: center;'>{$n}%</td>";
        } elseif ($n > 40) {
            $n = number_format($n, 2);
            echo "<td class ='yellow' style='text-class: center;'>{$n}%</td>";
        } else {
            $n = number_format($n, 2);
            echo "<td class ='red' style='text-class: center;'>{$n}%</td>";
        }
        echo "</tr>";

        ?>
    </table>
    <br />

    <div class='tab'>

        <?php

        echo "<table class='center autowidth semibordered centercells hoverable'><tr>";
        echo "<th>Class</th>";
        echo "<th>Attendance</th>";
        echo "</tr>";
        $att = (new \ScA\Student\Student($s->name))->get_attendance_data(array_reverse($classes));
        foreach ($att as list($class, $present)) {
            if ($present === NULL) {
                continue;
            }
            echo "<tr>";
            echo "<td style='text-align: left;'>" . $class->beautify(SCHEDULE_BEAUTY_SINGLELINE) . "</td>";
            if ($present) {
                echo "<td class='green'>P</td>";
            } else {
                echo "<td class='red'>A</td>";
            }
            echo "</tr>";
        }
        $result->free();
        $conn->close();
        echo "</table>";
        ?>
    </div>
</body>

</html>