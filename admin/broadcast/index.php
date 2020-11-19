<?php
require_once "../../login.php";


use \ScA\Student\TGLogin\TGLogin;




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

$is_logged_in = ($s != NULL);

if (!$is_logged_in) {
    header("Location: ../../?nauth");
    exit;
}
?>

<!DOCTYPE html>
<html lang='en'>

<head>
    <title>Broadcast Message to Telegram</title>
    <script src='/sc_a/scripts/tab.js'>
    </script>
    <link rel='stylesheet' type='text/css' href='/sc_a/themes/dark/base.css' />
    <link rel='stylesheet' type='text/css' href='/sc_a/themes/dark/tables.css' />
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

        textarea {
            height: 200px;
            width: 520px;
        }
    </style>
</head>

<body onload="autoload(0)">
    <h1>Broadcast to Telegram</h1>
    <?php if (array_key_exists('done', $_GET)) {
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
    <table class='nav mediumfont'>
        <tr>
            <td onclick="show(this, 'msg')" class='tab_button'>Send a message.</td>
            <td onclick="show(this, 'file')" class='tab_button'>Send a file.</td>
        </tr>
    </table>
    <div class='tab' id='msg'>
        <form action='send.php' method='post'><textarea name='msg' placeholder="Enter message to broadcast." required></textarea><br /><br /><input name='name' placeholder="Enter your name here." required /><br /><br /><button type='submit'>Send</button></form>
    </div>
    <div class='tab' id='file'>
        <p>Please note this can take upto 10 minutes to finish,
            depending on file size and network quality.<br />Please keep this tab open for the transfer to finish. </p>
        <form action='sendfile.php' method='post' enctype="multipart/form-data"><input type="file" name="file" required /><br /><br /><textarea name='msg' placeholder="Enter caption (optional)."></textarea><br /><br /><input name='name' placeholder="Enter your name here." required /><br /><br /><button type='submit'>Send</button></form>
    </div>
    <script id='remove_get'>
        window.history.replaceState(null, '', window.location.href.split('?')[0]);
        document.getElementById('remove_get').remove();
    </script>
</body>

</html>
