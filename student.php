<?php

namespace ScA\Student;

require_once "defs.php";
require_once "classes.php";

use const \ScA\DB;
use const \ScA\DB_HOST;
use const \ScA\DB_PWD;
use const \ScA\DB_USER;

use Exception;
use ScA\Classes\SchedClass;

const TELEMETRY_ENUM = [
    'LOGIN',
    'LOGOUT',
    'URLVISIT',
    'CBSEINFO_MANUALCONFIRM',
    'BOT_COMMAND',
    'PROFILE_UPDATE'
];
class Privilege
{
    private const LOOKUP = [
        "Basic",
        "Member",
        "Contributor",
        "Admin",
        "Super Admin"
    ];

    public $lv;

    public function __construct($str)
    {
        if (!in_array($str, self::LOOKUP)) {
            throw "Invalid Privilege Level.";
        }
        $this->lv = $str;
    }

    public function get_int()
    {
        return array_flip(self::LOOKUP)[$this->lv];
    }
}

function is_valid_telemetry($str)
{
    return in_array($str, TELEMETRY_ENUM);
}
class Student
{
    public $name;
    public $tgid;

    /**
     * Is this student real?
     * 
     * @var bool
     */
    public $is_valid;

    /**
     * Can the student login?
     * 
     * @var bool|null NULL when `is_valid` is false.
     */
    public $can_login;

    public function __construct($name = NULL, $tgid = NULL)
    {
        if ($name == NULL && $tgid == NULL) {
            throw new Exception("Atleast 1 parameter must be supplied.");
        }
        $this->name = $name;
        $this->tgid = $tgid;
        $this->check();
    }

    public function report_telemetry(string $action, array $extradata = NULL)
    {
        if (!is_valid_telemetry($action)) {
            error_log("Invalid telemetry action.");
            return false;
        }
        if ($this->get_telemetry_privacy() >= 2) {
            return true;
        }
        $conn = new \mysqli(DB_HOST, DB_USER, DB_PWD, DB);
        $json = $conn->real_escape_string(json_encode($extradata, JSON_HEX_APOS));
        $ip = $_SERVER['REMOTE_ADDR'];
        $conn->query("INSERT INTO telemetry VALUES (NULL, '{$this->name}', '{$ip}', '{$action}', '{$json}')");
        $b = !(bool) $conn->error;
        if (!$b) {
            error_log("Telemetry logging failed: " . $conn->error);
        }
        $conn->close();
        return $b;
    }

    public function report_url_visit(string $url)
    {
        switch ($this->get_telemetry_privacy()) {
            case 2:
                return true;
            case 1:
                return $this->report_telemetry("URLVISIT", ["url" => "REDACTED"]);
            default:
                return $this->report_telemetry("URLVISIT", ["url" => $url]);
        }
    }

    public function report_bot_command(string $command, array $args)
    {
        switch ($this->get_telemetry_privacy()) {
            case 2:
                return true;
            case 1:
                return $this->report_telemetry("BOT_COMMAND", ["command" => $command, "args" => "REDACTED"]);
            default:
                return $this->report_telemetry("BOT_COMMAND", ["command" => $command, "args" => $args]);
        }
    }

    public function report_profile_update(array $old, array $new)
    {
        switch ($this->get_telemetry_privacy()) {
            case 2:
                return true;
            default:
                return $this->report_telemetry("PROFILE_UPDATE", ["old" => $old, "new" => $new]);
        }
    }

    public function get_theme()
    {
        return "dark";
    }

    /**
     * Get privacy level of telemetry.
     * 
     * 0 => Record everything.
     * 1 => Redact URLs and arguments to bot commands.
     * 2 => Incognito mode.
     * 
     * @return int
     */
    public function get_telemetry_privacy(): int
    {
        $conn = new \mysqli(DB_HOST, DB_USER, DB_PWD, DB);
        $r = $conn->query("SELECT TelemetryPrivacy FROM accounts WHERE `Name` = '{$this->name}'");
        $row = $r->fetch_row();
        $r->free();
        $conn->close();
        return intval($row[0]);
    }

    /**
     * Get resource access blocking.
     * 
     * 0 => Allow.
     * 1 => Deny.
     * 
     * @return int
     */
    public function get_block_resource_access(): int
    {
        $conn = new \mysqli(DB_HOST, DB_USER, DB_PWD, DB);
        $r = $conn->query("SELECT BlockResourceAccess FROM accounts WHERE `Name` = '{$this->name}'");
        $row = $r->fetch_row();
        $r->free();
        $conn->close();
        return intval($row[0]);
    }

