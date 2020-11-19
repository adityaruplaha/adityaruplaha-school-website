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
$is_logged_in = ($s != NULL);

if (!$is_logged_in) {
    header("Location: ../../?nauth");
    exit;
}
?>

<?php

require_once $_SERVER['DOCUMENT_ROOT'] . '/telegram/BDMIOnlineClassesBot/defs.php';

use Longman\TelegramBot\Request;

if (array_key_exists('file', $_FILES) && array_key_exists('name', $_POST)) {
    $msg = $_POST['msg'];
    $n = $_POST['name'];

    $filename = basename($_FILES["file"]["name"]);
    $target_dir = $_SERVER['DOCUMENT_ROOT'] . "/uploads/";
    $target_file = $target_dir . $filename;

    // Check file size
    if ($_FILES["file"]["size"] > 20000000) {
        header("Location: index.php?done=0&error=File exceeds 20MB size limit.");
        exit;
    }
    if (file_exists($target_file)) {
        header("Location: index.php?done=0&error=File exists already.");
        exit;
    }

    $r = move_uploaded_file($_FILES["file"]["tmp_name"], $target_file);
    if (!$r) {
        header("Location: index.php?done=0&error=File upload failed.");
        error_log(print_r($r, true));
        exit;
    }

    $text = '';
    if (array_key_exists('msg', $_POST)) {
        $text = "{$msg}\n\n~ {$n}\n(This message was sent by a teacher.)";
    } else {
        $text = "~ {$n}\n(This message was sent by a teacher.)";
    }

    // Create Telegram API object
    $telegram = new Longman\TelegramBot\Telegram(BOT_API_KEY, BOT_USERNAME);
    $telegram->enableMySql(MYSQL_CREDENTIALS);

    $result = Request::sendDocument([
        'chat_id' => -1001214393687,
        'document' => Request::encodeFile($target_file),
        'caption' => $text,
        'disable_notification' => true
    ]);
    if ($result) {
        header("Location: index.php?done=1");
        exit;
    } else {
        header("Location: index.php?done=0&error=" . print_r($result, true));
        exit;
    }
}

header("Location: index.php");
exit;
