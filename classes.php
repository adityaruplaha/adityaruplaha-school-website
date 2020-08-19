<?php

namespace ScA\Classes;

date_default_timezone_set("Asia/Kolkata");

require_once "defs.php";
require_once 'trello/vendor/autoload.php';
require_once 'trello/secrets.php';

use const ScA\DB;
use const ScA\DB_HOST;
use const ScA\DB_PWD;
use const ScA\DB_USER;

const SUBCODES = array(
    "phy1" => "Physics (Saswati Sur)",
    "phy2" => "Physics (Debarati Pramanik)",
    "chem1" => "Chemistry (Soumi Karmakar)",
    "chem2" => "Chemistry (Nandita Dastidar)",
    "math" => "Mathematics (Rafiqul Amin)",
    "cs" => "Computer Science (Pintu Majumder)",
    "en" => "English (Molly Basu)",
    "pe0" => "Physical Education (Himadri Dutta)",
    "pe" => "Physical Education (Shiman Dey)",
    "bn" => "Bengali (Sharmistha Biswas)",
    "hi" => "Hindi (Seema Singh)"
);

const SCHEDULE_BEAUTY_MULTILINE = 0xADE0;
const SCHEDULE_BEAUTY_SINGLELINE = 0xADE1;
/**
 * @deprecated
 */
const SCHEDULE_BEAUTY_TABULATED = 0xADE2;
/**
 * @deprecated
 */
const SCHEDULE_BEAUTY_TGMSG_CLASSESON = 0xADE3;

class SchedClass
{
    public $timestamp;
    public $subject;
    public $trello;
    public $status;

    /**
     * Construct a new SchedClass.
     * 
     * @param int $timestamp UNIX timestamp indicating time.
     * @param string $subject Subject code.
     * @param string $trello Trello ShortURL.
     */
    public function __construct($timestamp, $subject, $trello = NULL, $status = NULL)
    {
        $this->timestamp = $timestamp;
        $this->subject = $subject;
        $this->trello = $trello;
        $this->status = $status;
        if ($trello == NULL) {
            $conn = new \mysqli(DB_HOST, DB_USER, DB_PWD, DB);
            $d = date("Y-m-d", $timestamp);
            $t = date("H:i:s", $timestamp);
            $r = $conn->query("SELECT Trello FROM classes WHERE `Date` = '{$d}' AND `Time` = '{$t}' AND `Subject` = '{$subject}'");
            if ($r) {
                $this->trello = $r->fetch_row()[0];
            }
            $r->free();
            $conn->close();
        }
        if ($status == NULL) {
            $conn = new \mysqli(DB_HOST, DB_USER, DB_PWD, DB);
            $d = date("Y-m-d", $timestamp);
            $t = date("H:i:s", $timestamp);
            $r = $conn->query("SELECT Status FROM classes WHERE `Date` = '{$d}' AND `Time` = '{$t}' AND `Subject` = '{$subject}'");
            if ($r) {
                $this->status = $r->fetch_row()[0];
            }
            $r->free();
            $conn->close();
        }
    }

    public function __toArray()
    {
        return [
            "Date" => date("Y-m-d", $this->timestamp),
            "Time" => date("H:i:s", $this->timestamp),
            "Subject" => $this->subject,
            "Trello" => $this->trello,
            "Status" => $this->status
        ];
    }

    /**
     * Get name of column for this SchedClass.
     * 
     * @param string $encloseby Optional. Delimiter to enclose by. Default: ` (tilde).
     * 
     * @return str
     */
    public function as_colname($encloseby = '`')
    {
        $conn = new \mysqli(DB_HOST, DB_USER, DB_PWD, DB);
        $date = date("Y-m-d", $this->timestamp);
        $time = date("H:i:s", $this->timestamp);
        $r = $conn->query("SELECT Time FROM classes WHERE `Date` = '{$date}' AND `Subject` = '{$this->subject}'");
        $suffix = "";
        $rows = $r->fetch_all();
        for ($i = 0; $i < count($rows); $i++) {
            if ($rows[$i][0] == $time) {
                if ($i) {
                    $suffix = '_ad' . $i;
                }
                break;
            }
        }
        $r->free();
        $conn->close();
        return $encloseby . $date . '_' . $this->subject . $suffix . $encloseby;
    }

    /**
     * Get Trello card object for this SchedClass.
     * 
     * @return \Trello\Api\Card
     */
    public function get_card()
    {
        $client = new \Trello\Client();
        $client->authenticate(\ScA\Trello\KEY, \ScA\Trello\TOKEN, \Trello\Client::AUTH_URL_CLIENT_ID);

        $link_arr = explode('/', $this->trello);
        $shortlink = $link_arr[count($link_arr) - 2];
        return $client->api('card')->show($shortlink);
    }

