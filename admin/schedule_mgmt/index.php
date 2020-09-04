<?php

require_once "../../login.php";

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
    header("Location: ../../?nauth");
    exit;
}

require_once "../../defs.php";

use const ScA\DB;
use const ScA\DB_HOST;
use const ScA\DB_PWD;
use const ScA\DB_USER;

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
    <title>XII Sc A - Control Center</title>
    <link rel='stylesheet' type='text/css' href='/sc_a/themes/dark/base.css' />
    <link rel='stylesheet' type='text/css' href='/sc_a/themes/dark/tables.css' />
    <link rel='stylesheet' type='text/css' href='/sc_a/themes/dark/slider.css' />
    <meta name="viewport" content="width=device-width, initial-scale=0.75">
    <script src='/sc_a/scripts/http_requests.js'>
    </script>
</head>

<body onload="autoload(0)">
    <h1 class='center'>XII Sc A - Control Center</h1>
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

    <div class='tab' id='stats'>
        <table class='unbordered bicolumn center'>
            <tr>
                <th>Member</th>
                <th>Telemetry Mode</th>
                <th>Can login?</th>
                <th>Can access resources?</th>
            </tr>
        </table>
    </div>
</body>

</html>