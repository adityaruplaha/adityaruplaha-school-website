<?php

namespace ScA\Student\TGLogin;

use \ScA\Student\Student;

require_once $_SERVER['DOCUMENT_ROOT'] . '/telegram/BDMIOnlineClassesBot/defs.php';
require_once "student.php";
require_once "serverkey.php";

class TGLogin
{
    /**
     * Should be set only if the object is valid.
     * 
     * @var string
     */
    public $id;

    public function store()
    {
        $hash = TGLogin::get_hash($this->id);
        setcookie("tg_id", $this->id, time() + 7 * 86400, '/sc_a/', '', true);
        setcookie("tg_id_hash", $hash, time() + 7 * 86400, '/sc_a/', '', true);
    }

    public static function logout()
    {
        setcookie("tg_id", '', time() - 7 * 86400, '/sc_a/', '', true);
        setcookie("tg_id_hash", '', time() - 7 * 86400, '/sc_a/', '', true);
    }

    public static function from_auth_data($auth_data)
    {
        // Extract hash
        $check_hash = $auth_data['hash'];
        unset($auth_data['hash']);

        // Create data check string
        $data_check_arr = [];
        foreach ($auth_data as $key => $value) {
            $data_check_arr[] = $key . '=' . $value;
        }
        sort($data_check_arr);
        $data_check_string = implode("\n", $data_check_arr);

        // Check autheniticity of data
        $secret_key = hash('sha256', BOT_API_KEY, true);
        $hash = hash_hmac('sha256', $data_check_string, $secret_key);

        if (!hash_equals($hash, $check_hash)) {
            return NULL;
        }

        // Check whether data is up to date
        if ((time() - $auth_data['auth_date']) > 86400) {
            return NULL;
        }

        // Check whether the student is actually real.
        if (!(new Student(NULL, $auth_data['id']))->is_valid) {
            return NULL;
        }

        $obj = new TGLogin();
        $obj->id = $auth_data['id'];
        return $obj;
    }

    public static function from_cookie()
    {
        // Check whether the cookies exist and contain something.
        if (!isset($_COOKIE['tg_id']) || !isset($_COOKIE['tg_id_hash'])) {
            return NULL;
        }
        if (!$_COOKIE['tg_id'] || !$_COOKIE['tg_id_hash']) {
            TGLogin::logout();
            return NULL;
        }

        // Check whether data is authentic.
        $id = $_COOKIE['tg_id'];
        $hash = TGLogin::get_hash($id);
        if (!hash_equals($hash, $_COOKIE['tg_id_hash'])) {
            TGLogin::logout();
            return NULL;
        }

        // Check whether the student is actually real.
        if (!(new Student(NULL, $id))->is_valid) {
            TGLogin::logout();
            return NULL;
        }

        $obj = new TGLogin();
        $obj->id = $id;
        return $obj;
    }

    protected static function get_hash($id)
    {
        $secret_key = hash('sha3-512', BOT_API_KEY . SERVER_KEY);
        $hash = hash_hmac('sha3-512', $id, $secret_key);
        return $hash;
    }
}