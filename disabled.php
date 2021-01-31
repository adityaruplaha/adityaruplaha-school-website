<?php

require_once "login.php";
require_once "student.php";


use \ScA\Student\TGLogin\TGLogin;


$s = TGLogin::from_cookie();

$is_logged_in = ($s != NULL);

if ($s != NULL) {
    $s = (new \ScA\Student\Student(NULL, $s->id));
    $s->report_url_visit($_SERVER['PHP_SELF']);
}

if (!$is_logged_in) {
    header("Location: ../?nauth");
    exit;
}

?>
<!DOCTYPE html>
<html lang='en'>

<head>
    <title>XII Sc A - Disabled</title>
    <link rel='stylesheet' type='text/css' href='/sc_a/themes/dark/base.css' />
    <link rel='stylesheet' type='text/css' href='stylesheet.css' />
    <meta name="viewport" content="width=device-width, initial-scale=0.8">
</head>

<body>
    <h1>XII Sc A - Disabled</h1>
    <hr />
    <div>
    <p class="red"><i>This functionality has been disabled or is currently unavailable.</i></p>
    <p>You were attempting to open: <code>
        <?php 
        echo $_GET["from"];
        ?>
    </code>.</p>
    </div>
</body>

</html>