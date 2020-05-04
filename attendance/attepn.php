<?php

/**
 * Get an associative array in the form {Serial No., Name, Attendance % (float b/w 0 and 1)}
 * 
 * @param array $student Associative array given by msqli::fetch_assoc()
 * 
 * @return array Adssociative array 
 * 
 */
function process_student($student)
{
    if (!$student) {
        return NULL;
    }

    $p = 0;
    $net = 0;
    $result = array();

    foreach ($student as $key => $value) {
        if (!strpos($key, '_')) {
            $result[$key] = $value;
            continue;
        }
        if ($value != NULL) {
            $net += 1;
            $p += floatval($value);
        }
    }
    $result['Attendance %'] = $p / $net;
    $result['P'] = $p;
    $result['A'] = $net - $p;
    $result['Total'] = $net;

    return $result;
}