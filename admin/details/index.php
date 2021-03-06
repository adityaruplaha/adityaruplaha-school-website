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

if ($s != NULL) {
    $s->report_url_visit($_SERVER['PHP_SELF']);
}

$is_logged_in = ($s != NULL);

if (!$is_logged_in) {
    header("Location: ../../?nauth");
    exit;
}
?>

<!DOCTYPE html>
<html lang='en'>

<head>
    <meta charset="utf-8">
    <title>XII Sc A - Student Details</title>
    <link rel='stylesheet' type='text/css' href='/sc_a/themes/dark/base.css' />
    <link rel='stylesheet' type='text/css' href='/sc_a/themes/dark/tables.css' />
</head>

<body>
    <?php
    require_once "../../defs.php";

    use const ScA\DB;
    use const ScA\DB_HOST;
    use const ScA\DB_PWD;
    use const ScA\DB_USER;

    $conn = new mysqli(DB_HOST, DB_USER, DB_PWD, DB);;

    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $sql = "SELECT Name FROM info ORDER BY Name";
    $result = $conn->query($sql);
    if (!$result) {
        die("Failed to get names.");
    }
    ?>

    <h1 class='center'>XII Sc A - Student Details</h1>
    <hr />

    <div>
        <table class="semibordered autowidth">
            <tr>
                <th>Name</th>
                <th>Gender</th>
                <th>Religion</th>
                <th>Caste</th>
                <th>Single girl child?</th>
                <th>Email</th>
                <th colspan="2">Mobile</th>
                <th>Subjects</th>
                <th>Status (Class XI)</th>
            </tr>
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

            while ($row = $result->fetch_assoc()) {
                $academic_info = (new \ScA\Student\Student($row['Name']))->get_academic_info($classes);
                $basic_info = (new \ScA\Student\Student($row['Name']))->get_basic_info($classes);
                $contact = (new \ScA\Student\Student($row['Name']))->get_contact_info($classes);
                echo "<tr>";
                echo "<td>{$row['Name']}</td>";
                echo "<td>{$basic_info['Gender']}</td>";
                echo "<td>{$basic_info['Religion']}</td>";
                echo "<td>{$basic_info['Caste']}</td>";
                echo "<td class='center'>{$basic_info['SingleGirlChild']}</td>";
                echo "<td>{$contact['EMail']}</td>";
                echo "<td>{$contact['Mobile']}</td>";
                echo "<td>{$contact['Mobile2']}</td>";
                $subs = render_subs($academic_info['Subjects']);
                echo "<td>{$subs}</td>";
                switch ($academic_info['Status']) {
                    case "Passed":
                        echo "<td class='green center'>{$academic_info['Status']}</td>";
                        break;
                    case "Retest":
                        echo "<td class='yellow center'>{$academic_info['Status']}</td>";
                        break;
                    case "Failed":
                        echo "<td class='red center'>{$academic_info['Status']}</td>";
                        break;
                    default:
                        echo "<td></td>";
                        break;
                }
                echo "</tr>";
            }
            $result->free();
            $conn->close();
            ?>
        </table>
    </div>
</body>

</html>
