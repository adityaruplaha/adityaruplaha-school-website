<?php


require_once "../login.php";
require_once "../student.php";

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
    header("Location: ../?nauth");
    exit;
}

?>
<!DOCTYPE html>
<html lang='en'>

<head>
    <title>
        XII Sc A - Policies
    </title>
    <script src='/sc_a/scripts/tab.js'>
    </script>
    <link rel='stylesheet' type='text/css' href='/sc_a/themes/dark/base.css' />
    <link rel='stylesheet' type='text/css' href='/sc_a/themes/dark/tables.css' />
    <link rel='stylesheet' type='text/css' href='/sc_a/themes/dark/tabs.css' />
    <link rel='stylesheet' type='text/css' href='/sc_a/themes/dark/cards.css' />
    <link rel='stylesheet' type='text/css' href='/sc_a/themes/dark/icons.css' />
    <link rel='stylesheet' type='text/css' href='/sc_a/themes/dark/flex.css' />
    <meta name="viewport" content="width=device-width, initial-scale=0.75">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/showdown/1.9.1/showdown.min.js"></script>
    <script>
    function cleanMarkdown() {
        var converter = new showdown.Converter();
        for (let e of document.getElementsByClassName("markdown")) {
            e.innerHTML = converter.makeHtml(e.innerHTML);
        }
    }
    </script>
</head>

<body onload='autoload(0);cleanMarkdown()'>
    <h1>XII Sc A - Policies</h1>
    <hr />
    <?php
    $last_edited = filemtime($_SERVER["DOCUMENT_ROOT"] . $_SERVER['PHP_SELF']);
    echo "<p>";
    echo "Last edited on: " . date("Y-m-d\TH:i:sP", $last_edited);
    echo "<br/><br/>";
    echo "This document is subject to change without prior notice.<br/>Disputes arising due to conflicting
    versions on the documents will be dealt according to the most recent version among them.<br/>";
    echo "By making use of this service, you agree to the below Terms and Conditions.";
    echo "</p>";
    ?>
    <table class='nav mediumfont'>
        <tr>
            <td onclick="show(this, 'content_policy')" class='tab_button'>Content Policy</td>
            <td onclick="show(this, 'membership_policy')" class='tab_button'>Membership Policy</td>
            <td onclick="show(this, 'privacy_policy')" class='tab_button'>Privacy Policy</td>
            <td onclick="show(this, 'code_licensing')" class='tab_button'>Code Licensing</td>
        </tr>
    </table>
    <div class='tab' id='content_policy'>
        <ol type="A">
            <li>
                All externally sourced content, but not derivatives there of, are to be be considered ownsership of
                their original owners. If the ownership of such content is unknown, then it may be considred to be
                placed in public domain.
            </li>
            <li>"Member" hereforth refers to any person who is able to login legitimately on ths website.</li>
            <li>
                User-generated content may hereforth refer to any of the following Definitions. Plural forms additionaly
                apply where applicable.
                <ol>
                    <li>The author is a member.</li>
                    <li>
                        The content in question has been based on external content, but has been altered sufficiently by
                        a member such that the member may be described as a co-author.
                    </li>
                    <li>
                        The content is external but is presented in format/structure that is substantially different
                        from its original. This includes document bundles that were created from external content. This
                        is only applicable if the one compiling the bundles is a member.
                    </li>
                </ol>
            </li>
            <li>
                The term "Intellectual Property holder" is defined as:
                <ol>
                    <li>All user-generated content satisfying Definition 1 on this website is to be regarded as
                        Intellectual Property of the person(s) who had originally wcreated them. All edits made
                        thereafter are the Intellectual Property of the editor(s).</li>
                    <li>In case of user-generated content satisfying Definition 2, the member(s) making the edit is to
                        be regarded as the Intellectual Property holder(s).</li>
                    <li>In case of user-generated content satisfying Definition 3, the member(s) creating the bundle is
                        to be regarded as the Intellectual Property holder(s).</li>
                </ol>
            </li>
            <li>
                All user-generated content on this website is licensed to its members for personal, non-commercial
                purposes only.
            </li>
            <li>User-generated content on of this website, in part or as a whole, must not be shared/transmitted to any
                external agencies (agencies who do not already have access to the contents) without the prior written
                permission of the Intellectual Property holder(s).
            </li>
        </ol>
    </div>
    <div class='tab' id='membership_policy'>
        <ol>
            <li>Membership is purely based on admin discretion, and can be revoked at anytime without notice or
                justification.</li>
            <li>Membership is non-transferable, and non-shareable.</li>
        </ol>
    </div>
    <div class='tab' id='privacy_policy'>
        <ol>
            <li>By making use of this service, you agree to having your data collected as per this policy.</li>
            <li>This website uses cookies solely for authentication.</li>
            <li>This website does not employ any form of third party analytics.</li>
            <li>
                This website is in no way affiliated to Trello or Telegram, so has no access to your data on either of
                these services, with the exception of the Telegram User ID, which is used for authentication and
                providing chatbot services only.
            </li>
            <li>This website employs action logging, which records all user activity on the website. This data is stored
                indefinitely and can be accessed by administrators.</li>
            <li>By using this service, you agree to your information being accessible by administrators, who can give it
                to teachers (only) without your consent if need be.</li>
        </ol>
    </div>
    <div class="tab" id="code_licensing">
        The <a class='compact' href='https://github.com/adityaruplaha/adityaruplaha-school-website'>source code</a>
        for
        this project is
        licensed under the
        <a class='compact' href="https://opensource.org/licenses/MIT">MIT License</a>, and uses code with the
        same or compatible license terms.
        <br />
        Libraries used:
        <ol>
            <li>
                <a class='compact' href='http://www.jacklmoore.com/autosize'>Autosize</a> by Jack Moore.
                (<a class='compact' href="https://opensource.org/licenses/MIT">MIT License</a>)
            </li>
            <li>
                <a class='compact' href='https://www.kryogenix.org/code/browser/sorttable/'>sorttable</a>
                by Stuart Langridge.
                (<a class='compact' href="https://www.kryogenix.org/code/browser/licence.html">MIT License</a>)
            </li>
            <li>
                <a class='compact' href='https://showdownjs.com/'>Showdown</a>
                (<a class='compact' href="https://github.com/showdownjs/showdown/blob/master/LICENSE">MIT
                    License</a>)
            </li>
            <li>
                <a class='compact' href='https://alexcorvi.github.io/anchorme.js/'>AnchorMe.js</a>
                (<a class='compact' href="https://github.com/alexcorvi/anchorme.js/LICENSE.md">MIT
                    License</a>)
            </li>
            <li>
                <a class='compact' href='https://github.com/caldwell/renderjson'>Renderjson</a> by David Caldwell.
                (<a class='compact' href="https://en.wikipedia.org/wiki/ISC_license">ISC
                    License</a>)
            </li>
            <li>
                <a class='compact' href='https://iconify.design'>Iconify</a>
                (<a class='compact' href="https://opensource.org/licenses/Apache-2.0">Apache License 2.0</a>)
            </li>
        </ol>
        <br />
        <hr />
        <pre>
        <?php
        echo file_get_contents($_SERVER["DOCUMENT_ROOT"] . '/sc_a/LICENSE')
        ?>
    </pre>
    </div>
</body>

</html>