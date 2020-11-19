<?php

require_once "../login.php";

use \ScA\Student\TGLogin\TGLogin;

$s = TGLogin::from_cookie();
if ($s != NULL) {
    $s = new \ScA\Student\Student(NULL, $s->id);
    if (!$s->has_privileges("Member")) {
        $s = NULL;
    }
}

if ($s != NULL) {
    $s->report_url_visit($_SERVER['PHP_SELF']);
}

$is_logged_in = ($s != NULL);

if (!$is_logged_in) {
    header("Location: ../?nauth");
    exit;
}


?>

<!DOCTYPE html>
<html lang='en'>

<head>
    <meta charset="utf-8">
    <title>XII Sc A - Comments</title>
    <link rel='stylesheet' type='text/css' href='/sc_a/themes/dark/base.css' />
</head>

<body>

    <h1 class='center'>XII Sc A - Comments</h1>
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
    <div style="width: 100%">
        <script async src="https://comments.app/js/widget.js?3" data-comments-app-website="n_vAYrFA" data-limit="5" data-dislikes="1" data-outlined="1" data-colorful="1" data-dark="1"></script>
    </div>
    <script id='remove_get'>
        window.history.replaceState(null, '', window.location.href.split('?')[0]);
        document.getElementById('remove_get').remove();
    </script>
</body>

</html>