    /**
     * Get attendance data in the form {P (int), A (int), E (int), % (float b/w 0 and 1)}.
     * 
     * @param mysqli $conn Connection to use.
     *  
     * @return array Associative array 
     * 
     */
    public function get_attendance_data($conn)
    {
        switch ($this->status) {
            case "Attendance Missing":
                return "NO DATA";
            case "Skip Attendance":
                return "EXEMPTED";
            case "Cancelled":
                return "CANCELLED";
            default:
                break;
        }
        $col = $this->as_colname('`');
        $sql = "SELECT {$col}, COUNT(Name) FROM attendance GROUP BY {$col} ORDER BY {$col} DESC";
        $r2 = $conn->query($sql);
        if (!$r2) {
            die("Query to show fields from table failed. Error Code: E_A02.");
        }
        $rows = $r2->fetch_all(MYSQLI_NUM);
        $p = 0;
        $a = 0;
        $n = 0;
        foreach ($rows as list($state, $val)) {
            if (isset($state)) {
                if ($state) {
                    $p = intval($val);
                } else {
                    $a = intval($val);
                }
            } else {
                $n = intval($val);
            }
        }
        if ($p === 0 && $a === 0) {
            return "NO DATA";
        }
        $percentage = floatval($p) / (floatval($p) + floatval($a));
        $r2->free();
        return [
            "P" => $p,
            "A" => $a,
            "E" => $n,
            "%" => $percentage
        ];
    }

    /** 
     * Get beautified string representation  of the SchedClass.
     * 
     * @param SCHEDULE_BEAUTY_* $mode 
     * 
     * @return str
     */
    public function beautify($mode)
    {
        if ($mode == SCHEDULE_BEAUTY_MULTILINE) {
            return date('d M Y', $this->timestamp) . '<br/><br/>' . SUBCODES[$this->subject];
        } elseif ($mode == SCHEDULE_BEAUTY_SINGLELINE) {
            return date('d M Y', $this->timestamp) . ': ' . SUBCODES[$this->subject];
        } elseif ($mode == SCHEDULE_BEAUTY_TABULATED) {
            return "<td>" . SUBCODES[$this->subject] . "</td><td>" . date('h:i A', $this->timestamp) .
                "</td><td><a href=\"" . $this->trello . "\">" . $this->trello . "</a></td>";
        } elseif ($mode == SCHEDULE_BEAUTY_TGMSG_CLASSESON) {
            return "\u{2022} " . SUBCODES[$this->subject] . " @ " . date("h:i A\n   ", $this->timestamp) . $this->trello;
        } else {
            return NULL;
        }
    }

    /**
     * Construct a new SchedClass.
     * 
     * @param string $date String date representation.
     * @param string $time String time representation.
     * @param string $subject Subject code.
     * @param string $trello Trello ShortURL.
     */
    public static function from_strs($date, $time, $subject, $trello = NULL, $status = NULL)
    {
        return new SchedClass(strtotime($date . ' ' . $time), $subject, $trello, $status);
    }

    /**
     * Make a SchedClass object from an associative array as returned by mysqli_result::fetch_assoc().
     * Uses SchedClass::from_strs.
     * 
     * @param array $arr
     * 
     * @return SchedClass Newly constructed object.
     */
    public static function from_array($arr)
    {
        return SchedClass::from_strs($arr["Date"], $arr["Time"], $arr["Subject"], $arr["Trello"], $arr["Status"]);
    }

    /**
     * Get an array of SchedClass scheduled on date $d.
     * 
     * @param mysqli $conn Connection to use.
     * @param date $d Date to obtain classes from.
     * @param array $filter Optional. Subjects to filter. Default: All.
     * 
     * @return array Array of SchedClass.
     * 
     */
    public static function get_classes_on($conn, $d, $filter = array())
    {
        $d = date("Y-m-d", $d);
        $r = NULL;
        if ($filter) {
            $ar = [];
            foreach ($filter as $sub) {
                array_push($ar, "`Subject` = '{$sub}'");
            }
            $r = $conn->query("SELECT * FROM `classes` WHERE Date = '{$d}' AND (" . implode(" OR ", $ar) . ") ORDER BY `Date` ASC, `Time` ASC, `Subject` ASC");
        } else {
            $r = $conn->query("SELECT * FROM `classes` WHERE Date = '{$d}' ORDER BY `Date` ASC, `Time` ASC, `Subject` ASC");
        }
        return array_map(["ScA\Classes\SchedClass", "from_array"], $r->fetch_all(MYSQLI_ASSOC));
    }

