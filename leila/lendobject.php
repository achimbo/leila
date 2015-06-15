<?php
require_once 'variables.php';
require_once 'tools.php';

session_start();
if (!isset($_SESSION['usertype']) || $_SESSION['usertype'] != "admin") die ("Bitte <a href='login.php'>anmelden</a>");

$connection = new mysqli ( $db_hostname, $db_username, $db_password, $db_database );
if ($connection->connect_error)
	die ( $connection->connect_error );

$message = "";
$comment = "";
$noquotegivenback = "";

if (isset($_GET['edit'])) {
	$userid = sanitizeMySQL ( $connection, $_GET ['userid'] );
	$objectid = sanitizeMySQL ( $connection, $_GET ['objectid'] );
	$loanedout = sanitizeMySQL ( $connection, $_GET ['loanedout'] );
	$query = "SELECT duedate, givenback, comment FROM rented WHERE object_id = '$objectid' AND user_id = '$userid' AND loanedout = '$loanedout'";
	$result = $connection->query ( $query );
	$result->data_seek ( 0 );
	$row = $result->fetch_array ( MYSQLI_ASSOC );
	$duedate = $row['duedate'];
	$givenback = $row['givenback'];
	$noquotesgivenback = $givenback;
	$comment = $row['comment'];
}

if (isset($_POST['delete'])) {
	$userid = sanitizeMySQL ( $connection, $_POST ['userid'] );
	$objectid = sanitizeMySQL ( $connection, $_POST ['objectid'] );
	$loanedout = sanitizeMySQL ( $connection, $_POST ['loanedout'] );
	$query = "DELETE FROM rented WHERE object_id = '$objectid' AND user_id = '$userid' AND loanedout = '$loanedout'";
	$result = $connection->query ( $query );
	$message = "Verleihvorgang gel&ouml;scht";
}


if (isset($_POST['lendobject']) || isset($_POST['updatelease'])) {
	$userid = sanitizeMySQL ( $connection, $_POST ['userid'] );
	$objectid = sanitizeMySQL ( $connection, $_POST ['objectid'] );
	$loanedout = sanitizeMySQL ( $connection, $_POST ['loanedout'] );
	$duedate = sanitizeMySQL ( $connection, $_POST ['duedate'] );
	$comment = sanitizeMySQL ( $connection, $_POST ['comment'] );
	
	$error = isempty($userid, "User ID");
	$error .= isempty($objectid, "Objekt ID");
	$error .= datetimepresent($loanedout);
	$error .= datepresent($duedate);
	
	if (isset($_POST['updatelease'])) {
		$givenback = sanitizeMySQL($connection, $_POST['givenback']);
		$noquotesgivenback = $givenback;
		if ($givenback == "") {
			$givenback = 'NULL';
		} else {
			$error .= datepresent($givenback);
			$givenback = addquotes($givenback);
		}
	}	
		
	if (isset($_POST['lendobject'])) {	
		if (isvaliduser($userid) < 1) $error .= "User ist ung&uuml;ltig";
		if (objectisavailable($objectid) == 0) $error .= "Objekt bereits verliehen";
		if (objectisavailable($objectid) == -1) $error .= "Objekt Status falsch";
		// wenn loanedout in der Zukunft Abbruch?
	}
	if ($error == "") {
		if (isset($_POST['lendobject'])) {
		$query = "INSERT INTO rented (object_ID, user_ID, loanedout, duedate, comment) 
			VALUES ('$objectid', '$userid', '$loanedout', '$duedate', '$comment') ";
		} elseif (isset($_POST['updatelease'])) {
			$query = "UPDATE rented SET duedate='$duedate', givenback = $givenback, comment = '$comment'
				WHERE object_id = '$objectid' AND user_id = '$userid' AND loanedout = '$loanedout'";		
		} 
		$result = $connection->query ( $query );
		
		if (! $result) {
			die ( "Angaben fehlerhaft" . $connection->error );
		} else {
			$message = "Verleihvorgang Gespeichert<p>";
		}
	}	
}

?>

