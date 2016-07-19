<?php

// load Smarty library
require('smarty/libs/Smarty.class.php');


// The setup.php file is a good place to load
// required application library files, and you
// can do that right here. An example:
// require('guestbook/guestbook.lib.php');

class Smarty_Leila extends Smarty {

	function __construct()
	{
        include 'variables.php';
		// Class Constructor.
		// These automatically get set with each new instance.

		parent::__construct();

        $this->setTemplateDir('views/templates/');
        $this->setCompileDir('views/templates_c/');
        $this->setConfigDir('views/configs/');
		$this->setCacheDir('views/cache/');

        $this->assign('languages', $languages);

    }

}


?>