<?php


require_once "../login.php";
require_once "../student.php";

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

?>
<!DOCTYPE html>
<html lang='en'>

<head>
    <title>
        XII Sc A - Members Portal
    </title>
    <link rel='stylesheet' type='text/css' href='/sc_a/themes/dark/base.css' />
    <link rel='stylesheet' type='text/css' href='/sc_a/themes/dark/tables.css' />
    <meta name="viewport" content="width=device-width, initial-scale=0.75">
</head>

<body>
    <h1>XII Sc A - Members Portal</h1>
    <hr />
    <div>
        <h2 class='center'>Details</h2><br />
        <table class='autowidth unbordered center bicolumn bigfont'>
            <?php
            $contact = $s->get_contact_info();
            $mob = $contact["Mobile"];
            echo "<tr>";
            echo "<td style='text-align: right;'>Name:</td><td style='text-align: left;'>{$contact['Name']}</td>";
            echo "</tr>";
            echo "<tr>";
            echo "<td style='text-align: right;'>Email:</td><td style='text-align: left;'>{$contact['EMail']}</td>";
            echo "</tr>";
            if ($m2 = $contact["Mobile2"]) {
                echo "<tr>";
                echo "<td rowspan=2>Mobile No.(s):</td><td style='text-align: left;'>{$mob}</td>";
                echo "</tr>";
                echo "<tr>";
                echo "<td style='text-align: left;'>{$m2}</td>";
                echo "</tr>";
            } else {
                echo "<tr>";
                echo "<td style='text-align: right;'>Mobile No.(s):</td><td style='text-align: left;'>{$mob}</td>";
                echo "</tr>";
            }
            ?>
        </table>
        <br />
        <hr /><br />
        <h2 class='center'>Attendance</h2><br />
        <table class='autowidth unbordered center bicolumn bigfont'>
            <?php
            $att = $s->get_attendance_summary();
            echo "<tr>";
            echo "<td style='text-align: right;'>Present:</td><td style='text-align: left;'>{$att['P']}/{$att['Total']}</td>";
            echo "</tr>";
            echo "<tr>";
            echo "<td style='text-align: right;'>Absent:</td><td style='text-align: left;'>{$att['A']}/{$att['Total']}</td>";
            echo "</tr>";
            echo "<tr>";
            echo "<td style='text-align: right;'>Attendance %:</td>";
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

            ?>
            <tr>
                <td style='text-align: center;' colspan=2><a href='attendance/'>See Details</a></td>
            </tr>
        </table>
        <br />
        <hr /><br />
        <h2 class='center'>Quick Links</h2>
        <table class='autowidth unbordered center mediumfont centercells'>
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
            <tr></tr>
            <tr>
                <td colspan="2">
                    <a href='cbseinfo/'>
                        Check CBSE Info
                    </a>
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
    <hr /><br />
    <div>
        <h2 class='center'>Uploads</h2><br />
        <table class='reducedwidth bordered center smallfont centercells'>
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
                echo "<td><a class='compact' href='{$url}'>{$url}</a></td>";
                echo "<td>{$stat}</td>";
                echo "</tr>";
            }

            ?>
        </table>
    </div>
</body>

</html>