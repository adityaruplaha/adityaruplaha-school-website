<?php

require_once "../../login.php";
require_once "../../classes.php";

use ScA\Logging\ActionLogger;
use \ScA\Student\TGLogin\TGLogin;

$s = TGLogin::from_cookie();
if ($s != NULL) {
    $s = new \ScA\Student\Student(NULL, $s->id);
    if (!$s->has_privileges("Admin")) {
        $s = NULL;
    }
}
$is_logged_in = ($s != NULL);

if (!$is_logged_in) {
    header("Location: ../../?nauth");
    exit;
}

if (!isset($_GET["date"])) {
    die("Please provide a date.");
}

require_once "log.php";

$log = new ActionLogger('tcc');
$log->write("tcc: v2.1");

use const ScA\DB;
use const ScA\DB_HOST;
use const ScA\DB_PWD;
use const ScA\DB_USER;

$conn = new mysqli(DB_HOST, DB_USER, DB_PWD, DB);
$log->write("DB initialized.");

date_default_timezone_set("Asia/Kolkata");

$date = strtotime($_GET["date"]);

// 10 AM
$date += 10 * 3600;

///
/// Trello init stuff

require_once "../../trello/secrets.php";
$client = new \Trello\Client();
$client->authenticate(\ScA\Trello\KEY, \ScA\Trello\TOKEN, \Trello\Client::AUTH_URL_CLIENT_ID);
$mgr = new \Trello\Manager($client);
$boards = $mgr->getMember("me")->getBoards();

$board = NULL;
foreach ($boards as $test_board) {
    if ($test_board->getName() == "Bulletin Board: Private") {
        $board = $test_board;
        break;
    }
}
$clogs = $board->getList("CLogs");

/// Trello init stuff over
///

function post_card($name, $due)
{
    global $client, $clogs;
    return $client->api("cards")->create([
        'idList' => $clogs->getId(),
        'name' => $name,
        'due' => $due,
        'pos' => 'bottom'
    ]);
}

$log->write("Initialized Trello.");

$log->write("Working for: ". date("d F Y", $date));

$day = new \ScA\Classes\Day(date("Y-m-d", $date));

const SUBCODES = [
    "phy1" => "Physics: (Vol 1) ??",
    "phy2" => "Physics: (Vol 2) ??",
    "chem1" => "Chemistry: (Vol 1) ??",
    "chem2" => "Chemistry: (Vol 2) ??",
    "cs" => "CS: ??",
    "math" => "Maths: ??",
    "en" => "English: ??",
    "pe" => "PEd: ??",
    "bn" => "Bengali: ??",
    "hi" => "Hindi: ??"
];

$classes = $day->get_classes($conn);
$count = count($classes);

if (!$count) {
    $log->write("No classes scheduled.");
}

$done = 0;
foreach ($classes as $class) {
    $class_time = $class->timestamp;

    $name = date(SUBCODES[$class->subject] . ": ??");
    $due = date("c", $class_time);

    $log->write("Posting card: {$name} @ {$due}");
    $tcc = post_card($name, $due);
    $url = $tcc["shortUrl"];
    $log->write("Posted card: {$url}");

    if ($class->set_trello_url($url)) {
        $log->write("Updated DB successfully.");
        $done += 1;
    } else {
        $log->write("DB update failed.", "SQL Error [{$conn->errno}]: " . $conn->error);
        $log->write("You should probably manually delete the orphan card.");
    }
}
$log->write("Finished: ${done} of {$count} successfully done.");

$log->publish($conn);

echo "<pre>";
echo $log->read();
echo "</pre>";