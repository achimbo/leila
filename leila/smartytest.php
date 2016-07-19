<?php
/**
 * Created by PhpStorm.
 * User: achim
 * Date: 19.07.16
 * Time: 12:25
 */

require_once('variables.php');
require_once('smarty-leila.php');
require_once('configlocale.php');

session_start();




$smarty  = new Smarty_Leila();

$smarty->assign('name','achim');



$smarty->display('hello.tpl');

?>