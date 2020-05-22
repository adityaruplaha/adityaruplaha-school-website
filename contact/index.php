<?php

require_once "../login.php";
require_once "../teacher/defs.php";

use \ScA\Student\TGLogin\TGLogin;
use \ScA\Teacher;

$is_logged_in = (TGLogin::from_cookie() != NULL) || (Teacher\is_logged_in());

if (!$is_logged_in) {
    header("Location: ../?nauth");
    exit;
}

?>
<html>

<head>
    <meta charset="utf-8">
    <link rel='stylesheet' type='text/css' href='stylesheet.css' />
</head>

<body>
    <h1 align=center>Contact Teachers</h1>
    <hr />
    <div>
        <table>
            <tr>
                <td align=center>Debarati Pramanik</td>
                <td align=center>Nandita Dastidar</td>
                <td align=center>Saswati Sur</td>
            </tr>
            <tr>
                <td><a href='vcf/debartip.vcf'><img src='img/debaratip.png' /></a></td>
                <td><a href='vcf/nanditad.vcf'><img src='img/nanditad.png' /></a></td>
                <td><a href='vcf/saswatis.vcf'><img src='img/saswatis.png' /></a></td>
            </tr>
            <tr>
                <td colspan=3>
                    <hr />
                </td>
            </tr>
            <tr>
                <td align=center>Soumi Karmakar</td>
            </tr>
            <tr>
                <td><a href='vcf/soumik.vcf'><img src='img/soumik.png' /></a></td>
            </tr>
        </table>
    </div>
    <hr width="67%" />
    <p align=center id='foot'>
        Scan the QR codes above to add teachers to your contacts.<br />
        Alternatively, click on them to download as vCard file.
    </p>
</body>

</html>