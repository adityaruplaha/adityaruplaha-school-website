<?php

namespace ScA\Student;

const STUDENT_ATTENDANCE = 0b001;
const STUDENT_BASIC = 0b010;
const STUDENT_CONTACT = 0b100;

require_once "defs.php";

use const \ScA\DB;
use const \ScA\DB_HOST;
use const \ScA\DB_PWD;
use const \ScA\DB_USER;

use Exception;
use ScA\Classes\SchedClass;

class Privilege
{
    private const LOOKUP = [
        0 => "Basic",
        1 => "Member",
        2 => "Admin",
        3 => "Super Admin"
    ];

    public $lv;

    public function __construct($str)
    {
        if (!in_array($str, Privilege::LOOKUP)) {
            throw "Invalid Privilege Level.";
        }
        $this->lv = $str;
    }

    public function get_int()
    {
        return array_flip(Privilege::LOOKUP)[$this->lv];
    }
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

    public function __construct($name = NULL, $tgid = NULL)
    {
        if ($name == NULL && $tgid == NULL) {
            throw new Exception("Atleast 1 parameter must be supplied.");
        }
        $this->name = $name;
        $this->tgid = $tgid;
        $this->check();
    }

    /**
     * Get info merged from component functions as set by flags (use `|` to select multiple).
     *  
     * @param int flags
     * @param array passargs Arguments to pass to respective functions as: `{"STUDENT_ATTENDANCE" => [...], ...}`
     * 
     * @return array|null Associative array or NULL if no data is matched for some reason.
     * 
     */
    public function get_info($flags, $passargs = [])
    {
        $info = [];
        if ($flags & STUDENT_ATTENDANCE) {
            $arg = isset($passargs["STUDENT_ATTENDANCE"]) ? $passargs["STUDENT_ATTENDANCE"] : [];
            $info = array_merge($info, $this->get_attendance_data(...$arg));
        }
        if ($flags & STUDENT_BASIC) {
            $arg = isset($passargs["STUDENT_BASIC"]) ? $passargs["STUDENT_BASIC"] : [];
            $info = array_merge($info, $this->get_basic_info(...$arg));
        }
        if ($flags & STUDENT_CONTACT) {
            $arg = isset($passargs["STUDENT_CONTACT"]) ? $passargs["STUDENT_CONTACT"] : [];
            $info = array_merge($info, $this->get_contact_info(...$arg));
        }
        return $info;
    }

    /**
     * Get attendance data in the form {Name, Attendance % (float b/w 0 and 1), P (int), A (int), Total (int)}.
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
            if ($class->status == 'All OK') {
                array_push($cols, $class->as_colname('`'));
            }
        }
        $s = implode(', ', $cols);
        $r = $conn->query("SELECT {$s} FROM attendance WHERE `Name` = '{$this->name}'");
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
     * Get info in the form `{Name, ExtraSub, Status}`.
     *  
     * @return array|null Associative array 
     * 
     */
    public function get_basic_info()
    {
        $conn = new \mysqli(DB_HOST, DB_USER, DB_PWD, DB);
        $r = $conn->query("SELECT Name, ExtraSub, Status FROM info WHERE `Name` = '{$this->name}'");
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
        $r = $conn->query("SELECT Name, EMail, Mobile, Mobile2 FROM info WHERE `Name` = '{$this->name}'");
        $row = $r->fetch_assoc();
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
            $r = $conn->query("SELECT Telegram_UserID FROM accounts WHERE `Name` = '{$this->name}'");
            if ($r) {
                if ($row = $r->fetch_row()) {
                    $this->tgid = $row[0];
                    $this->is_valid = true;
                } else {
                    $this->name = NULL;
                    $this->tgid = NULL;
                    $this->is_valid = false;
                }
            }
            $r->free();
            $conn->close();
            return $this->is_valid;
        }
        if (!$this->name) {
            $conn = new \mysqli(DB_HOST, DB_USER, DB_PWD, DB);
            $r = $conn->query("SELECT Name FROM accounts WHERE `Telegram_UserID` = {$this->tgid}");
            if ($r) {
                if ($row = $r->fetch_row()) {
                    $this->name = $row[0];
                    $this->is_valid = true;
                } else {
                    $this->name = NULL;
                    $this->tgid = NULL;
                    $this->is_valid = false;
                }
            }
            $r->free();
            $conn->close();
            return $this->is_valid;
        }

        $conn = new \mysqli(DB_HOST, DB_USER, DB_PWD, DB);
        $r = $conn->query("SELECT Name FROM accounts WHERE `Name` = '{$this->name}' AND `Telegram_UserID` = {$this->tgid}");
        $this->is_valid = $r->fetch_row() ? true : false;
        return $this->is_valid;
    }
}
