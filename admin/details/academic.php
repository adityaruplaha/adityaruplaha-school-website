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
?>

<!DOCTYPE html>
<html lang='en'>

<head>
    <meta charset="utf-8">
    <title>XII Sc A - Student Details</title>
    <link rel='stylesheet' type='text/css' href='stylesheet.css' />
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

    $sql = "SELECT Name FROM info";
    $result = $conn->query($sql);
    if (!$result) {
        die("Failed to get names.");
    }
    ?>

    <h1 class='center'>XII Sc A - Student Details</h1>
    <hr />

    <div>
        <table>
            <tr>
                <th>Name</th>
                <th>Additional Subject</th>
                <th>Status (Class XI)</th>
            </tr>
            <?php
            while ($row = $result->fetch_assoc()) {
                $info = (new \ScA\Student\Student($row['Name']))->get_academic_info($classes);
                echo "<tr>";
                echo "<td>{$row['Name']}</td>";
                switch ($info['ExtraSub']) {
                    case "pe":
                        echo "<td>Physical Education</td>";
                        break;
                    case "bn":
                        echo "<td>Bengali</td>";
                        break;
                    case "hi":
                        echo "<td>Hindi</td>";
                        break;
                    default:
                        echo "<td></td>";
                        break;
                }
                switch ($info['Status']) {
                    case "Passed":
                        echo "<td class='green' align='center'>{$info['Status']}</td>";
                        break;
                    case "Retest":
                        echo "<td class='yellow' align='center'>{$info['Status']}</td>";
                        break;
                    case "Failed":
                        echo "<td class='red' align='center'>{$info['Status']}</td>";
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