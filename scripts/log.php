<?php

namespace ScA\Logging;

abstract class Logger
{
    private array $lines = [];

    public function __construct()
    {
        $this->lines = [];
        $this->write("Logging initialized: v1.2");
    }

    public function write(string ...$logs)
    {
        foreach ($logs as $log) {
            $this->write_internal($log);
        };
    }

    public function read()
    {
        return implode("\n", $this->lines);
    }

    public function clear()
    {
        $this->lines = [];
    }

    abstract function publish();

    private function write_internal($log)
    {
        $log = "[" . self::udate("Y-m-d\TH:i:s.uP") . "] " . $log;
        array_push($this->lines, $log);
    }

    private static function udate($format, $utimestamp = null)
    {
        if (is_null($utimestamp))
            $utimestamp = microtime(true);

        $timestamp = floor($utimestamp);
        $milliseconds = round(($utimestamp - $timestamp) * 1000000);

        return date(preg_replace('`(?<!\\\\)u`', $milliseconds, $format), $timestamp);
    }
}