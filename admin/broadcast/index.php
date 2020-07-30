<?php
require_once "../../login.php";
require_once "../../teacher/defs.php";

use \ScA\Student\TGLogin\TGLogin;
use \ScA\Teacher;

$is_teacher = Teacher\is_logged_in();

$s = TGLogin::from_cookie();
if ($s != NULL) {
    $s = new \ScA\Student\Student(NULL, $s->id);
    if (!$s->has_privileges("Super Admin")) {
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
    <title>Broadcast Message to Telegram</title>
    <link rel='stylesheet' type='text/css' href='stylesheet.css' />
</head>

<body>
    <h1>Broadcast to Telegram</h1>
    <?php
    if (array_key_exists('done', $_GET)) {
        if ($_GET['done']) {
            echo '<p class="green">Successfully posted message.</p>';
        } else {
            echo "<p class='red'>Failed to post message:";
            echo "<br/><br/>";
            echo $_GET["error"];
            echo "</p>";
        }
    }
    ?>
    <hr />
    <div>
        <h2>Send Message</h2>
        <form action='send.php' method='post'>
            <textarea name='msg' placeholder="Enter message to broadcast." required></textarea>
            <br /><br />
            <input name='name' placeholder="Enter your name here." required />
            <br /><br />
            <button type='submit'>Send</button>
        </form>
    </div>
    <hr />
    <div>
        <h2>Send File</h2>
        <p>
            Please note this can take upto 10 minutes to finish, depending on file size and network quality.<br />
            Please keep this tab open for the transfer to finish.
        </p>
        <form action='sendfile.php' method='post' enctype="multipart/form-data">
            <input type="file" name="file" required />
            <br /><br />
            <textarea name='msg' placeholder="Enter caption (optional)."></textarea>
            <br /><br />
            <input name='name' placeholder="Enter your name here." required />
            <br /><br />
            <button type='submit'>Send</button>
        </form>
    </div>
</body>

</html>