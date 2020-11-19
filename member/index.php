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
    <script src='/sc_a/scripts/tab.js'>
    </script>
    <link rel='stylesheet' type='text/css' href='/sc_a/themes/dark/base.css' />
    <link rel='stylesheet' type='text/css' href='/sc_a/themes/dark/tables.css' />
    <link rel='stylesheet' type='text/css' href='/sc_a/themes/dark/tabs.css' />
    <link rel='stylesheet' type='text/css' href='/sc_a/themes/dark/cards.css' />
    <link rel='stylesheet' type='text/css' href='/sc_a/themes/dark/icons.css' />
    <link rel='stylesheet' type='text/css' href='/sc_a/themes/dark/flex.css' />
    <meta name="viewport" content="width=device-width, initial-scale=0.75">
    <script src="https://code.iconify.design/1/1.0.7/iconify.min.js"></script>
</head>

<body onload='autoload(0)'>
    <h1>XII Sc A - Members Portal</h1>
    <table class='nav mediumfont'>
        <tr>
            <td onclick="show(this, 'information')" class='tab_button'>Information</td>
            <td onclick="show(this, 'uploads')" class='tab_button'>Uploads</td>
        </tr>
    </table>
    <div class='tab cardholder' id='information'>
        <div class='card deconstructed_pancake' id='details'>
            <br />
            <div class='deconstructed_pancake_card'>
                <table class='autowidth unbordered center bicolumn bigfont'>
                    <?php

                    function render_sub($sub)
                    {
                        return array(
                            "phy" => "Physics",
                            "chem" => "Chemistry",
                            "math" => "Mathematics",
                            "cs" => "Computer Science",
                            "en" => "English",
                            "pe" => "Physical Education",
                            "bn" => "Bengali",
                            "hi" => "Hindi"
                        )[$sub];
                    }

                    function render_subs($subs_str)
                    {
                        return implode(", ", array_map("render_sub", explode(",", $subs_str)));
                    }

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
                    $subs = render_subs($s->get_academic_info()['Subjects']);
                    echo "<tr>";
                    echo "<td style='text-align: right;'>Subjects:</td><td style='text-align: left;'>{$subs}</td>";
                    echo "</tr>";
                    ?>
                </table>
            </div>
            <p class='bigfont deconstructed_pancake_card'>
                <a href='cbseinfo/'>
                    Check CBSE Info
                </a>
                <br />
                <a href='edit/'>
                    Edit
                </a>
            </p>
        </div>
        <div class='card' id='qlinks'>
            <table class='autowidth unbordered center bigfont centercells'>
                <tr>
                    <td>
                        <a href='/go/?url=https://trello.com/b/GsKINBwD/'>
                            Open Bulletin Board: Private
                        </a>
                    </td>
                    <?php
                    if ($s->has_privileges("Admin")) {
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
        <div class='card deconstructed_pancake' id='attendance'>
            <div class='deconstructed_pancake_card'>
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
                        <td style='text-align: center;' colspan=2></td>
                    </tr>
                </table>
                <div class='bigfont center'>
                    <a href='attendance/'>See Details</a>
                </div>
            </div>
            <div class='deconstructed_pancake_card bigfont center'>
                Recently absent on:<br /><br />
                <div class='mediumfont' style='text-align:left;'>
                    <?php
                    $i = 0;

                    use const ScA\Classes\SCHEDULE_BEAUTY_SINGLELINE;

                    foreach ($s->get_attendance_data() as list($class, $present)) {
                        if ($i > 10) {
                            break;
                        }
                        if (!$present and isset($present)) {
                            echo $class->beautify(SCHEDULE_BEAUTY_SINGLELINE) . "<br/>";
                        }
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>
    <?php
    if (!$s->has_privileges("Member")) {
        echo "</body></html>";
        exit;
    }
    ?>
    <div class='tab' id='uploads'>
        <table class='reducedwidth bordered center mediumfont centercells'>
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
