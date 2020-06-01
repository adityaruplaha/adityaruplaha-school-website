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
$is_logged_in = ($s != NULL) || $is_teacher;

if (!$is_logged_in) {
    header("Location: ../../?nauth");
    exit;
}
?>

<?php

require_once $_SERVER['DOCUMENT_ROOT'] . '/telegram/BDMIOnlineClassesBot/defs.php';

use Longman\TelegramBot\Request;

if (array_key_exists('msg', $_POST) && array_key_exists('name', $_POST)) {
    $msg = $_POST['msg'];
    $n = $_POST['name'];

    // Create Telegram API object
    $telegram = new Longman\TelegramBot\Telegram(BOT_API_KEY, BOT_USERNAME);
    $telegram->enableMySql(MYSQL_CREDENTIALS);

    $result = Request::sendMessage([
        'chat_id' => -1001214393687,
        'text' => "{$msg}\n\n~ {$n}\n(This message was sent by a teacher.)"
    ]);
    if ($result) {
        header("Location: /sc_a/teacher/broadcast/?done=1");
        exit;
    } else {
        header("Location: /sc_a/teacher/broadcast/?done=0&error=" . print_r($result, true));
        exit;
    }
}

header("Location: /sc_a/teacher/broadcast/");
exit;
