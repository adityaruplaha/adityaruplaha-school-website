<?php

namespace ScA {

    const DB_HOST = 'localhost';
    const DB_USER = 'prog_access';
    const DB_PWD = '';
    const DB = 'xii_sc_a';
}

namespace Deprecate {
    function disable_page() {
        header('Location: /sc_a/disabled.php?from='.$_SERVER['PHP_SELF']);
        exit;
    }
}