    /**
     * Get an array of SchedClass scheduled between $from and $to, both inclusive.
     * 
     * @param mysqli $conn Connection to use.
     * @param date $from Date to obtain classes from.
     * @param date $to Date to obtain classes to.
     * @param array $filter Optional. Subjects to filter. Default: All.
     * 
     * @return array Array of SchedClass.
     * 
     */
    public static function get_classes_between($conn, $from, $to, $filter = array())
    {
        $from = date("Y-m-d", $from);
        $to = date("Y-m-d", $to);
        $r = NULL;
        if ($filter) {
            $ar = [];
            foreach ($filter as $sub) {
                array_push($ar, "`Subject` = '{$sub}'");
            }
            $r = $conn->query("SELECT * FROM `classes` WHERE Date BETWEEN '{$from}' AND '{$to}' AND (" . implode(" OR ", $ar) . ") ORDER BY `Date` ASC, `Time` ASC, `Subject` ASC");
        } else {
            $r = $conn->query("SELECT * FROM `classes` WHERE Date BETWEEN '{$from}' AND '{$to}' ORDER BY `Date` ASC, `Time` ASC, `Subject` ASC");
        }
        return array_map(["ScA\Classes\SchedClass", "from_array"], $r->fetch_all(MYSQLI_ASSOC));
    }

    /**
     * Get an array of SchedClass scheduled in the last $lim_days days + today.
     * 
     * @param mysqli $conn Connection to use.
     * @param int $lim_days Optional. Number of previous days to include from today. Default: 0 = only today.
     * @param array $filter Optional. Subjects to filter. Default: All.
     * 
     * @return array Array of SchedClass.
     * 
     */
    public static function get_last_classes($conn, $lim_days = 0, $filter = array())
    {
        $d = time() - $lim_days * 86400;
        return SchedClass::get_classes_between($conn, $d, time(), $filter);
    }

    /**
     * Get an array of SchedClass scheduled in the next $lim_days days + today.
     * 
     * @param mysqli $conn Connection to use.
     * @param int $lim_days Optional. Number of next days to include from today. Default: 0 = only today.
     * @param array $filter Optional. Subjects to filter. Default: All.
     * 
     * @return array Array of SchedClass.
     * 
     */
    public static function get_next_classes($conn, $lim_days = 0, $filter = array())
    {
        $d = time() + $lim_days * 86400;
        return SchedClass::get_classes_between($conn, time(), $d, $filter);
    }

    /**
     * Get an array of SchedClass.
     * 
     * @param mysqli $conn Connection to use.
     * @param date $d Date to obtain classes from.
     * @param array $filter Optional. Subjects to filter. Default: All.
     * 
     * @return array Array of SchedClass.
     * 
     */
    public static function get_classes_from($conn, $d, $filter = array())
    {
        return SchedClass::get_classes_between($conn, $d, time(), $filter);
    }
}


class Day
{
    public $date;
    public $trello;

    public function __construct($date, $trello = NULL)
    {
        $this->date = $date;
        $this->trello = $trello;
        if ($trello == NULL) {
            $conn = new \mysqli(DB_HOST, DB_USER, DB_PWD, DB);
            $d = date("Y-m-d", $date);
            $r = $conn->query("SELECT PrivateTrello FROM days WHERE `Date` = '{$d}'");
            if ($r) {
                $this->trello = $r->fetch_row()[0];
            }
            $r->free();
            $conn->close();
        }
    }

    public function get_classes($conn, $filter = [])
    {
        return SchedClass::get_classes_on($conn, $this->date, $filter);
    }

    public function get_upload_data($conn)
    {
        $d = date("Y-m-d", $this->date);
        $r = $conn->query("SELECT UploadedBy, Status FROM days WHERE `Date` = '{$d}'");
        $row = $r->fetch_assoc();
        $r->free();
        return $row;
    }

    /**
     * Make a Day object from an associative array as returned by mysqli_result::fetch_assoc().
     * 
     * @param array $arr
     * 
     * @return Day Newly constructed object.
     */
    public static function from_array($arr)
    {
        if (!$arr) {
            return NULL;
        }
        return new Day(strtotime($arr["Date"]), $arr["PrivateTrello"]);
    }

    /**
     * Find last day for which schedule is provided.
     * 
     * @param mysqli $conn Connection used to query databases.
     * 
     * @return int UNIX timestamp for the last date.
     */
    public static function last_day($conn)
    {
        $r1 = $conn->query("SELECT MAX(`Date`) FROM days");
        $d1 = strtotime($r1->fetch_row()[0]);
        $r1->free();
        $r2 = $conn->query("SELECT MAX(`Date`) FROM classes");
        $d2 = strtotime($r2->fetch_row()[0]);
        $r2->free();
        return min($d1, $d2);
    }
}