<!DOCTYPE html>
<html>
<head>
<link rel="stylesheet" href="leila.css" type="text/css">
<title>Objekt verleihen</title>
</head>
<body onload="updateNames()">
<?php include 'menu.php';?>
<div id="content">
	<h1>Objekt Verleih <?php if (isset($_GET['edit'])) echo "updaten"?></h1>
	<?php
	if (isset ( $error ) && $error != "")
		echo "<div class='errorclass'>Fehler: $error </div><p>";
	if (isset ( $message ))
		echo $message;
	?>
	<form action="lendobject.php<?php if (isset($_SERVER['QUERY_STRING']) && $_SERVER['QUERY_STRING'] != '') echo '?' . $_SERVER['QUERY_STRING']; ?>" method="post">
	<label for="userid">User ID</label>
	<input type="text" name="userid" id="userid" oninput="displayUserName(this)" <?php if (isset($_GET['edit'])) echo "readonly "; if (isset($_GET['userid'])) {echo "value='" . $_GET['userid']. "'";} elseif (isset($_POST['userid'])) {echo "value='". $_POST['userid'] . "'";} ?>><br>
	<label for="username">User Name</label>
	<input type="text" name="username" id="username" oninput="searchUserName(this)" <?php if (isset($_GET['edit'])) echo "readonly "; ?>><p>
	<div id="usersearchbox"></div>
	<label for="objectid">Objekt ID</label>
	<input type="text" name="objectid" id="objectid" oninput="displayObjectName(this)"<?php if (isset($_GET['edit'])) echo "readonly "; if (isset($_GET['objectid'])) {echo "value='" . $_GET['objectid']. "'";} elseif (isset($_POST['objectid'])) {echo "value='". $_POST['objectid'] . "'";} ?>><br>
	<label for="objectname">Objekt Name</label>
	<input type="text" name="objectname" id="objectname" oninput="searchObjectName(this)" <?php if (isset($_GET['edit'])) echo "readonly "?>><p>
	<div id="objectsearchbox"></div>
	<label for="loanedout">Von</label>
	<input type="text" name="loanedout" id="loanedout" <?php if (isset($_GET['edit'])) echo "readonly "; ?> value="<?php if (isset($_GET['loanedout'])) { echo $_GET['loanedout'];} else{ echo date("Y-m-d G:i:s", time());} ?>"><p>
	<label for="duedate">Bis</label>
	<input type="text" name="duedate" id="duedate" value='<?php if (isset($_GET['edit'])) echo $duedate; else echo date("Y-m-d", (time() + 60 * 60 * 24 * 14))?>'><p>
	<?php 
		if (isset($_GET['edit'])) {
			echo "<label for='givenback'>R&uuml;ckgabedatum</label>";
			if (isset($givenback) && $givenback != 'NULL') {
				echo "<input type='text' name='givenback' id='givenback' value='" . $noquotesgivenback . "'><p>";
			} else {
				echo "<input type='text' name='givenback' id='givenback' value=''><p>";
			}
		}
	?>
	<label for="comment">Kommentar</label>
	<textarea name="comment" id="comment"><?=$comment?></textarea><p>
	<?php
	if (isset($_GET['edit'])) {
		echo "<input type='submit' name='updatelease' value='Verleih updaten'><p>";
		echo "<input type='submit' name='delete' value='Verleih l&ouml;schen' onclick='return confirm(\"Sicher l&ouml;schen?\");'>";
	} else {
		echo "<input type='submit' name='lendobject' value='objekt verleihen'>";
	}
	?>
	</form>

	<div id="test"></div>
</div>
<script type="text/javascript">
	

function updateNames() {
	displayUserName(document.getElementById('userid'))
	displayObjectName(document.getElementById('objectid'))
}

function displayUserName(input) {
	var request = new ajaxRequest()

	request.open("GET", "leilaservice.php?userid=" + input.value, true)
    request.send(null)		

    request.onreadystatechange = function()
    {
      if (this.readyState == 4)
      {
        if (this.status == 200)
        {
          if (this.responseText != null)
          {
          		document.getElementById('username').value = unescapeHtml(this.responseText)
          }
          else alert("Ajax error: No data received")
        }
        else alert( "Ajax error: " + this.statusText)
      }
    }
  	
}

