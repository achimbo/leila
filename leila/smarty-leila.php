<?php

// load Smarty library
require('smarty/libs/Smarty.class.php');

// The setup.php file is a good place to load
// required application library files, and you
// can do that right here. An example:
// require('guestbook/guestbook.lib.php');

class Smarty_Leila extends Smarty {

	function __construct($lang)
	{

		// Class Constructor.
		// These automatically get set with each new instance.

		parent::__construct();
		
		$this->setTemplateDir('views/templates/' . $lang);
		$this->setCompileDir('views/templates_c/');
		$this->setConfigDir('views/configs/');
		$this->setCacheDir('views/cache/');

	}

}


session_start();

$smarty  = new Smarty_Leila('DE');

$smarty->assign('name','me!');



$smarty->display('hello.tpl');


?>