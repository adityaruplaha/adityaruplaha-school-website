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
    <link rel='stylesheet' type='text/css' href='stylesheet.css' />
    </script>
</head>

<body>
    <h1>XII Sc A - Administrators Portal</h1>
    <hr />
    <div>
        <table>
            <tr>
                <td><a href='actions/'>Action Log</a></td>
                <td><a href='details/'>Student Details</a></td>
            </tr>
            <tr>
                <td><a href='trello_upload_recommendation/'>Trello Uploads Recommendations</a></td>
                <?php
                if ($s->has_privileges("Super Admin")) {
                    echo "
                    <td><a href='broadcast/'>Broadcast to Telegram</a></td>
                ";
                }
                ?>
            </tr>
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
        <h2>Uploads requiring Attention</h2>
        <br />
        <table class='bordered'>
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
                echo "<td><a href='{$url}'>{$url}</a></td>";
                echo "<td>{$uploader}</td>";
                echo "<td>{$stat}</td>";
                echo "</tr>";
            }

            ?>
        </table>
    </div>
    <div>
        <h2>Recent/Upcoming Uploads</h2>
        <br />
        <table class='bordered'>
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
                echo "<td><a href='{$url}'>{$url}</a></td>";
                echo "<td>{$uploader}</td>";
                echo "<td>{$stat}</td>";
                echo "</tr>";
            }

            ?>
        </table>
    </div>
</body>