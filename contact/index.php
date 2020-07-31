<?php

require_once "../login.php";
require_once "../teacher/defs.php";

use \ScA\Student\TGLogin\TGLogin;
use \ScA\Teacher;

$s = TGLogin::from_cookie();

$is_logged_in = ($s != NULL) || (Teacher\is_logged_in());

if ($s != NULL) {
    $s = (new \ScA\Student\Student(NULL, $s->id));
    $s->report_url_visit($_SERVER['PHP_SELF']);
}

if (!$is_logged_in) {
    header("Location: ../?nauth");
    exit;
}

?>
<html lang='en'>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=0.7">
    <link rel='stylesheet' type='text/css' href='/sc_a/themes/dark/base.css' />
    <link rel='stylesheet' type='text/css' href='/sc_a/themes/dark/tables.css' />
    <style>
    img.icon {
        height: 30px;
        width: 30px;
        display: inline-block;
        vertical-align: middle;
    }
    </style>
</head>

<body>
    <h1 class=center>Contact Teachers</h1>
    <hr /><br />
    <div>
        <table class='smallfont autowidth semibordered'>
            <tr>
                <th>Name</th>
                <th colspan="3">Phone No.</th>
                <th>EMail</th>
            </tr>
            <?php

            require_once "../defs.php";

            use const ScA\DB;
            use const ScA\DB_HOST;
            use const ScA\DB_PWD;
            use const ScA\DB_USER;

            $conn = new mysqli(DB_HOST, DB_USER, DB_PWD, DB);

            // Check connection
            if ($conn->connect_error) {
                die("Connection failed: " . $conn->connect_error);
            }

            // Query
            $result = $conn->query("SELECT * FROM teachers WHERE Display = 1");
            if (!$result) {
                die("Query to show fields from table failed");
            }

            function get_phone_no_formatted($ph)
            {
                $ph_i = intval($ph);
                $wa = "<a class='img' href='https://wa.me/{$ph_i}'><img class='icon' src='img/wp.png'/></a>";
                $tel = "<a class='img' href='tel:{$ph}'><img class='icon' src='img/call.png'/></a>";
                return "<td>{$ph}</td><td>{$wa}</td><td>{$tel}</td>";
            }

            while ($r = $result->fetch_assoc()) {
                $rp = 1;
                if ($r["Mobile2"]) {
                    $rp = 2;
                }
                echo "<tr>";

                echo "<td rowspan={$rp}>";
                echo $r["Name"];
                echo "</td>";

                echo get_phone_no_formatted($r["Mobile1"]);

                echo "<td rowspan={$rp}>";
                echo "<a href='mailto:" . $r["EMail1"] . "'>" . $r["EMail1"] . "</a>";
                if ($r["EMail2"]) {
                    echo "<br/>" . "<a href='mailto:" . $r["EMail2"] . "'>" . $r["EMail2"] . "</a>";
                }
                echo "</td>";

                echo "</tr>";

                if ($r["Mobile2"]) {
                    echo "<tr>" . get_phone_no_formatted($r["Mobile2"]) . "</tr>";
                }
            }

            ?>
        </table>
    </div>
</body>

</html>