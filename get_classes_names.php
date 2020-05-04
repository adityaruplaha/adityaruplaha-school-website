<?php

/**
 * Get an array of strings containing field names enclosed by $delim.
 * 
 * @param mysqli $conn Connection to use.
 * @param date $d Date to obtain classes from.
 * @param array $filter Optional. Subjects to filter. Default: All.
 * @param string $delim Optional. Delimiter to enclose by. Default: ` (tilde).
 * 
 * @return array Array of strings containing field names enclosed by $delim.
 * 
 */
function get_classes_on($conn, $d, $filter = array(), $delim = '`')
{
    $d = date("Y-m-d", $d);
    $r = NULL;
    if ($filter) {
        $ar = [];
        foreach ($filter as $sub) {
            array_push($ar, "`Subject` = '{$sub}'");
        }
        $r = $conn->query("SELECT * FROM `xii_sc_a_classes` WHERE Date = '{$d}' AND (" . implode(" OR ", $ar) . ")");
    } else {
        $r = $conn->query("SELECT * FROM `xii_sc_a_classes` WHERE Date = '{$d}'");
    }
    $r = $conn->query("SELECT * FROM `xii_sc_a_classes` WHERE Date = '{$d}'");
    $classes = array();
    while ($class = $r->fetch_assoc()) {
        array_push($classes, $delim . $class["Date"] . '_' . $class["Subject"] . $delim);
    }
    return $classes;
}

/**
 * Get an array of strings containing field names enclosed by $delim.
 * 
 * @param mysqli $conn Connection to use.
 * @param date $from Date to obtain classes from.
 * @param date $to Date to obtain classes to.
 * @param array $filter Optional. Subjects to filter. Default: All.
 * @param string $delim Optional. Delimiter to enclose by. Default: ` (tilde).
 * 
 * @return array Array of strings containing field names enclosed by $delim.
 * 
 */
function get_classes_between($conn, $from, $to, $filter = array(), $delim = '`')
{
    $from = date("Y-m-d", $from);
    $to = date("Y-m-d", $to);
    $r = NULL;
    if ($filter) {
        $ar = [];
        foreach ($filter as $sub) {
            array_push($ar, "`Subject` = '{$sub}'");
        }
        $r = $conn->query("SELECT * FROM `xii_sc_a_classes` WHERE Date BETWEEN '{$from}' AND '{$to}' AND (" . implode(" OR ", $ar) . ")");
    } else {
        $r = $conn->query("SELECT * FROM `xii_sc_a_classes` WHERE Date BETWEEN '{$from}' AND '{$to}'");
    }
    $classes = array();
    while ($class = $r->fetch_assoc()) {
        array_push($classes, $delim . $class["Date"] . '_' . $class["Subject"] . $delim);
    }
    return $classes;
}

/**
 * Get an array of strings containing field names enclosed by $delim.
 * 
 * @param mysqli $conn Connection to use.
 * @param int $lim_days Optional. Number of previous days to include from today. Default: 0 = only today.
 * @param array $filter Optional. Subjects to filter. Default: All.
 * @param string $delim Optional. Delimiter to enclose by. Default: ` (tilde).
 * 
 * @return array Array of strings containing field names enclosed by $delim.
 * 
 */
function get_last_classes($conn, $lim_days = 0, $filter = array(), $delim = '`')
{
    $d = time() - $lim_days * 86400;
    return get_classes_between($conn, $d, time(), $filter, $delim);
}

/**
 * Get an array of strings containing field names enclosed by $delim.
 * 
 * @param mysqli $conn Connection to use.
 * @param int $lim_days Optional. Number of next days to include from today. Default: 0 = only today.
 * @param array $filter Optional. Subjects to filter. Default: All.
 * @param string $delim Optional. Delimiter to enclose by. Default: ` (tilde).
 * 
 * @return array Array of strings containing field names enclosed by $delim.
 * 
 */
function get_next_classes($conn, $lim_days = 0, $filter = array(), $delim = '`')
{
    $d = time() + $lim_days * 86400;
    return get_classes_between($conn, time(), $d, $filter, $delim);
}

/**
 * Get an array of strings containing field names enclosed by $delim.
 * 
 * @param mysqli $conn Connection to use.
 * @param date $d Date to obtain classes from.
 * @param array $filter Optional. Subjects to filter. Default: All.
 * @param string $delim Optional. Delimiter to enclose by. Default: ` (tilde).
 * 
 * @return array Array of strings containing field names enclosed by $delim.
 * 
 */
function get_classes_from($conn, $d, $filter = array(), $delim = '`')
{
    return get_classes_between($conn, $d, time(), $filter, $delim);
}