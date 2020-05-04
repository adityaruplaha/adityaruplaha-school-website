<?php

$db_host = 'localhost';
$db_user = 'prog_access';
$db_pwd = '';

$database = 'school';
$table = 'xii_sc_a_resources';

$conn = new mysqli($db_host, $db_user, $db_pwd, $database);

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

$RES = array();
foreach ($SUBCODES as $sub => $v) {
    $result = $conn->query("SELECT COUNT(`Name`) FROM {$table} WHERE `Subject` = '{$sub}'");
    if (!$result) {
        die("Query to show fields from table failed.");
    }
    $RES[$sub] = $result->fetch_array(MYSQLI_NUM)[0][0];
}
?>

<html lang='en'>

<head>
    <meta charset="utf-8">
    <title>XII Sc A - Resources</title>
    <script src='script.js'>
    </script>
    <link rel='stylesheet' type='text/css' href='stylesheet.css' />
</head>

<body onload="clean()">

    <h1 align='center'>XII Sc A - Resources</h1>
    <hr />

    <div>
        <table class='nav'>
            <tr>
                <td>Subject</td>
                <td>Resources</td>
            </tr>
            <?php
            foreach ($SUBCODES as $k => $v) {
                $a = $RES[$k];
                echo "<tr>";
                echo "<td onclick=\"show('{$k}')\" class='button'>{$v}</td><td>{$a}</td>";
                echo "</tr>";
            }
            ?>
        </table>
        <br />
    </div>

    <?php

    foreach ($SUBCODES as $sub => $SUB) {

        // Query
        $result = $conn->query("SELECT `Name`, `Notes`, DATE_FORMAT(`GivenOn`, '%d %M %Y') 'GivenOn',
         `URL`, `Source` FROM {$table} WHERE `Subject` = '{$sub}' ORDER BY `{$table}`.`GivenOn` ASC");

        if (!$result) {
            die("Query to show fields from table failed.");
        }

        echo "
        <div class='tab' id='{$sub}'>
        <table>
            <tr>
                <th>Resource</th>
                <th>Given On</th>
                <th>File/URL</th>
                <th>Source</th>
                <th>Notes</th>
            </tr>";

        while ($resource = $result->fetch_assoc()) {
            echo "<tr>";

            echo "<td>" . $resource["Name"] . "</td>";

            echo ($d = $resource["GivenOn"]) ? "<td>" . $d . "</td>" : "<td></td>";
            echo ($l = $resource["URL"]) ? "<td><a href=\"" . $l . "\">Open</a></td>" : "<td></td>";
            echo ($s = $resource["Source"]) ? "<td>" . $s . "</td>" : "<td></td>";

            if ($r = $resource["Notes"]) {
                echo "<td class='button' onclick=\"alert(`" . htmlspecialchars($r) . "`);\">Click to view.</td>";
            } else {
                echo "<td></td>";
            }

            echo "</tr>";
        }

        echo "</table>
        <p>You are viewing Resources for {$SUB}.</p>
        </div>";
        $result->free();
    }

    ?>


    <footer align='center'>
        <hr />
        <br />
        <i>
            <?php
            date_default_timezone_set("Asia/Kolkata");
            echo "Report generated on " . date("d M Y h:i:sa") . " IST."
            ?>
            <br />
            <br />
            Click on the subject header to see resources from that subject.
        </i>
    </footer>
</body>

</html>