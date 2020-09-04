<?php

require_once "../../login.php";

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

$log = new ActionLogger('pcc');
$log->write("pcc: v2.0");

use const ScA\DB;
use const ScA\DB_HOST;
use const ScA\DB_PWD;
use const ScA\DB_USER;

$conn = new mysqli(DB_HOST, DB_USER, DB_PWD, DB);
$log->write("DB initialized.");

date_default_timezone_set("Asia/Kolkata");

$date = strtotime($_GET["date"]);
if ($date <= $last = \ScA\Classes\Day::last_day($conn)) {
    die("Invalid timestamp " . date("c", $date) . ". Give a date atleast after " . date("d F Y", $last) . ".");
}

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

$name = date("d F Y", $date);
$due = date("c", $date);

$log->write("Posting card: {$name} @ {$due}");
$pcc = post_card($name, $due);
$url = $pcc["shortUrl"];
$log->write("Posted card: {$url}");

$date_sql = date("Y-m-d", $date);
$sql = "INSERT INTO days VALUES ('{$date_sql}', '{$url}', NULL, 'Not Due')";

if ($conn->query($sql)) {
    $log->write("Updated DB successfully: " . $sql);
} else {
    $log->write("DB update failed: " . $sql, "SQL Error [{$conn->errno}]: " . $conn->error);
    $log->write("You should probably manually delete the orphan card.");
}

$log->publish($conn);

echo "<pre>";
echo $log->read();
echo "</pre>";