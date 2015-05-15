<?php
$scriptname = basename($_SERVER['PHP_SELF']);

echo "<div id='nav'>";

if (isset($_SESSION['username'])) {
	echo "<span class='login' ><a href='login.php?logout=1'>Logout " . $_SESSION['username'] . "</a></span>";
} else {
	echo "<span class='login' ><a href='login.php'>login</a></span>";
}

switch ($scriptname){
	case "listmembers.php":
		echo "<a href='listobjects.php'>Objekte</a>&nbsp;<b><a href='listmembers.php'>Mitglieder</a></b><br>";
		echo "<b><a href='listmembers.php'>Mitglieder listen</a></b>&nbsp;<a href='addmember.php'>Mitglied anlegen</a>";
		break;
	
	case "addmember.php":
		echo "<a href='listobjects.php'>Objekte</a>&nbsp;<b><a href='listmembers.php'>Mitglieder</a></b><br>";
		echo "<a href='listmembers.php'>Mitglieder listen</a>&nbsp;<b><a href='addmember.php'>Mitglied anlegen</a></b>";
		break;

	case "editmember.php":
			echo "<a href='listobjects.php'>Objekte</a>&nbsp;<b><a href='listmembers.php'>Mitglieder</a></b><br>";
			echo "<a href='listmembers.php'>Mitglieder listen</a>&nbsp;<a href='addmember.php'>Mitglied anlegen</a>";
			break;		
		
	case "listobjects.php":
		echo "<b><a href='listobjects.php'>Objekte</a></b>&nbsp;<a href='listmembers.php'>Mitglieder</a><br>";
		echo "<b><a href='listobjects.php'>Objekte listen</a></b>&nbsp;<a href='addobject.php'>Objekt anlegen</a>&nbsp;<a href='categoriesadmin.php'>Kategorien verwalten</a>";		
		break;
		
	case "addobject.php":
		echo "<b><a href='listobjects.php'>Objekte</a></b>&nbsp;<a href='listmembers.php'>Mitglieder</a><br>";
		echo "<a href='listobjects.php'>Objekte listen</a>&nbsp;<b><a href='addobject.php'>Objekt anlegen</a></b>&nbsp;<a href='categoriesadmin.php'>Kategorien verwalten</a>";		
		break;
		
	case "categoriesadmin.php":
		echo "<b><a href='listobjects.php'>Objekte</a></b>&nbsp;<a href='listmembers.php'>Mitglieder</a><br>";
		echo "<a href='listobjects.php'>Objekte listen</a>&nbsp;<a href='addobject.php'>Objekt anlegen</a>&nbsp;<b><a href='categoriesadmin.php'>Kategorien verwalten</a></b>";		
		break;
	
	default:
		echo "<b><a href='listobjects.php'>Objekte</a></b>&nbsp;<a href='listmembers.php'>Mitglieder</a><br>";
		echo "<a href='listobjects.php'>Objekte listen</a>&nbsp;<a href='addobject.php'>Objekt anlegen</a>&nbsp;<a href='categoriesadmin.php'>Kategorien verwalten</a>";		
		break;
}


echo "<hr></div>\n";
?>