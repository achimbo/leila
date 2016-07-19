<?php
/**
 * Created by PhpStorm.
 * User: achim
 * Date: 19.07.16
 * Time: 12:25
 */

require_once 'variables.php';
require('smarty-leila.php');

session_start();



$smarty  = new Smarty_Leila();

$smarty->assign('name','achim');



$smarty->display('hello.tpl');

?>