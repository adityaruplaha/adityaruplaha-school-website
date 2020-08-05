<?php


require_once "../login.php";
require_once "../student.php";

use \ScA\Student\TGLogin\TGLogin;

$s = TGLogin::from_cookie();
if ($s != NULL) {
    $s = new \ScA\Student\Student(NULL, $s->id);
    if (!$s->has_privileges("Admin")) {
        $s = NULL;
    }
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
        XII Sc A - Administrators Portal
    </title>
    <link rel='stylesheet' type='text/css' href='/sc_a/themes/dark/base.css' />
    <link rel='stylesheet' type='text/css' href='/sc_a/themes/dark/tables.css' />
</head>

<body>
    <h1>XII Sc A - Administrators Portal</h1>
    <hr />
    <div>
        <table class='fullwidth bigfont centercells'>
            <tr>
                <td><a href='command_logs/'>Command Logs</a></td>
                <td><a href='details/'>Student Details</a></td>
            </tr>
            <tr>
                <td><a href='trello_upload_recommendation/'>Trello Uploads Recommendations</a></td>
                <td><a href='post/'>Post Assignment/Resource</a></td>
            </tr>
            <?php
            if ($s->has_privileges("Super Admin")) {
                echo "
                    <tr>
                    <tr>
                        <td colspan=2><br /></td>
                    </tr>
                    <td><a href='broadcast/'>Broadcast to Telegram</a></td>
                    <td><a href='cbseinfo/'>Check CBSE Info</a></td>                    
                    </tr>
                    <tr>
                    <td><a href='telemetry/'>Telemetry</a></td>
                    </tr>
                ";
            }
            ?>
            <tr>
                <td colspan="2"><br /></td>
            </tr>
            <tr>
                <td colspan="2">
                    <a href='../'>Open Students' Portal</a>
                </td>
            </tr>
        </table>
    </div>
    <hr />
    <div>
        <h2 class='center'>Uploads requiring Attention</h2>
        <br />
        <table class='reducedwidth centercells smallfont bordered'>
            <tr>
                <th>Date</th>
                <th>URL</th>
                <th>Uploader</th>
                <th>Status</th>
            </tr>
            <?php

            require_once "../defs.php";

            use const ScA\DB;
            use const ScA\DB_HOST;
            use const ScA\DB_PWD;
            use const ScA\DB_USER;

            $conn = new mysqli(DB_HOST, DB_USER, DB_PWD, DB);;

            // Check connection
            if ($conn->connect_error) {
                die("Connection failed: " . $conn->connect_error);
            }

            // Query
            $result = $conn->query("SELECT * FROM days WHERE Status = 'Pending' OR Status = 'Awaiting Approval'");
            if (!$result) {
                die("Query to show fields from table failed");
            }

            $uploads = $result->fetch_all(MYSQLI_ASSOC);

            foreach ($uploads as $upload) {
                echo "<tr>";
                $date = strtotime($upload["Date"]);
                $date = strftime("%d %B %Y", $date);
                $url = $upload["PrivateTrello"];
                $uploader = $upload["UploadedBy"];
                $stat = $upload["Status"];
                echo "<td>{$date}</td>";
                echo "<td><a class='compact' href='{$url}'>{$url}</a></td>";
                echo "<td>{$uploader}</td>";
                echo "<td>{$stat}</td>";
                echo "</tr>";
            }

            ?>
        </table>
    </div>
    <div>
        <h2 class='center'>Recent/Upcoming Uploads</h2>
        <br />
        <table class='reducedwidth centercells smallfont bordered'>
            <tr>
                <th>Date</th>
                <th>URL</th>
                <th>Uploader</th>
                <th>Status</th>
            </tr>
            <?php

            $threshold = strftime("%Y-%m-%d", time() - 2 * 86400);

            // Query
            $result = $conn->query("SELECT * FROM days WHERE Date > '{$threshold}'");
            if (!$result) {
                die("Query to show fields from table failed");
            }

            $uploads = $result->fetch_all(MYSQLI_ASSOC);

            foreach ($uploads as $upload) {
                echo "<tr>";
                $date = strtotime($upload["Date"]);
                $date = strftime("%d %B %Y", $date);
                $url = $upload["PrivateTrello"];
                $uploader = $upload["UploadedBy"];
                $stat = $upload["Status"];
                echo "<td>{$date}</td>";
                echo "<td><a class='compact' href='{$url}'>{$url}</a></td>";
                echo "<td>{$uploader}</td>";
                echo "<td>{$stat}</td>";
                echo "</tr>";
            }

            ?>
        </table>
    </div>
</body>