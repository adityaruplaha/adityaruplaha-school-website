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

require_once "../../defs.php";

use const ScA\DB;
use const ScA\DB_HOST;
use const ScA\DB_PWD;
use const ScA\DB_USER;

$table = 'notes';

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
    <title>XII Sc A - Admin Notes</title>
    <script src='/sc_a/scripts/paginate.js'>
    </script>
    <script src='/sc_a/scripts/post.js'>
    </script>
    <script src='/sc_a/scripts/autosize.min.js'>
    </script>
    <script src='script.js'>
    </script>
    <link rel='stylesheet' type='text/css' href='/sc_a/themes/dark/base.css' />
    <link rel='stylesheet' type='text/css' href='/sc_a/themes/dark/tables.css' />
    <link rel='stylesheet' type='text/css' href='/sc_a/themes/dark/input.css' />
    <link rel='stylesheet' type='text/css' href='/sc_a/themes/dark/select.css' />
    <link rel='stylesheet' type='text/css' href='/sc_a/themes/dark/cards.css' />
    <link rel='stylesheet' type='text/css' href='/sc_a/themes/dark/icons.css' />
    <link rel='stylesheet' type='text/css' href='stylesheet.css' />
    <script src="https://code.iconify.design/1/1.0.7/iconify.min.js"></script>
</head>

<body>

    <h1 class='center'>XII Sc A - Admin Notes</h1>
    <?php if (array_key_exists('done', $_GET)) {
        if ($_GET['done']) {
            echo '<p class="green">Operation successful.</p>';
        } else {
            echo "<p class='red'>Failed to post:";
            echo "<br/><br/>";
            echo $_GET["error"];
            echo "</p>";
        }
    }
    ?>
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
    <div class="cardholder">
        <?php
        // Query
        $result = $conn->query("SELECT * FROM {$table} ORDER BY `{$table}`.`PostedOn` DESC");

        if (!$result) {
            die("Query to show fields from table failed.");
        }

        function prep_content($str)
        {
            $str = htmlspecialchars($str);

            // Replace URLs 
            $regex = '@(http)?(s)?(://)?(([a-zA-Z])([-\w]+\.)+([^\s\.]+[^\s]*)+[^,.\s])@';
            $str = preg_replace($regex, '<a href="http$2://$4" target="_blank" title="$0" class="compact">$0</a>', $str);
            $str = str_replace("\n", "<br/>", $str);

            return $str;
        }

        while ($note = $result->fetch_assoc()) {
            if (!$s->has_privileges($note['MinPrivilegeLevel'])) {
                continue;
            }
            echo "<div class='card'>";
            echo "<span class='yellow'>";
            echo "{$note['PostedBy']} on {$note['PostedOn']}";
            if ($note['PostedOn'] != $note['LastEditedOn']) {
                echo " (last edited on {$note['LastEditedOn']})";
            }
            echo "</span>";
            echo "<div>";
            echo prep_content($note['Content']);
            echo "</div>";
            echo "<span class='green'>";
            echo "Visible to {$note['MinPrivilegeLevel']} (+).";
            echo "</span>";
            if ($s->name == $note['PostedBy']) {
                echo "<span class='iconholders'>";
                echo "<span class='iconify yellow'
                data-icon='typcn-edit' data-inline='false'
                onclick='edit(this, {$note['NoteID']})'>
                </span>";
                echo "<span class='iconify red'
                data-icon='mdi-trash-can' data-inline='false'
                onclick='delete_note({$note['NoteID']});'>
                </span>";
                echo "</span>";
            }
            echo "</div>";
        }
        $result->free();
        ?>
    </div>
    <div class='footercard'>
        <form action='post_note.php' method='POST' class='center'>
            <div class='content'>
                <textarea name='Content' placeholder="Note" required></textarea>
            </div>
            <div>
                <select name="MinPrivilegeLevel" required>
                    <option>Admin</option>
                    <?php
                    if ($s->has_privileges("Super Admin")) {
                        echo "<option>Super Admin</option>";
                    }
                    ?>
                </select>
                &nbsp;&nbsp;&nbsp;
                <button type='submit'>Post Note</button>
            </div>
        </form>
    </div>
    <script id='remove_get'>
    window.history.replaceState(null, '', window.location.href.split('?')[0]);
    document.getElementById('remove_get').remove();
    </script>
</body>

</html>