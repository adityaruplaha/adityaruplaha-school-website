<?php

require_once "../login.php";


use \ScA\Student\TGLogin\TGLogin;


$s = TGLogin::from_cookie();

$is_logged_in = ($s != NULL);

if ($s != NULL) {
    $s = (new \ScA\Student\Student(NULL, $s->id));
    $s->report_url_visit($_SERVER['PHP_SELF']);
}

if (!$is_logged_in) {
    header("Location: ../?nauth");
    exit;
}

require_once '../classes.php';
require_once '../student.php';

$lim_days = isset($_GET['lim_days']) ? $_GET['lim_days'] : 0;
$subjects = isset($_GET['subs']) ? explode(',', $_GET['subs']) : [];

$from = isset($_GET['from']) ? $_GET['from'] : "2020-04-03";
$from = strtotime($from);
$from = max($from, strtotime("2020-04-03"));

$to = isset($_GET['to']) ? $_GET['to'] : "today";
$to = strtotime($to);
$to = min($to, strtotime("today"));

$using_date_range = isset($_GET['from']);

$table = 'attendance';

use ScA\Classes\SchedClass;

use const ScA\DB;
use const ScA\DB_HOST;
use const ScA\DB_PWD;
use const ScA\DB_USER;

use const ScA\Classes\SCHEDULE_BEAUTY_MULTILINE;
use const ScA\Classes\SCHEDULE_BEAUTY_SINGLELINE;

?>

<!DOCTYPE html>
<html lang='en'>

<head>
    <meta charset="utf-8">
    <title>XII Sc A - Student Attendance</title>
    <script src='/sc_a/scripts/tab.js'>
    </script>
    <script src='/sc_a/scripts/paginate.js'>
    </script>
    <link rel='stylesheet' type='text/css' href='/sc_a/themes/dark/base.css' />
    <link rel='stylesheet' type='text/css' href='/sc_a/themes/dark/tables.css' />
    <link rel='stylesheet' type='text/css' href='/sc_a/themes/dark/tabs.css' />
    <link rel='stylesheet' type='text/css' href='/sc_a/themes/dark/pages.css' />
</head>