function displayObjectName(input) {
	var request = new ajaxRequest()

	request.open("GET", "leilaservice.php?objectid=" + input.value, true)
    request.send(null)		

    request.onreadystatechange = function()
    {
      if (this.readyState == 4)
      {
        if (this.status == 200)
        {
          if (this.responseText != null)
          {
          		document.getElementById('objectname').value = unescapeHtml(this.responseText)
          }
          else alert("Ajax error: No data received")
        }
        else alert( "Ajax error: " + this.statusText)
      }
    }
  	
}

function searchUserName(input) {

	if (input.value.length > 2) {	
		var request = new ajaxRequest()
	
		request.open("GET", "leilaservice.php?username=" + input.value, true)
	    request.send(null)		
	
	    request.onreadystatechange = function()
	    {
	      if (this.readyState == 4)
	      {
	        if (this.status == 200)
	        {
	          if (this.responseText != null)
	          {
		          var objectlist = JSON.parse(this.responseText)
	          		document.getElementById('usersearchbox').innerHTML = ""
	      		document.getElementById('usersearchbox').style.display = "block" 
		      		for (x in objectlist) {	
        				document.getElementById('usersearchbox').innerHTML += "<div onclick=\"setUserId(" + objectlist[x].id + ")\">ID: " + objectlist[x].id + " - " + objectlist[x].name + '</div>'
		      		}
	          }
	          else alert("Ajax error: No data received")
	        }
	        else alert( "Ajax error: " + this.statusText)
	      }
	    }
	}	else {
		document.getElementById('usersearchbox').innerHTML = ""
		document.getElementById('usersearchbox').style.display = "none"
	}
}

function searchObjectName(input) {

	if (input.value.length > 2) {	
		var request = new ajaxRequest()
	
		request.open("GET", "leilaservice.php?objectname=" + input.value, true)
	    request.send(null)		
	
	    request.onreadystatechange = function()
	    {
	      if (this.readyState == 4)
	      {
	        if (this.status == 200)
	        {
	          if (this.responseText != null)
	          {
		          var objectlist = JSON.parse(this.responseText)
		          		document.getElementById('objectsearchbox').innerHTML = ""
		      		document.getElementById('objectsearchbox').style.display = "block" 
			      		for (x in objectlist) {	
	          				document.getElementById('objectsearchbox').innerHTML += "<div onclick=\"setObjectId(" + objectlist[x].id + ")\">ID: " + objectlist[x].id + " - " + objectlist[x].name + '</div>'
			      		}
	          }
	          else alert("Ajax error: No data received")
	        }
	        else alert( "Ajax error: " + this.statusText)
	      }
	    }
	}	else {
		document.getElementById('objectsearchbox').innerHTML = ""
		document.getElementById('objectsearchbox').style.display = "none"
	}
}

function setObjectId(id) {
	document.getElementById('objectid').value = id
	document.getElementById('objectsearchbox').style.display = "none" 
	updateNames()
}

function setUserId(id) {
	document.getElementById('userid').value = id
	document.getElementById('usersearchbox').style.display = "none" 
	updateNames()
}

function ajaxRequest()
{
	try
	{
		var request = new XMLHttpRequest()
	}
	catch(e1)
	{
		try
		{
			request = new ActiveXObject("Msxml2.XMLHTTP")
		}
		catch(e2)
		{
			try
			{
				request = new ActiveXObject("Microsoft.XMLHTTP")
			}
			catch(e3)
			{
				request = false
			}
		}
	}
	return request
}

function unescapeHtml(unsafe) {
    return unsafe
        .replace(/&amp;/g, "&")
        .replace(/&ouml;/g, "ö")
        .replace(/&Ouml;/g, "Ö")
        .replace(/&auml;/g, "ä")
        .replace(/&Auml;/g, "Ä")
        .replace(/&uuml;/g, "ü")
        .replace(/&Uuml;/g, "Ü")
        .replace(/&szlig;/g, "ß")
        .replace(/&lt;/g, "<")
        .replace(/&gt;/g, ">")
        .replace(/&quot;/g, "\"")
        .replace(/&#039;/g, "'");
}
</script>
</body>
</html>