<?php
require_once 'variables.php';
require_once 'tools.php';

session_start();
require_once('configlocale.php');

if (!isset($_SESSION['usertype']) || $_SESSION['usertype'] != "admin") die (_("please <a href='login.php'>login</a>"));

$connection = new mysqli ( $db_hostname, $db_username, $db_password, $db_database );
if ($connection->connect_error)
	die ( $connection->connect_error );

$mylist = '';

if (isset($_GET['sortmax'])) {
	$sortmax = sanitizeMySQL($connection, $_GET['sortmax']);
} else {
	$sortmax = 10;
}

if (isset($_GET['datefrom'])) {
	$from = sanitizeMySQL($connection, $_GET['datefrom']);
} else {
	$from = "";
}

if (isset($_GET['dateuntil'])) {
	$until = sanitizeMySQL($connection, $_GET['dateuntil']);
} else {
	$until = "";
}

if(isset($_GET['byuser'])) {
	if (datepresent($from) == "" && datepresent($until) == "") {
		$query = "SELECT u.user_id, u.firstname, u.lastname, count(r.loanedout) as timesrented 
		FROM users u LEFT JOIN rented r ON u.user_id = r.user_id 
		WHERE loanedout BETWEEN CAST('$from' AS DATE) AND CAST('$until' AS DATE) or r.loanedout is null
		GROUP BY u.user_id, u.firstname, u.lastname ORDEr BY timesrented desc LIMIT $sortmax";		
	} else {
		$query = "SELECT u.user_id, u.firstname, u.lastname, count(r.loanedout) as timesrented 
		FROM users u LEFT JOIN rented r ON u.user_id = r.user_id 
		GROUP BY u.user_id, u.firstname, u.lastname ORDEr BY timesrented desc LIMIT $sortmax";	
	}
	$result = $connection->query($query);
	if (!$result) die ("Database query error " . $connection->error);
	$rows = $result->num_rows;

	$mylist .= "<table id='toplist' class='margin-top table table-bordered table-striped'>";
	$mylist .= "<tr><th>User</th><th>Geliehen</th></tr>";

	for ($r = 0; $r < $rows; ++$r) {
		$result->data_seek($r);
		$row = $result->fetch_array(MYSQLI_ASSOC);

		$mylist .= '<tr><td><a href="editmember.php?ID=' . $row["user_id"] . '">' .$row['firstname'] . ' ' . $row['lastname'] . '</a></td>'
			. '<td>' . $row['timesrented'] . '</td></tr> ' . "\n";
	}

	$mylist .= "</table>";

} elseif(isset($_GET['byobject'])) {
	if (datepresent($from) == "" && datepresent($until) == "") {
		$query = "SELECT o.name, o.object_id, count(r.loanedout) AS timesrented
		FROM objects o left join rented r on o.object_id = r.object_id
		where (r.loanedout BETWEEN CAST('$from' AS DATE) AND CAST('$until' AS DATE)) or r.loanedout is null
		group by o.object_id, o.name order by timesrented desc
		limit $sortmax";
		
// 		"SELECT o.name, r.object_id, COUNT(r.object_id) AS timesrented
// 			FROM rented r JOIN objects o ON r.object_id = o.object_id
// 			WHERE loanedout BETWEEN CAST('$from' AS DATE) AND CAST('$until' AS DATE)
// 			GROUP BY object_id
// 			ORDER BY timesrented DESC
// 			LIMIT $sortmax";
		
	} else {
		$query = "SELECT o.name, o.object_id, count(r.loanedout) AS timesrented
		FROM objects o left join rented r on o.object_id = r.object_id
		group by o.object_id, o.name order by timesrented desc
		limit $sortmax";
		
// 		"SELECT o.name, r.object_id, COUNT(r.object_id) AS timesrented
// 			FROM rented r JOIN objects o ON r.object_id = o.object_id
// 			GROUP BY object_id
// 			ORDER BY timesrented DESC
// 			LIMIT $sortmax";
	}
	$result = $connection->query($query);
	if (!$result) die ("Database query error " . $connection->error);
	$rows = $result->num_rows;

	$mylist .= "<table id='toplist' class='margin-top table table-bordered table-striped'>";
	$mylist .= "<tr><th>". _('object') . "</th><th>" ._('times rented') . "</th></tr>";

	for ($r = 0; $r < $rows; ++$r) {
		$result->data_seek($r);
		$row = $result->fetch_array(MYSQLI_ASSOC);

		$mylist .= '<tr><td><a href="showobject.php?ID=' . $row["object_id"] . '">' .$row['name'] . '</a></td>'
			. '<td>' . $row['timesrented'] . '</td></tr> ' . "\n";
	}

	$mylist .= "</table>";
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
	<title><?= _('statistics')?></title>
</head>

<body>
<div class="container">

	<?php include 'nav.php';?>

	<script type="text/javascript">
		document.getElementById('lendingtab').className = 'active';
		document.getElementById('objectspane').className = 'tab-pane';
		document.getElementById('lendingpane').className = 'tab-pane active';
	</script>

	<div class="h1"><?= _('statistics')?></div>
	<div class="row margin-top">
		<div class="col-md-6">
			<form>
				<div class="form-group">
					<label for="datefrom"><?= _('from date')?> &#x1f4c5;</label>
					<input type="text" id="datefrom" class="form-control" name="datefrom" value="<?php echo $from ?>"><br>
					<script type="text/javascript">
						$( "#datefrom" ).datepicker({
							dateFormat: "yy-mm-dd",
							firstDay: 1,
							defaultDate: -365,
							changeYear: true
						});
					</script>
				</div>
				<div class="form-group">
					<label for="dateuntil"><?= _('until date')?> &#x1f4c5;</label>
					<input type="text" id="dateuntil" class="form-control" name="dateuntil" value="<?php echo $until ?>">	<br>
					<script type="text/javascript">
						$( "#dateuntil" ).datepicker({
							dateFormat: "yy-mm-dd",
							firstDay: 1,
							changeYear: true
						});
					</script>
				</div>
				<div class="form-group">
					<label for="sortmax"><?= _('maximum entries')?> </label>
					<input type="text" id="sortmax" class="form-control" name="sortmax" value=<?php if (isset($_GET['sortmax'])){echo $_GET['sortmax'];} else {echo 10;}?>>	<br>
				</div>
				<input type="submit" name="byuser" class="btn btn-default" value="<?= _('sort by user')?>">
				<input type="submit" name="byobject" class="btn btn-default" value="<?= _('sort by object')?>">
			</form>
		</div>
		</div>
	<div class="col-md-6 table-responsive">
	<?= $mylist?>
</div>
</div>
</body>
</html>