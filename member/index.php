<?php


require_once "../login.php";
require_once "../student.php";

use \ScA\Student\TGLogin\TGLogin;

$s = TGLogin::from_cookie();
if ($s != NULL) {
    $s = new \ScA\Student\Student(NULL, $s->id);
}
$is_logged_in = ($s != NULL);

if (!$is_logged_in) {
    header("Location: ../?nauth");
    exit;
}

?>
<!DOCTYPE html>
<html lang='en'>

<head>
    <title>
        XII Sc A - Members Portal
    </title>
    <link rel='stylesheet' type='text/css' href='stylesheet.css' />
    <meta name="viewport" content="width=device-width, initial-scale=0.75">
</head>

<body>
    <h1>XII Sc A - Members Portal</h1>
    <hr />
    <div>
        <h2>Details</h2><br />
        <table class='center' style="table-layout: auto;">
            <?php
            $info = $s->get_contact_info();
            echo "<tr>";
            echo "<td style='text-align: right;'>Name:</td><td style='text-align: left;'>{$info['Name']}</td>";
            echo "</tr>";
            echo "<tr>";
            echo "<td style='text-align: right;'>Email:</td><td style='text-align: left;'>{$info['EMail']}</td>";
            echo "</tr>";
            if ($m2 = $info["Mobile2"]) {
                echo "<tr>";
                echo "<td rowspan=2>Mobile No.(s):</td><td style='text-align: left;'>{$info['Mobile']}</td>";
                echo "</tr>";
                echo "<tr>";
                echo "<td style='text-align: left;'>{$m2}</td>";
                echo "</tr>";
            } else {
                echo "<tr>";
                echo "<td style='text-align: right;'>Mobile No.(s):</td><td style='text-align: left;'>{$info['Mobile']}</td>";
                echo "</tr>";
            }
            ?>
        </table>
        <br />
        <hr />
        <h2>Attendance</h2><br />
        <table class='center' style="table-layout: auto;">
            <?php
            $info = $s->get_attendance_summary();
            echo "<tr>";
            echo "<td style='text-align: right;'>Present:</td><td style='text-align: left;'>{$info['P']}/{$info['Total']}</td>";
            echo "</tr>";
            echo "<tr>";
            echo "<td style='text-align: right;'>Absent:</td><td style='text-align: left;'>{$info['A']}/{$info['Total']}</td>";
            echo "</tr>";
            echo "<tr>";
            echo "<td style='text-align: right;'>Attendance %:</td>";
            $n = round($info['Attendance %'] * 100, 2);
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

            ?>
            <tr>
                <td colspan=2><a href='attendance/'>See Details</a></td>
            </tr>
        </table>
        <br />
        <hr />
        <h2>Quick Links</h2>
        <table class='center'>
            <tr>
                <td>
                    <a href='/go/?url=https://trello.com/b/GsKINBwD/'>
                        Open Bulletin Board: Private
                    </a>
                </td>
                <?php
                if ($s->has_privileges("Admin") || $is_teacher) {
                    echo "
                    <td><a href='../admin/'>Open Admin Portal</a></td>
                ";
                }
                ?>
            </tr>
            <tr>
                <td colspan="2">
                    <a href='../'>Open Students' Portal</a>
                </td>
            </tr>
        </table>
    </div>
    <?php
    if (!$s->has_privileges("Member")) {
        echo "</body></html>";
        exit;
    }
    ?>
    <hr />
    <div>
        <h2>Uploads</h2><br />
        <table class='bordered'>
            <tr>
                <th>Date</th>
                <th>URL</th>
                <th>Status</th>
            </tr>
            <?php
            $uploads = $s->get_uploads_info();
            foreach ($uploads as $upload) {
                echo "<tr>";
                $date = strtotime($upload["Date"]);
                $date = strftime("%d %B %Y", $date);
                $url = $upload["PrivateTrello"];
                $stat = $upload["Status"];
                echo "<td>{$date}</td>";
                echo "<td><a href='{$url}'>{$url}</a></td>";
                echo "<td>{$stat}</td>";
                echo "</tr>";
            }

            ?>
        </table>
    </div>
</body>

</html>