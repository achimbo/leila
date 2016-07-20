<?php
/**
 * Created by PhpStorm.
 * User: achim
 * Date: 19.07.16
 * Time: 17:07
 */

require_once('variables.php');

if (isset($_GET['lang']) && key_exists($_GET['lang'], $languages)) {
    $_SESSION['lang'] = $_GET['lang'];
    $lang = $_SESSION['lang'];
}
if (isset($_SESSION['lang'])) {
    $lang = $_SESSION['lang'];
} else {
    $lang = $defaultlang;
}

putenv("LC_ALL=" . $lang);
setlocale(LC_ALL, $lang);

$domain = 'messages';
bindtextdomain($domain, "/Library/WebServer/Documents/leila/leila/locale");
textdomain($domain);

?>