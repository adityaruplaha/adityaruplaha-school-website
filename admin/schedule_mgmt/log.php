<?php

namespace ScA\Logging;

use ScA\Logging\Logger;

require_once "../../defs.php";
require_once "../../scripts/log.php";

use const ScA\DB;
use const ScA\DB_HOST;
use const ScA\DB_PWD;
use const ScA\DB_USER;

class ActionLogger extends Logger
{
    private string $action;

    public function __construct($action)
    {
        parent::__construct();
        $this->action = $action;
    }

    public function publish(\mysqli $conn = NULL)
    {
        if (!$conn) {
            $conn = new \mysqli(DB_HOST, DB_USER, DB_PWD, DB);
        }
        $contents = $conn->real_escape_string($this->read());
        $conn->query("INSERT INTO `command_logs` VALUES (CURRENT_TIMESTAMP(), '{$this->action}', \"{$contents}\")");
    }
}