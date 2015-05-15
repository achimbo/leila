<?php

session_start();
if (!isset($_SESSION['usertype']) || $_SESSION['usertype'] != "admin") die ("Bitte <a href='login.php'>anmelden</a>");

?>

<html>

<body>
<h1>Objekt verleihen</h1>
ID Vorname Nachname<br>
Objekt ID <input type="text"> Name <input type="submit" value="objekt suchen"><br>
Von <input type="text"> Bis <input type="text"><br>
Kommentar <input type="text"><br>
<input type="submit" value="objekt verleihen">
</body>
</html>