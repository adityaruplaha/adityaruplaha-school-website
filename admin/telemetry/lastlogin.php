<?php

require_once "../../login.php";
require_once "../../teacher/defs.php";

use \ScA\Student\TGLogin\TGLogin;
use \ScA\Teacher;

$is_teacher = Teacher\is_logged_in();

$s = TGLogin::from_cookie();
if ($s != NULL) {
    $s = new \ScA\Student\Student(NULL, $s->id);
    if (!$s->has_privileges("Admin")) {
        $s = NULL;
    }
}
$is_logged_in = ($s != NULL) || $is_teacher;

if (!$is_logged_in) {
    header("Location: ../../?nauth");
    exit;
}

require_once "../../defs.php";

use const ScA\DB;
use const ScA\DB_HOST;
use const ScA\DB_PWD;
use const ScA\DB_USER;

$table = 'last_logins';

$conn = new mysqli(DB_HOST, DB_USER, DB_PWD, DB);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

?>

<!DOCTYPE html>
<html lang='en'>

<head>
    <meta charset="utf-8">
    <title>XII Sc A - Last Logins</title>
    <script src='script.js'>
    </script>
    <link rel='stylesheet' type='text/css' href='/sc_a/themes/dark/base.css' />
    <link rel='stylesheet' type='text/css' href='/sc_a/themes/dark/tables.css' />
</head>

<body onload="clean()">

    <h1 class='center'>XII Sc A - Last Logins</h1>
    <hr />
    <p class='center'>
        <i>
            <?php
            date_default_timezone_set("Asia/Kolkata");
            echo "Report generated on " . date("d M Y h:i:sa") . " IST."
            ?>
        </i>
        <br /> This page is not covered by telemetry.
        <hr />
    </p>

    <?php
    // Query
    $result = $conn->query("SELECT * FROM {$table}");

    if (!$result) {
        die("Query to show fields from table failed.");
    }

    echo "
        <div class='tab' id='{$sub}'>
        <table class='semibordered center autowidth'>
            <tr>
                <th>Member</th>
                <th>Last Login</th>
            </tr>";

    while ($action = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $action["Member"] . "</td>";
        echo "<td>" . $action["LastLogin"] . "</td>";
        echo "</tr>";
    }

    echo "</table>";
    $result->free();


    ?>
</body>

</html>