    /**
     * Set privacy level of telemetry.
     * 
     * 0 => Record everything.
     * 1 => Redact URLs and arguments to bot commands.
     * 2 => Incognito mode.
     * 
     * @param $mode int
     * 
     * @return bool
     */
    public function set_telemetry_privacy(int $mode): bool
    {
        $conn = new \mysqli(DB_HOST, DB_USER, DB_PWD, DB);
        $r = $conn->query("UPDATE accounts SET TelemetryPrivacy = {$mode} WHERE `Name` = '{$this->name}'");
        $b = (bool) $conn->error;
        $conn->close();
        return !$b;
    }

    /**
     * Set login blocking.
     * 
     * 0 => Allow.
     * 1 => Block.
     * 
     * @param $mode int
     * 
     * @return bool Success/Failure
     */
    public function set_block_login(int $mode): bool
    {
        $conn = new \mysqli(DB_HOST, DB_USER, DB_PWD, DB);
        $r = $conn->query("UPDATE accounts SET BlockLogin = {$mode} WHERE `Name` = '{$this->name}'");
        $b = (bool) $conn->error;
        $conn->close();
        return !$b;
    }

    /**
     * Set resource access blocking.
     * 
     * 0 => Allow.
     * 1 => Block.
     * 
     * @param $mode int
     * 
     * @return bool Success/Failure
     */
    public function set_block_resource_access(int $mode): bool
    {
        $conn = new \mysqli(DB_HOST, DB_USER, DB_PWD, DB);
        $r = $conn->query("UPDATE accounts SET BlockResourceAccess = {$mode} WHERE `Name` = '{$this->name}'");
        $b = (bool) $conn->error;
        $conn->close();
        return !$b;
    }

    /**
     * Get attendance stats in the form {Name, Attendance % (float b/w 0 and 1), P (int), A (int), Total (int)}.
     * 
     * @param array $classes Array of `SchedClass` objects.
     *  
     * @return array Associative array 
     * 
     */
    public function get_attendance_summary($classes = [])
    {
        $conn = new \mysqli(DB_HOST, DB_USER, DB_PWD, DB);
        $r = NULL;
        if (!$classes) {
            $classes = SchedClass::get_classes_from($conn, strtotime("2020-04-03"));
        }
        $cols = [];
        foreach ($classes as $class) {
            if ($class->status == 'All OK') {
                array_push($cols, $class->as_colname('`'));
            }
        }
        $s = implode(', ', $cols);
        $sql = "SELECT {$s} FROM attendance WHERE `Name` = '{$this->name}'";
        $r = $conn->query($sql);
        if (!$r) {
            error_log("SQL Error [{$conn->errno}]: {$sql} gives {$conn->error}.");
        }
        $row = $r->fetch_assoc();
        $r->free();
        $conn->close();

        $p = 0;
        $net = 0;
        $result = array();

        foreach ($row as $key => $value) {
            if (!strpos($key, '_')) {
                continue;
            }
            if ($value != NULL) {
                $net += 1;
                $p += floatval($value);
            }
        }

        $result["Name"] = $this->name;
        $result['Attendance %'] = $p / $net;
        $result['P'] = $p;
        $result['A'] = $net - $p;
        $result['Total'] = $net;

        return $result;
    }

    /**
     * Get attendance data in the form {Object<SchedClass> => bool}.
     * 
     * @param array $classes Array of `SchedClass` objects.
     *  
     * @return array Associative array 
     * 
     */
    public function get_attendance_data($classes = [])
    {
        $conn = new \mysqli(DB_HOST, DB_USER, DB_PWD, DB);
        $r = NULL;
        if (!$classes) {
            $classes = SchedClass::get_classes_from($conn, strtotime("2020-04-03"));
        }
        $cols = [];
        foreach ($classes as $class) {
            $cols[$class->as_colname('`')] = $class;
        }
        $s = implode(', ', array_keys($cols));
        $sql = "SELECT {$s} FROM attendance WHERE `Name` = '{$this->name}'";
        $r = $conn->query($sql);
        if (!$r) {
            error_log("SQL Error [{$conn->errno}]: {$sql} gives {$conn->error}.");
            return NULL;
        }
        $row = $r->fetch_assoc();
        $r->free();
        $conn->close();

        $result = array_map(null, $cols, $row);
        return $result;
    }

