<?php
require_once 'variables.php';
require_once 'tools.php';

session_start();
require_once('configlocale.php');

if (!isset($_SESSION['usertype']) || $_SESSION['usertype'] != "admin") die (_("please <a href='login.php'>login</a>"));

$connection = new mysqli ( $db_hostname, $db_username, $db_password, $db_database );
if ($connection->connect_error)
	die ( $connection->connect_error );

$message = "";
$comment = "";
$noquotesgivenback = "";

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
	$message = _('deleted transaction');
}


if (isset($_POST['lendobject']) || isset($_POST['updatelease'])) {
	$userid = sanitizeMySQL ( $connection, $_POST ['userid'] );
	$objectid = sanitizeMySQL ( $connection, $_POST ['objectid'] );
	$loanedout = sanitizeMySQL ( $connection, $_POST ['loanedout'] );
	$duedate = sanitizeMySQL ( $connection, $_POST ['duedate'] );
	$comment = sanitizeMySQL ( $connection, $_POST ['comment'] );
	$username = sanitizeMySQL ( $connection, $_POST ['username'] );
	$objectname = sanitizeMySQL ( $connection, $_POST ['objectname'] );



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
		if (isvaliduser($userid) < 1) $error .= _("user invalid");
		if (objectisavailable($objectid) == 0) $error .= _("object already rented away");
		if (objectisavailable($objectid) == -1) $error .= _("object status wrong");
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
			die ( _('invalid data') . $connection->error );
		} else {
			$message = "<a href='lendobject.php?edit=1&objectid=$objectid&userid=$userid&loanedout=$loanedout'>" . _('transaction') . "</a> " . _('saved') . "<p>";
		}

		if (is_null($noquotesgivenback) || $noquotesgivenback == "") {
			$email = getemail($userid);
			if ($email != "") {
				$subject = _('rented object in LOT');
				$headers = "From: $fromemail\r\n";
				$headers .= "Mime-Version: 1.0\r\n";
				$headers .= "Content-type: text/plain; charset=utf-8\r\n";
				$mailbody = sprintf(_('hello %1$s \n A short reminder: You have rented a %2$s in the LOT and should give it back until %3$s. \n kind regards, %4$s'), $username, $objectname, $duedate, $fromname);

				if (mail($email, $subject, $mailbody, $headers)) {
					$message .= _('email sent') . "<p>";
				}
			}
		}
	}
}

?>

<!DOCTYPE html>
<html>
<head>
	<link rel="stylesheet" href="leila-new.css"  type="text/css">
	<link rel="stylesheet" href="bootstrap/css/bootstrap.min.css"  type="text/css">
	<link rel="stylesheet" href="bootstrap/css/bootstrap-theme.min.css" type="text/css">
	<link rel="stylesheet" href="jquery-ui/jquery-ui.min.css">

	<script src="jquery/jquery.js"></script>
	<script src="bootstrap/js/bootstrap.min.js"></script>
	<script src="jquery-ui/jquery-ui.min.js"></script>


	<meta charset="utf-8"/>
	<title><?=_('rent object away')?></title>
