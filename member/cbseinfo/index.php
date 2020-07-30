<?php


require_once "../../login.php";
require_once "../../student.php";

use \ScA\Student\TGLogin\TGLogin;

$s = TGLogin::from_cookie();
if ($s != NULL) {
    $s = new \ScA\Student\Student(NULL, $s->id);
}

if ($s != NULL) {
    $s->report_url_visit($_SERVER['PHP_SELF']);
}

$is_logged_in = ($s != NULL);

if (!$is_logged_in) {
    header("Location: ../../?nauth");
    exit;
}

?>
<!DOCTYPE html>
<html lang='en'>

<head>
    <title>
        XII Sc A - CBSE Info
    </title>
    <link rel='stylesheet' type='text/css' href='stylesheet.css' />
    <meta name="viewport" content="width=device-width, initial-scale=0.75">
    <script>
    function httpGet(theUrl) {
        var xmlHttp = new XMLHttpRequest();
        xmlHttp.open("GET", theUrl, false); // false for synchronous request
        xmlHttp.send(null);
        return xmlHttp.responseText;
    }

    function enableOverride() {
        httpGet("override.php?engage")
    }

    function disableOverride() {
        httpGet("override.php")
    }
    </script>
</head>

<body>
    <h1>XII Sc A - CBSE Info</h1>
    <hr />
    <div>
        <table class='center' style="table-layout: auto;">
            <?php
            function ver_string(bool $ver = NULL)
            {
                if ($ver === NULL) {
                    return "<td></td>";
                }
                if ($ver) {
                    return "<td class='green'><b>&#x2611;</b></td>";
                } else {
                    return "<td class='yellow'><b>?</b></td>";
                }
            }
            $contact = $s->get_contact_cbse();
            $info = $s->get_basic_info();
            $games = $s->get_games();
            echo "<tr>";
            echo "<td style='text-align: right;'>Name:</td><td style='text-align: left;'>{$contact['Name']}</td><td></td>";
            echo "</tr>";
            echo "<tr>";
            echo "<td style='text-align: right;'>Gender:</td><td style='text-align: left;'>{$info['Gender']}</td><td></td>";
            echo "</tr>";
            echo "<tr>";
            echo "<td style='text-align: right;'>Single girl child?</td><td style='text-align: left;'>{$info['SingleGirlChild']}</td><td></td>";
            echo "</tr>";
            echo "<tr>";
            echo "<td style='text-align: right;'>Religion:</td><td style='text-align: left;'>{$info['Religion']}</td><td></td>";
            echo "</tr>";
            echo "<tr>";
            echo "<td style='text-align: right;'>Caste:</td><td style='text-align: left;'>{$info['Caste']}</td><td></td>";
            echo "</tr>";
            echo "<tr>";
            echo "<td style='text-align: right;'>Games played:</td><td style='text-align: left;'>{$games}</td><td></td>";
            echo "</tr>";
            echo "<tr>";
            echo "<td style='text-align: right;'>Email:</td><td style='text-align: left;'>{$contact['EMail']}</td>";
            echo ver_string($contact['EMail_verified']);
            echo "</tr>";
            echo "<tr>";
            echo "<td style='text-align: right;'>Mobile No.:</td><td style='text-align: left;'>{$contact['Mobile']}</td>";
            echo ver_string($contact['Mobile_verified']);
            echo "</tr>";
            ?>
        </table>
        <?php
        if ($contact["ManualConfirm"]) {
            echo "<div style='text-align:center; font-size:20px;' class='yellow'>You have manually confirmed that your information is correct.<br/>";
            echo "Your information is exempt from automated checking.<br/>";
            echo "<a href='javascript:disableOverride();window.location.reload();'>Enable checks.</a></div>";
        } elseif (!$contact['EMail_verified'] || !$contact['Mobile_verified']) {
            echo "<div style='text-align:center; font-size:20px;' class='red'>Some of your contact information doesn't seem to add up. You sure this is correct?<br/>";
            echo "If not, contact an admin immediately.<br/>";
            echo "<a href='javascript:enableOverride();window.location.reload();'>Yes, this information is correct.</a></div>";
        } else {
            echo "<div style='text-align:center; font-size:20px;'>You can manually confirm that your information is correct. Your information is then exempt from automated checking.<br/>";
            echo "However, your information has already been auto-verified by the system, so you don't need to.<br/>";
            echo "<a href='javascript:enableOverride();window.location.reload();'>Yes, this information is correct.</a></div>";
        }
        ?>
    </div>
</body>

</html>