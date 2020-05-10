<?php

namespace ScA\Student;

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
     * Get attendance data in the form {Name, Attendance % (float b/w 0 and 1), P (int), A (int), Total (int)}.
     *  
     * @return array Adssociative array 
     * 
     */
    public function get_attendance_data()
    {
        $conn = new \mysqli(DB_HOST, DB_USER, DB_PWD, DB);
        $r = $conn->query("SELECT * FROM xii_sc_a_attendance WHERE `Name` = '{$this->name}'");
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

    public function check()
    {
        if (!$this->tgid) {
            $conn = new \mysqli(DB_HOST, DB_USER, DB_PWD, DB);
            $r = $conn->query("SELECT Telegram_UserID FROM xii_sc_a_accounts WHERE `Name` = '{$this->name}'");
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
            $r = $conn->query("SELECT Name FROM xii_sc_a_accounts WHERE `Telegram_UserID` = {$this->tgid}");
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
        $r = $conn->query("SELECT Name FROM xii_sc_a_accounts WHERE `Name` = '{$this->name}' AND `Telegram_UserID` = {$this->tgid}");
        $this->is_valid = $r->fetch_row() ? true : false;
        return $this->is_valid;
    }
}