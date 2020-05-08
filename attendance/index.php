<?php
require '../classes.php';
require '../student.php';

$lim_days = isset($_GET['lim_days']) ? $_GET['lim_days'] : 0;
$subjects = isset($_GET['subs']) ? explode(',', $_GET['subs']) : [];

$table = 'xii_sc_a_attendance';

use ScA\Classes\SchedClass;

use const ScA\DB;
use const ScA\DB_HOST;
use const ScA\DB_PWD;
use const ScA\DB_USER;

use const ScA\Classes\SCHEDULE_BEAUTY_MULTILINE;
use const ScA\Classes\SCHEDULE_BEAUTY_SINGLELINE;

?>


<html lang='en'>

<head>
    <meta charset="utf-8">
    <title>XII Sc A - Student Attendance</title>
    <script src='script.js'>
    </script>
    <link rel='stylesheet' type='text/css' href='stylesheet.css' />
</head>

<body onload="beautify()">

    <h1 align='center'>XII Sc A - Student Attendance</h1>
    <hr />
    <p align='center'>
        <i>
            <?php
            date_default_timezone_set("Asia/Kolkata");
            echo "Report generated on " . date("d M Y h:i:sa") . " IST.";
            if ($subjects) {
                $subs = array();
                foreach ($subjects as $subject) {
                    array_push($subs, $SUBCODES[$subject]);
                }
                $subs = implode(', ', $subs);
                echo "<br/>Viewing data for {$subs}.";
            }
            ?>
        </i>
    </p>

    <div>
        <table class='nav'>
            <tr>
                <td onclick="show(this, 'data')" class='button' id='dbut'>Show Studentwise Attendance of Recent
                    Classes</td>
                <td onclick="show(this, 'summary')" class='button' id='sbut'>Show Summary of All Classes</td>
                <td onclick="show(this, 'stusum')" class='button' id='stbut'>Show Attendance % of Students
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

        $classes = SchedClass::get_last_classes($conn, $lim_days, $subjects);
        $class_s = [];
        foreach ($classes as $class) {
            array_push($class_s, $class->as_colname('`'));
        }
        $class_s = implode(", ", $class_s);

        $sql = "SELECT LPAD(row_number() over ( order by Name), 2, 0) `Serial No.`, Name, "
            . $class_s .
            " FROM {$table}";

        // Query
        $result = $conn->query($sql);
        if (!$result) {
            die("Query to show fields from table failed. Error code: E_A01.");
        }

        echo "<table border='1'><tr>";
        print("<th>Serial No.</th>");
        print("<th>Name</th>");
        foreach ($classes as $class) {
            print("<th>" . $class->beautify(SCHEDULE_BEAUTY_MULTILINE) . "</th>");
        }
        echo "</tr>\n";
        // printing table rows
        while ($row = $result->fetch_row()) {
            echo "<tr>";

            // $row is array... foreach( .. ) puts every element
            // of $row to $cell variable
            foreach ($row as $cell)
                echo "<td>$cell</td>";

            echo "</tr>\n";
        }
        $result->free();
        //$conn->close();
        echo "</table>";
        ?>
    </div>

    <div class='tab' id='summary' style='display: none;'>
        <table>
            <tr>
                <td>Class</td>
                <td class='green' style="text-align: center;">P</td>
                <td class='red' style="text-align: center;">A</td>
                <td style="text-align: center;">Attendance %</td>
            </tr>
            <?php

            $classes = SchedClass::get_classes_from($conn, strtotime("2020-04-03"), $subjects);

            foreach ($classes as $class) {
                echo "<tr>";
                echo "<td>" . $class->beautify(SCHEDULE_BEAUTY_SINGLELINE) . "</td>";
                $col = $class->as_colname('`');
                $sql = "SELECT LPAD(COUNT(Name), 2, 0) FROM xii_sc_a_attendance GROUP BY {$col} ORDER BY {$col} DESC";
                $r2 = $conn->query($sql);
                if (!$r2) {
                    die("Query to show fields from table failed. Error Code: E_A02.");
                }
                $rows = $r2->fetch_all();
                if (!isset($rows[1])) {
                    echo "<td colspan=3 style=\"text-align: center;\"><b>NO DATA</b></td>";
                    continue;
                }
                $p = $rows[0][0];
                $a = $rows[1][0];
                $n = floatval($p) / (floatval($p) + floatval($a)) * 100;
                echo "<td style=\"text-align: center;\">" . $p . "</td>";
                echo "<td style=\"text-align: center;\">" . $a . "</td>";
                if ($n > 80) {
                    $n = number_format($n, 2);
                    echo "<td class ='green' style='text-align: center;'>{$n}%</td>";
                } elseif ($n > 50) {
                    $n = number_format($n, 2);
                    echo "<td class ='yellow' style='text-align: center;'>{$n}%</td>";
                } else {
                    $n = number_format($n, 2);
                    echo "<td class ='red' style='text-align: center;'>{$n}%</td>";
                }
                echo "</tr>";
                $r2->free();
            }
            $conn->close();

            ?>
        </table>
    </div>

    <div class='tab' id='stusum' style='display: none;'>

        <?php


        $conn = new mysqli(DB_HOST, DB_USER, DB_PWD, DB);

        // Check connection
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        $classes = SchedClass::get_classes_from($conn, strtotime("2020-04-03"), $subjects);
        $class_s = [];
        foreach ($classes as $class) {
            array_push($class_s, $class->as_colname('`'));
        }
        $class_s = implode(", ", $class_s);

        $sql = "SELECT LPAD(row_number() over ( order by Name), 2, 0) `Serial No.`, Name, "
            . $class_s .
            " FROM {$table}";

        // Query
        $result = $conn->query($sql);
        if (!$result) {
            die("Query to show fields from table failed. Error Code: E_A03.");
        }

        echo "<table border='1'><tr>";
        echo "<th>Serial No.</th>";
        echo "<th>Name</th>";
        echo "<th class='green'>P</th>";
        echo "<th class='red'>A</th>";
        echo "<th>Total</th>";
        echo "<th>Attendance %</th>";
        echo "</tr>";
        while ($row = $result->fetch_assoc()) {
            $att = (new \ScA\Student\Student($row['Name']))->get_attendance_data();
            echo "<tr>";
            echo "<td style='text-align: center;'>{$row['Serial No.']}</td>";
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
</body>

</html>