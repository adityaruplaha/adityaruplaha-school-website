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

if ($s != NULL) {
    $s->report_url_visit($_SERVER['PHP_SELF']);
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
    <title>Post Assignments/Resources</title>
    <script src='/sc_a/scripts/tab.js'>
    </script>
    <link rel='stylesheet' type='text/css' href='/sc_a/themes/dark/base.css' />
    <link rel='stylesheet' type='text/css' href='/sc_a/themes/dark/tables.css' />
    <link rel='stylesheet' type='text/css' href='/sc_a/themes/dark/select.css' />
    <link rel='stylesheet' type='text/css' href='/sc_a/themes/dark/tabs.css' />
    <style>
    form {
        text-align: center;
    }

    input,
    textarea {
        background-color: #222222;
        font-family: "Arial", "Courier", "Letter Gothic";
        color: #BBBBBB;
        border-radius: 10px;
        border: 2px solid;
        padding: 14px 25px;
        font-size: 23px;
    }

    ::-webkit-calendar-picker-indicator {
        background-color: #BBBBBB;
        padding: 2px;
        border-radius: 3px;
    }

    ::-webkit-calendar-picker-indicator:hover {
        background-color: #DDDDDD;
        transition: 0.4s;
    }

    textarea {
        height: 200px;
        width: 520px;
    }
    </style>
</head>

<body onload="autoload(0)">
    <h1>Post Assignments/Resources</h1>
    <?php if (array_key_exists('done', $_GET)) {
        if ($_GET['done']) {
            echo '<p class="green">Successfully posted.</p>';
        } else {
            echo "<p class='red'>Failed to post:";
            echo "<br/><br/>";
            echo $_GET["error"];
            echo "</p>";
        }
    }

    ?>
    <table class='nav smallfont'>
        <tr>
            <td onclick="show(this, 'assignments')" class='tab_button'>Assignments</td>
            <td onclick="show(this, 'resources')" class='tab_button'>Resources</td>
        </tr>
    </table>
    <div class='tab' id='assignments'>
        <form action='upload_assignment.php' method='post'>
            <table class='unbordered bicolumn mediumfont center autowidth'>
                <tr>
                    <td><label>Name:</label></td>
                    <td>
                        <input name='Name' placeholder="Name" required />
                    </td>
                </tr>
                <tr>
                    <td><label>Assigned on:</label></td>
                    <td>
                        <input type="date" name="AssignedOn" min="2020-03-01" required />
                    </td>
                </tr>
                <tr>
                    <td><label>Due on:</label></td>
                    <td>
                        <input type="date" name="DueOn" min="2020-03-01" />
                    </td>
                </tr>
                <tr>
                    <td><label>Subject:</label></td>
                    <td>
                        <select name="Subject" required>
                            <option>English</option>
                            <option>Mathematics</option>
                            <option>Computer Science</option>
                            <option>Physics</option>
                            <option>Chemistry</option>
                            <option>Physical Education</option>
                            <option>Bengali</option>
                            <option>Hindi</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td><label>URL:</label></td>
                    <td>
                        <textarea name='URL' placeholder="Enter URL."></textarea>
                    </td>
                </tr>
                <tr>
                    <td><label>Notes:</label></td>
                    <td>
                        <textarea name='Notes' placeholder="Notes"></textarea>
                    </td>
                </tr>
            </table>
            <button type='submit'>Upload Assignment</button>
        </form>
    </div>
    <div class='tab' id='resources'>
        <form action='upload_resource.php' method='post'>
            <table class='unbordered bicolumn mediumfont center autowidth'>
                <tr>
                    <td><label>Name:</label></td>
                    <td>
                        <input name='Name' placeholder="Name" required />
                    </td>
                </tr>
                <tr>
                    <td><label>Given on:</label></td>
                    <td>
                        <input type="date" name="GivenOn" min="2020-03-01" />
                    </td>
                </tr>
                <tr>
                    <td><label>Subject:</label></td>
                    <td>
                        <select name="Subject" required>
                            <option>English</option>
                            <option>Mathematics</option>
                            <option>Computer Science</option>
                            <option>Physics</option>
                            <option>Chemistry</option>
                            <option>Physical Education</option>
                            <option>Bengali</option>
                            <option>Hindi</option>
                            <option>Miscellaneous</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td><label>URL:</label></td>
                    <td>
                        <textarea name='URL' placeholder="Enter URL."></textarea>
                    </td>
                </tr>
                <tr>
                    <td><label>Notes:</label></td>
                    <td>
                        <textarea name='Notes' placeholder="Notes"></textarea>
                    </td>
                </tr>
                <tr>
                    <td><label>Source:</label></td>
                    <td>
                        <select name="Source" required>
                            <option></option>
                            <option>CBSE</option>
                            <option>Teacher</option>
                            <option>Community</option>
                        </select>
                    </td>
                </tr>
            </table>
            <button type='submit'>Upload Resource</button>
        </form>
    </div>
</body>

</html>