    /**
     * Get info in the form `{Name, Gender, Religion, Caste, SingleGirlChild}`.
     *  
     * @return array|null Associative array 
     * 
     */
    public function get_basic_info()
    {
        $conn = new \mysqli(DB_HOST, DB_USER, DB_PWD, DB);
        $sql = "SELECT * FROM info WHERE `Name` = '{$this->name}'";
        $r = $conn->query($sql);
        if (!$r) {
            error_log("SQL Error [{$conn->errno}]: {$sql} gives {$conn->error}.");
            return NULL;
        }
        $row = $r->fetch_assoc();
        $r->free();
        $conn->close();
        return $row;
    }

    /**
     * Get games played.
     *  
     * @return string 
     * 
     */
    public function get_games()
    {
        $conn = new \mysqli(DB_HOST, DB_USER, DB_PWD, DB);
        $sql = "SELECT * FROM games WHERE `Name` = '{$this->name}'";
        $r = $conn->query($sql);
        if (!$r) {
            error_log("SQL Error [{$conn->errno}]: {$sql} gives {$conn->error}.");
            return NULL;
        }
        $row = $r->fetch_assoc();
        $r->free();
        $conn->close();
        return $row['Games'];
    }


    /**
     * Get info in the form `{Name, ExtraSub, Status}`.
     *  
     * @return array|null Associative array 
     * 
     */
    public function get_academic_info()
    {
        $conn = new \mysqli(DB_HOST, DB_USER, DB_PWD, DB);
        $sql = "SELECT Name, ExtraSub, Status FROM academic WHERE `Name` = '{$this->name}'";
        $r = $conn->query($sql);
        if (!$r) {
            error_log("SQL Error [{$conn->errno}]: {$sql} gives {$conn->error}.");
            return NULL;
        }
        $row = $r->fetch_assoc();
        $r->free();
        $conn->close();
        return $row;
    }

    /**
     * Get info in the form {Name, EMail, Mobile, Mobile2}.
     *  
     * @return array|null Associative array 
     * 
     */
    public function get_contact_info()
    {
        $conn = new \mysqli(DB_HOST, DB_USER, DB_PWD, DB);
        $sql = "SELECT Name, EMail, Mobile, Mobile2 FROM contact WHERE `Name` = '{$this->name}'";
        $r = $conn->query($sql);
        if (!$r) {
            error_log("SQL Error [{$conn->errno}]: {$sql} gives {$conn->error}.");
            return NULL;
        }
        $row = $r->fetch_assoc();
        $r->free();
        $conn->close();
        return $row;
    }

    /**
     * Set info in the form {EMail, Mobile, Mobile2}.
     *  
     * @param array|null $contact Associative array 
     * 
     */
    public function set_contact_info($contact)
    {
        $c_old = $this->get_contact_info();
        $conn = new \mysqli(DB_HOST, DB_USER, DB_PWD, DB);
        $r = NULL;
        if ($contact["Mobile2"]) {
            $r = $conn->query("UPDATE contact SET EMail = '{$contact['EMail']}', Mobile = '{$contact['Mobile']}', Mobile2  = '{$contact['Mobile2']}' WHERE `Name` = '{$this->name}'");
        } else {
            $r = $conn->query("UPDATE contact SET EMail = '{$contact['EMail']}', Mobile = '{$contact['Mobile']}', Mobile2  = NULL WHERE `Name` = '{$this->name}'");
        }
        $b = !(bool) $conn->error;
        if ($b) {
            if (!$this->report_profile_update($c_old, $contact)) {
                error_log("set_contact_info: Telemetry logging failed.");
            }
        } else {
            error_log("set_contact_info: UPDATE failed: " . $conn->error);
        }
        $conn->close();
        return $b;
    }

