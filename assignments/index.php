<?php

require_once "../login.php";


use \ScA\Student\TGLogin\TGLogin;


$s = TGLogin::from_cookie();

$is_logged_in = ($s != NULL);

Deprecate\disable_page();

if ($s != NULL) {
    $s = (new \ScA\Student\Student(NULL, $s->id));
    $s->report_url_visit($_SERVER['PHP_SELF']);
}

if (!$is_logged_in) {
    header("Location: ../?nauth");
    exit;
}


require_once "../defs.php";

use const ScA\DB;
use const ScA\DB_HOST;
use const ScA\DB_PWD;
use const ScA\DB_USER;

$table = 'assignments';

$conn = new mysqli(DB_HOST, DB_USER, DB_PWD, DB);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$d = date("Y-m-d");

$SUBCODES = array(
    "phy" => "Physics",
    "chem" => "Chemistry",
    "math" => "Mathematics",
    "cs" => "Computer Science",
    "en" => "English",
    "pe" => "Physical Education",
    "bn" => "Bengali",
    "hi" => "Hindi",
    "any" => "Miscellaneous",
);

$ASS = array();
foreach ($SUBCODES as $sub => $v) {
    $result = $conn->query("SELECT COUNT(`Name`) FROM {$table} WHERE `Subject` = '{$sub}'");
    if (!$result) {
        die("Query to show fields from table failed.");
    }
    $ASS[$sub] = $result->fetch_row()[0];
}


function remote_file_exists($url)
{
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_NOBODY, 1);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1); # handles 301/2 redirects
    curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return $httpCode == 200;
}

function starts_with($haystack, $needle)
{
    return strpos($haystack, $needle) === 0;
}

function check_url($url)
{
    if (isset($_GET["checkurls"]) && !remote_file_exists($url)) {
        return ["[Dead Link]", ["red"]];
    }
    if (starts_with($url, "https://res.cloudinary.com")) {
        return ["[Dead Link]", ["red"]];
    }
    $ad_classes = [];
    if (starts_with($url, "http://")) {
        array_push($ad_classes, "yellow");
    }
    if (starts_with($url, "https://classroom.google.com/")) {
        return ["Join", $ad_classes];
    } elseif (starts_with($url, "https://trello.com/")) {
        return ["Open Card", $ad_classes];
    } elseif (
        starts_with($url, "https://trello-attachments.s3.amazonaws.com/") ||
        starts_with($url, "https://s3.ap-south-1.amazonaws.com/res.cloudinary-s3-vawsum-new-media/") ||
        starts_with($url, "http://schoolatweb.byethost7.com/")
    ) {
        return ["Download", $ad_classes];
    } elseif (starts_with($url, "https://drive.google.com/")) {
        return ["Open in Drive", $ad_classes];
    } else {
        return ["Open", $ad_classes];
    }
}

?>

<!DOCTYPE html>
<html lang='en'>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=0.75">
    <title>XII Sc A - Assignments</title>
    <script src='script.js'>
    </script>
    <?php
    echo "<script>";
    echo "function showint(n) {";
    $buf = [];
    foreach ($SUBCODES as $sub => $v) {
        array_push($buf, "'{$sub}'");
    }
    echo "var subs = [" . implode(",", $buf) . "];";
    echo "show(subs[n]);";
    echo "}";
    echo "</script>";
    ?>
    <script src="/sc_a/scripts/sorttable.js"></script>
    <link rel='stylesheet' type='text/css' href='/sc_a/themes/dark/base.css' />
    <link rel='stylesheet' type='text/css' href='/sc_a/themes/dark/tables.css' />
    <link rel='stylesheet' type='text/css' href='/sc_a/themes/dark/select.css' />
</head>

<body onload="clean(); showint(0);">

    <h1 class='center'>XII Sc A - Assignments</h1>
    <hr />
    <p class='center'>
        <i>
            <?php
            date_default_timezone_set("Asia/Kolkata");
            echo "Report generated on " . date("d M Y h:i:sa") . " IST."
            ?>
        </i>
    </p>
    <hr />
    <p>
        <label for="subject">Select Subject: </label>&nbsp;&nbsp;
        <select name="subject" id="subject" onchange="showint(value)" class="select-css center">
            <?php
            $i = 0;
            foreach ($SUBCODES as $k => $v) {
                $a = $ASS[$k];
                echo "<option class='button' value='{$i}'>{$v} ({$a})</option>";
                echo "</tr>";
                $i += 1;
            }
            ?>
        </select>
    </p>

    <?php

    foreach ($SUBCODES as $sub => $SUB) {

        // Query
        $result = $conn->query("SELECT `Name`, `Notes`, `AssignedOn`,
        `DueOn`, `URL` FROM {$table} WHERE `Subject` = '{$sub}' ORDER BY `{$table}`.`AssignedOn` ASC");

        if (!$result) {
            die("Query to show fields from table failed.");
        }

        echo "
        <div class='tab' id='{$sub}'>
        <style>
        table.sortable th:not(.sorttable_sorted):not(.sorttable_sorted_reverse):not(.sorttable_nosort):after { 
            content: \" \\25B4\\25BE\" 
        }
        </style>
        <table class='center autowidth semibordered conpact sortable'>
            <tr>
                <th>Assignment</th>
                <th>Assigned On</th>
                <th>Due On</th>
                <th class='sorttable_nosort'>File/URL</th>
                <th class='sorttable_nosort'>Notes</th>
            </tr>";

        while ($assignment = $result->fetch_assoc()) {
            echo "<tr>";

            echo "<td>" . $assignment["Name"] . "</td>";
            echo "<td sorttable_customkey='{$assignment["AssignedOn"]}'>" . strftime('%d %B %Y', strtotime($assignment["AssignedOn"])) . "</td>";

            echo ($d = $assignment["DueOn"]) ? "<td sorttable_customkey='{$d}'>" . strftime('%d %B %Y', strtotime($d)) . "</td>" : "<td></td>";
            if ($l = $assignment["URL"]) {
                list($text, $ad_classes) = check_url($l);
                echo "<td><a class='compact " . implode(" ", $ad_classes) . "' href=\"" . $l . "\">" . $text . "</a></td>";
            } else {
                echo "<td></td>";
            }

            if ($r = $assignment["Notes"]) {
                echo "<td class='button' onclick=\"alert(`" . htmlspecialchars($r) . "`);\">Click to view.</td>";
            } else {
                echo "<td></td>";
            }

            echo "</tr>";
        }

        echo "</table>
        <p>You are viewing assignments for {$SUB}.</p>
        </div>";
        $result->free();
    }

    ?>
</body>

</html>
