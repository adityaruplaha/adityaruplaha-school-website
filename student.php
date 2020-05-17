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
     * Get info merged from component functions as set by flags (use | to select multiple).
     *  
     * @return array|null Associative array or NULL if no data is matched for some reason.
     * 
     */
    public function get_info($flags)
    {
        $info = [];
        if ($flags & STUDENT_ATTENDANCE) {
            $info = array_merge($info, $this->get_attendance_data());
        }
        if ($flags & STUDENT_BASIC) {
            $info = array_merge($info, $this->get_basic_info());
        }
        if ($flags & STUDENT_CONTACT) {
            $info = array_merge($info, $this->get_contact_info());
        }
        return $info;
    }

    /**
     * Get attendance data in the form {Name, Attendance % (float b/w 0 and 1), P (int), A (int), Total (int)}.
     *  
     * @return array Associative array 
     * 
     */
    public function get_attendance_data()
    {
        $conn = new \mysqli(DB_HOST, DB_USER, DB_PWD, DB);
        $r = $conn->query("SELECT * FROM attendance WHERE `Name` = '{$this->name}'");
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
     * Get info in the form {Name, ExtraSub, Status}.
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
     * Is the student a Trello member?
     *  
     * @return bool
     * 
     */
    public function on_trello()
    {
        $conn = new \mysqli(DB_HOST, DB_USER, DB_PWD, DB);
        $r = $conn->query("SELECT OnTrello FROM accounts WHERE `Name` = '{$this->name}'");
        return $r->fetch_row()[0] == 'Yes';
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