    /**
     * Get info in the form {Name, EMail, EMail_verified, Mobile, Mobile_verified, ManualConfirm} from CBSE listing.
     *  
     * @return array|null Associative array 
     * 
     */
    public function get_contact_cbse()
    {
        $conn = new \mysqli(DB_HOST, DB_USER, DB_PWD, DB);
        $r = $conn->query("SELECT * FROM contact_cbse WHERE `Name` = '{$this->name}'");
        $row = $r->fetch_assoc();
        $r->free();
        if ($row["ManualConfirm"]) {
            $row['EMail_verified'] = NULL;
            $row['Mobile_verified'] = NULL;
            $conn->close();
            return $row;
        }
        $r = $conn->query("SELECT * FROM contact WHERE `Name` = '{$this->name}'");
        $row2 = $r->fetch_assoc();
        $r->free();
        $conn->close();
        $row['EMail_verified'] = ($row["EMail"] == $row2["EMail"]);
        $row['Mobile_verified'] = (($row["Mobile"] == $row2["Mobile"]) || ($row["Mobile"] == $row2["Mobile2"]));
        return $row;
    }

    /**
     * Set CBSE contact ManualConfirm status.
     *  
     * @return bool 
     * 
     */
    public function set_manual_confirm_contact_cbse($manual_confirm)
    {
        $conn = new \mysqli(DB_HOST, DB_USER, DB_PWD, DB);
        $r = $conn->query("UPDATE contact_cbse SET `ManualConfirm` = {$manual_confirm} WHERE `Name` = '{$this->name}'");
        $conn->close();
        $this->report_telemetry("CBSEINFO_MANUALCONFIRM", ["status" => $manual_confirm]);
        return (bool) $r;
    }

    /**
     * Get info in the form {Date, PrivateTrello, Status}.
     *  
     * @return array|null Associative array as obtained from mysqli_result::fetch_all. 
     * 
     */
    public function get_uploads_info()
    {
        $conn = new \mysqli(DB_HOST, DB_USER, DB_PWD, DB);
        $r = $conn->query("SELECT Date, PrivateTrello, Status FROM days WHERE `UploadedBy` = '{$this->name}' ORDER BY Date DESC");
        $row = $r->fetch_all(MYSQLI_ASSOC);
        $r->free();
        $conn->close();
        return $row;
    }

    /**
     * Get privilege level.
     *  
     * @return Privilege
     * 
     */
    public function privilege()
    {
        $conn = new \mysqli(DB_HOST, DB_USER, DB_PWD, DB);
        $r = $conn->query("SELECT PrivilegeLevel FROM accounts WHERE `Name` = '{$this->name}'");
        return new Privilege($r->fetch_row()[0]);
    }

    /**
     * Check authorization by privilege level.
     *  
     * @return bool
     * 
     */
    public function has_privileges($min_lv)
    {
        $check = new Privilege($min_lv);
        return $this->privilege()->get_int() >= $check->get_int();
    }

    public function check()
    {
        if (!$this->tgid) {
            $conn = new \mysqli(DB_HOST, DB_USER, DB_PWD, DB);
            $r = $conn->query("SELECT Telegram_UserID, BlockLogin FROM accounts WHERE `Name` = '{$this->name}'");
            if ($r) {
                if ($row = $r->fetch_row()) {
                    $this->tgid = $row[0];
                    $this->can_login = !(bool) intval($row[1]);
                    $this->is_valid = true;
                } else {
                    $this->name = NULL;
                    $this->tgid = NULL;
                    $this->can_login = NULL;
                    $this->is_valid = false;
                }
            }
            $r->free();
            $conn->close();
            return $this->is_valid;
        }
        if (!$this->name) {
            $conn = new \mysqli(DB_HOST, DB_USER, DB_PWD, DB);
            $r = $conn->query("SELECT Name, BlockLogin FROM accounts WHERE `Telegram_UserID` = {$this->tgid}");
            if ($r) {
                if ($row = $r->fetch_row()) {
                    $this->name = $row[0];
                    $this->can_login = !(bool) intval($row[1]);
                    $this->is_valid = true;
                } else {
                    $this->name = NULL;
                    $this->tgid = NULL;
                    $this->can_login = NULL;
                    $this->is_valid = false;
                }
            }
            $r->free();
            $conn->close();
            return $this->is_valid;
        }

        $conn = new \mysqli(DB_HOST, DB_USER, DB_PWD, DB);
        $r = $conn->query("SELECT Name, BlockLogin FROM accounts WHERE `Name` = '{$this->name}' AND `Telegram_UserID` = {$this->tgid}");
        if ($row = $r->fetch_row()) {
            $this->is_valid = true;
            $this->can_login = !(bool) intval($row[1]);
        }
        return $this->is_valid;
    }
}