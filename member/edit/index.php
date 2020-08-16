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
        XII Sc A - Edit Profile
    </title>
    <link rel='stylesheet' type='text/css' href='/sc_a/themes/dark/base.css' />
    <link rel='stylesheet' type='text/css' href='/sc_a/themes/dark/tables.css' />
    <link rel='stylesheet' type='text/css' href='/sc_a/themes/dark/input.css' />
    <link rel='stylesheet' type='text/css' href='/sc_a/themes/dark/slider.css' />
    <link rel='stylesheet' type='text/css' href='stylesheet.css' />
    <meta name="viewport" content="width=device-width, initial-scale=0.75">
    <script src='/sc_a/scripts/post.js'>
    </script>
    <script src='telemetry_slider.js'>
    </script>
</head>

<body>
    <h1>XII Sc A - Edit Profile</h1>
    <hr />
    <div>
        <table class='center hugefont bicolumn unbordered'>
            <?php

            if ($s->has_privileges("Admin")) {
                $telemetry_privacy = $s->get_telemetry_privacy();
                echo "
                <tr>
                    <td>Telemetry Mode</td>
                    <td class='slider_container'>
                        <input type='range' min='0' max='2'
                        value='{$telemetry_privacy}' class='slider mode{$telemetry_privacy}'
                        id='telemetry_slider' oninput='telemetry_updated(this)'/>
                    </td>
                </tr>
                <tr><td colspan=2><br/></td></tr>
                ";

                $contact = $s->get_contact_info();
            }

            ?>

            <tr>
                <td>Name</td>
                <td>
                    <?php echo $s->name; ?>
                </td>
            </tr>

            <form action='update_contact.php' method="POST">
                <tr>
                    <td><label for="EMail">E-mail Address</label></td>
                    <td>
                        <input type='email' name="EMail" value="<?php echo $contact['EMail']; ?>" required />
                    </td>
                </tr>
                <tr>
                    <td><label for="Mobile">Mobile Number (Primary)</label></td>
                    <td>
                        <input type='tel' name="Mobile" pattern="\+91[0-9]{10}" value="<?php echo $contact['Mobile']; ?>" required />
                    </td>
                </tr>
                <tr>
                    <td><label for="Mobile2">Mobile Number (Alternate)</label></td>
                    <td>
                        <input type='tel' name="Mobile2" pattern="\+91[0-9]{10}" value="<?php echo $contact['Mobile2']; ?>" />
                    </td>
                </tr>
                <tr>
                    <td><a href=" ../">Cancel </button> </td>
                    <td><button type='submit'>Save</button></td>
                </tr>
            </form>
        </table>
        <?php if (array_key_exists('done', $_GET)) {
            if ($_GET['done']) {
                echo '<p class="green">Successfully edited.</p>';
            } else {
                echo "<p class='red'>Failed to edit:";
                echo "<br/><br/>";
                echo $_GET["error"];
                echo "</p>";
            }
        }

        ?>
        <script id='remove_get'>
            window.history.replaceState(null, '', window.location.href.split('?')[0]);
            document.getElementById('remove_get').remove();
        </script>
    </div>
</body>

</html>