</head>
<body onload="updateNames()">
<div class="container">
	<?php include 'nav.php';?>
	<script type="text/javascript">
		document.getElementById('lendingtab').className = 'active';
		document.getElementById('objectspane').className = 'tab-pane';
		document.getElementById('lendingpane').className = 'tab-pane active';
	</script>

		<h1><?php if (isset($_GET['edit'])) {echo _('update lease');} else { echo _('rent object away');} ?></h1>
	<div class="row margin-top">
		<div class="col-md-6">
			<?php
			if (isset ( $error ) && $error != "")
				echo "<div class='alert alert-danger'>" . _('error') . ":" . $error . " </div><p>";
			if (isset ( $message ) && $message != "") {
				echo "<div class='alert alert-success'>" . $message . " </div><p>";
			}
			?>

			<form action="lendobject.php<?php if (isset($_SERVER['QUERY_STRING']) && $_SERVER['QUERY_STRING'] != '') echo '?' . $_SERVER['QUERY_STRING']; ?>" method="post">
				<div class="form-group">
					<label for="userid"><?=_('User ID')?> &#x1f50e;</label>
					<input type="text" name="userid" class="form-control" id="userid" oninput="displayUserName(this)" <?php if (isset($_GET['edit'])) echo "readonly "; if (isset($_GET['userid'])) {echo "value='" . $_GET['userid']. "'";} elseif (isset($_POST['userid'])) {echo "value='". $_POST['userid'] . "'";} ?>>
				</div>
				<div class="form-group">
					<label for="username"><?=_('User Name')?> &#x1f50e;</label>
					<input type="text" autocomplete="off" name="username" class="form-control" id="username" oninput="searchUserName(this)" <?php if (isset($_GET['edit'])) echo "readonly "; ?>><p>
					<div id="usersearchbox"></div>
				</div>
				<div class="form-group">
					<label for="objectid"><?=_('Object ID')?> &#x1f50e;</label>
					<input type="text" name="objectid" class="form-control" id="objectid" oninput="displayObjectName(this)"<?php if (isset($_GET['edit'])) echo "readonly "; if (isset($_GET['objectid'])) {echo "value='" . $_GET['objectid']. "'";} elseif (isset($_POST['objectid'])) {echo "value='". $_POST['objectid'] . "'";} ?>>
				</div>
				<div class="form-group">
					<label for="objectname"><?=_('Object Name')?> &#x1f50e;</label>
					<input type="text" autocomplete="off" name="objectname" class="form-control" id="objectname" oninput="searchObjectName(this)" <?php if (isset($_GET['edit'])) echo "readonly "?>><p>
					<div id="objectsearchbox"></div>
				</div>
				<div class="form-group">
					<label for="loanedout"><?=_('Loaned From')?></label>
					<input type="text" name="loanedout" class="form-control" id="loanedout" <?php if (isset($_GET['edit'])) echo "readonly "; ?> value="<?php if (isset($_GET['loanedout'])) { echo $_GET['loanedout'];} else{ echo date("Y-m-d G:i:s", time());} ?>"><p>
				</div>
				<div class="form-group">
					<label for="duedate"><?=_('Loaned Until')?> &#x1f4c5;</label>
					<input type="text" name="duedate" class="form-control" id="duedate" value='<?php if (isset($_GET['edit'])) echo $duedate; else echo date("Y-m-d", (time() + 60 * 60 * 24 * 14))?>'><p>
						<script type="text/javascript">
							$( "#duedate" ).datepicker({
								dateFormat: "yy-mm-dd",
								firstDay: 1,
								changeYear: true
							});
						</script>
				</div>
				<div class="form-group">
					<?php
					if (isset($_GET['edit'])) {
						echo "<label for='givenback'>" . _('Date given back') . " &#x1f4c5;</label>";
						if (isset($givenback) && $givenback != 'NULL') {
							echo "<input type='text' name='givenback' class=\"form-control\" id='givenback' value='" . $noquotesgivenback . "'><p>";
						} else {
							echo "<input type='text' name='givenback'  class=\"form-control\" id='givenback' value=''><p>";
						}
					}
					?>
					<script type="text/javascript">
						$( "#givenback" ).datepicker({
							dateFormat: "yy-mm-dd",
							firstDay: 1,
							changeYear: true
						});
					</script>
				</div>
				<div class="form-group">
					<label for="comment"><?=_('Comment')?></label>
					<textarea name="comment" class="form-control" id="comment"><?=$comment?></textarea><p>
				</div>
				<?php
				if (isset($_GET['edit'])) {
					echo "<input type='submit'  class=\"btn btn-default\" name='updatelease' value='" . _('update lease') . "'><p>";
					echo "<input type='submit' class=\"btn btn-warning margin-top\" name='delete' value='" . _('delete lease') . "'" . " onclick='return confirm(\"" . _('Are you sure you want to delete?') . "\");'>";
				} else {
					echo "<input type='submit' class=\"btn btn-default\" name='lendobject' value='" . _('lend object') . "'>";
				}
				?>
			</form>

			<div id="test"></div>
		</div>
	</div>
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
								document.getElementById('objectsearchbox').innerHTML += "<div " + objectlist[x].style + " onclick=\"setObjectId(" + objectlist[x].id + ")\">ID: " + objectlist[x].id + " - " + objectlist[x].name + '</div>'
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