<body onload='autoload(0)'>

    <h1 class='center'>XII Sc A - Student Attendance</h1>
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
        if ($from > $to) {
            die("<br/><br/><span class='red'><b>Invalid Date Range! <code>from</code> is later than <code>to</code>.</b></span>");
        }
        ?>
    </p>

    <div>
        <table class='nav'>
            <tr>
                <?php
                $str = "Studentwise Attendance of Recent Classes";
                if ($using_date_range) {
                    $str = "Studentwise Attendance of Selected Classes";
                }
                ?>
                <td onclick="show(this, 'data')" class='tab_button'>
                    <?php echo $str;
                    unset($str); ?>
                </td>
                <?php
                $str = "Classwise Attendance of All Classes";
                if ($using_date_range) {
                    $str = "Classwise Attendance of Selected Classes";
                }
                ?>
                <td onclick="show(this, 'summary')" class='tab_button'>
                    <?php echo $str;
                    unset($str); ?>
                </td>
                <?php
                $str = "Studentwise Attendance Statistics";
                if ($using_date_range) {
                    $str = "Studentwise Attendance Statistics in Selected Classes";
                }
                ?>
                <td onclick="show(this, 'stusum')" class='tab_button'>
                    <?php echo $str;
                    unset($str); ?>
                </td>
            </tr>
        </table>
        <br />
    </div>

    <div class='tab' id='data'>

        <?php

        $conn = new mysqli(DB_HOST, DB_USER, DB_PWD, DB);

        // Check connection
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        $classes = [];
        if ($using_date_range) {
            $classes = SchedClass::get_classes_between($conn, $from, $to, $subjects);
        } else {
            $classes = SchedClass::get_last_classes($conn, $lim_days, $subjects);
        }
        if ($classes) {
            echo "<table class='semibordered center autowidth'><tr>";
            echo "<th>Name</th>";
            foreach ($classes as $class) {
                print("<th>" . $class->beautify(SCHEDULE_BEAUTY_MULTILINE) . "</th>");
            }
            echo "</tr>\n";

            $sql = "SELECT Name FROM {$table}";
            $result = $conn->query($sql);
            if (!$result) {
                die("Query to show fields from table failed. Error Code: E_A01.");
            }
            while ($row = $result->fetch_assoc()) {
                $att = (new \ScA\Student\Student($row['Name']))->get_attendance_data($classes);
                echo "<tr>";
                echo "<td>" . $row['Name'] . "</td>";
                foreach ($att as list($class, $present)) {
                    if ($present === NULL) {
                        echo "<td></td>";
                        continue;
                    }
                    if ($present) {
                        echo "<td class='green' style='text-align: center;'>P</td>";
                    } else {
                        echo "<td class='red' style='text-align: center;'>A</td>";
                    }
                }
                echo "</tr>";
            }
            $result->free();
            echo "</table>";
        } else {
            echo "No data to show.";
        }
        ?>
    </div>

    <div class='tab' id='summary'>
        <table class="semibordered autowidth center" id="summary_table">
            <tr>
                <td>Class</td>
                <td class='green' style="text-align: center;">P</td>
                <td class='red' style="text-align: center;">A</td>
                <td style="text-align: center;">Attendance %</td>
            </tr>
            <?php

            $classes = SchedClass::get_classes_between($conn, $from, $to, $subjects);

            foreach ($classes as $class) {
                echo "<tr>";
                echo "<td>" . $class->beautify(SCHEDULE_BEAUTY_SINGLELINE) . "</td>";
                $r = $class->get_attendance_data($conn);
                if (!is_array($r)) {
                    echo "<td colspan=3 style=\"text-align: center;\"><b>" . $r . "</b></td>";
                    continue;
                }
                $p = str_pad("{$r['P']}", 2, '0', STR_PAD_LEFT);
                $a = str_pad("{$r['A']}", 2, '0', STR_PAD_LEFT);
                $n = $r["%"] * 100;
                echo "<td style=\"text-align: center;\">" . $p . "</td>";
                echo "<td style=\"text-align: center;\">" . $a . "</td>";
                if ($n > 80) {
                    $n = number_format($n, 2);
                    $n = str_pad($n, 2, '0', STR_PAD_LEFT);
                    echo "<td class ='green' style='text-align: center;'>{$n}%</td>";
                } elseif ($n > 50) {
                    $n = number_format($n, 2);
                    $n = str_pad($n, 2, '0', STR_PAD_LEFT);
                    echo "<td class ='yellow' style='text-align: center;'>{$n}%</td>";
                } else {
                    $n = number_format($n, 2);
                    $n = str_pad($n, 2, '0', STR_PAD_LEFT);
                    echo "<td class ='red' style='text-align: center;'>{$n}%</td>";
                }
                echo "</tr>";
            }

            ?>
        </table>
    </div>

    <div class='tab' id='stusum'>

        <?php

        $sql = "SELECT Name FROM {$table}";

        // Query
        $result = $conn->query($sql);
        if (!$result) {
            die("Query to show fields from table failed. Error Code: E_A03.");
        }

        echo "<table class='semibordered center autowidth'><tr>";
        echo "<th>Name</th>";
        echo "<th class='green'>P</th>";
        echo "<th class='red'>A</th>";
        echo "<th>Total</th>";
        echo "<th>Attendance %</th>";
        echo "</tr>";
        while ($row = $result->fetch_assoc()) {
            $stu = (new \ScA\Student\Student($row['Name']));
            $classes = SchedClass::get_classes_between($conn, $from, $to, $subjects);
            $att = $stu->get_attendance_summary($classes);
            echo "<tr>";
            echo "<td>{$row['Name']}</td>";
            $p = str_pad($att['P'], 2, '0', STR_PAD_LEFT);
            echo "<td style='text-align: center;'>{$p}</td>";
            $a = str_pad($att['A'], 2, '0', STR_PAD_LEFT);
            echo "<td style='text-align: center;'>{$a}</td>";
            $t = str_pad($att['Total'], 2, '0', STR_PAD_LEFT);
            echo "<td style='text-align: center;'>{$t}</td>";
            $n = round($att['Attendance %'] * 100, 2);
            if ($n > 75) {
                $n = number_format($n, 2);
                echo "<td class ='green' style='text-align: center;'>{$n}%</td>";
            } elseif ($n > 40) {
                $n = number_format($n, 2);
                echo "<td class ='yellow' style='text-align: center;'>{$n}%</td>";
            } else {
                $n = number_format($n, 2);
                echo "<td class ='red' style='text-align: center;'>{$n}%</td>";
            }
            echo "</tr>";
        }
        $result->free();
        $conn->close();
        echo "</table>";
        ?>
    </div>
    <script>
        paginate(document.getElementById('summary_table'), 40)
        show_page('summary_table', 0);
    </script>
</body>

</html>
