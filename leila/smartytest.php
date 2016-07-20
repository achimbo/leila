<?php
/**
 * Created by PhpStorm.
 * User: achim
 * Date: 19.07.16
 * Time: 12:25
 */

require_once('variables.php');
require_once('smarty-leila.php');

session_start();

require_once('configlocale.php');




$smarty  = new Smarty_Leila();

$smarty->assign('name','achim');
$smarty->assign('data',array(array(1, 2, 3), array(4, 5, 6), array(7, 8, 9)));


$smarty->display('hello.tpl');

?>