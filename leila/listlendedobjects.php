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
$mylist = '';

if (isset($_GET['showoverdue'])) {
    $query = "SELECT COUNT(*) AS count FROM rented WHERE DATEDIFF(duedate, curdate()) < 0  AND givenback IS NULL";
    $result = $connection->query($query);
    $row = $result->fetch_array(MYSQLI_ASSOC);
    $count = $row['count'];
    $pag = paginate($count);

    $query = "SELECT o.object_id AS objectid, o.name, u.user_id AS userid, u.firstname, u.lastname, r.loanedout, r.duedate, r.givenback
		 FROM objects o INNER JOIN rented r ON o.object_id = r.object_id INNER JOIN users u on r.user_id = u.user_id 
			WHERE DATEDIFF(duedate, curdate()) < 0  AND givenback IS NULL ORDER BY r.loanedout ASC " . $pag['query'];
    $message = _("which are overdue");
} elseif (isset($_GET['showrented'])) {
    $query = "SELECT COUNT(*) AS count FROM rented WHERE givenback IS NULL ";
    $result = $connection->query($query);
    $row = $result->fetch_array(MYSQLI_ASSOC);
    $count = $row['count'];
    $pag = paginate($count);

    $query = "SELECT o.object_id AS objectid, o.name, u.user_id AS userid, u.firstname, u.lastname, r.loanedout, r.duedate, r.givenback
		 FROM objects o INNER JOIN rented r ON o.object_id = r.object_id INNER JOIN users u on r.user_id = u.user_id
			WHERE givenback IS NULL ORDER BY r.loanedout ASC " . $pag['query'];
    $message = _("which are currently lended away");
} elseif (isset($_GET['datefrom']) && isset($_GET['dateuntil'])) {
    $from = sanitizeMySQL($connection, $_GET['datefrom']);
    $until = sanitizeMySQL($connection, $_GET['dateuntil']);
    if (datepresent($from) == "" && datepresent($until) == "") {

        $query = "SELECT COUNT(*) AS count FROM rented WHERE loanedout BETWEEN CAST('$from' AS DATE) AND CAST('$until' AS DATE) ";
        $result = $connection->query($query);
        $row = $result->fetch_array(MYSQLI_ASSOC);
        $count = $row['count'];
        $pag = paginate($count);

        $query = "SELECT o.object_id AS objectid, o.name, u.user_id AS userid, u.firstname, u.lastname, r.loanedout, r.duedate, r.givenback
		 FROM objects o INNER JOIN rented r ON o.object_id = r.object_id INNER JOIN users u on r.user_id = u.user_id
					WHERE loanedout BETWEEN CAST('$from' AS DATE) AND CAST('$until' AS DATE) ORDER BY r.loanedout ASC " . $pag['query'];
        $message = sprintf(_("which have been lended between %1$s and %2$s", $from, $until));
    } else {
        $query = "SELECT COUNT(*) AS count FROM rented";
        $result = $connection->query($query);
        $row = $result->fetch_array(MYSQLI_ASSOC);
        $count = $row['count'];
        $pag = paginate($count);

        $error = _("date malformed");

        $query = "SELECT o.object_id AS objectid, o.name, u.user_id AS userid, u.firstname, u.lastname, r.loanedout, r.duedate, r.givenback
		 FROM objects o INNER JOIN rented r ON o.object_id = r.object_id INNER JOIN users u on r.user_id = u.user_id ORDER BY r.loanedout ASC " . $pag['query'];
    }
} else {
    $query = "SELECT COUNT(*) AS count FROM rented";
    $result = $connection->query($query);
    $row = $result->fetch_array(MYSQLI_ASSOC);
    $count = $row['count'];
    $pag = paginate($count);

    $query = "SELECT o.object_id AS objectid, o.name, u.user_id AS userid, u.firstname, u.lastname, r.loanedout, r.duedate, r.givenback
		 FROM objects o INNER JOIN rented r ON o.object_id = r.object_id INNER JOIN users u on r.user_id = u.user_id ORDER BY r.loanedout ASC " . $pag['query'];
}

$result = $connection->query($query);
if (!$result) die ("Database query error" . $connection->error);
$rows = $result->num_rows;

$mylist .= "<table id='rentedlist' class='margin-top table table-bordered'>";
$mylist .= "<tr><th>" . _("object") . "</th><th>" . _("user") . "</th><th>" . _("rented") . "</th><th>" . _("due") . "</th><th>" . _("given back") . "</th></tr>";

for ($r = 0; $r < $rows; ++$r) {
    $result->data_seek($r);
    $row = $result->fetch_array(MYSQLI_ASSOC);

    $mylist .= '<tr><td><a href="showobject.php?ID=' . $row["objectid"] . '">' .$row['name'] . '</a></td>'
        . '<td><a href="editmember.php?ID=' . $row['userid'] . '">' . $row['firstname'] . ' ' .  $row['lastname'] . '</a></td>'
        . '<td><a href="lendobject.php?edit=1&objectid=' . $row['objectid'] . '&userid=' . $row['userid'] . '&loanedout=' . $row['loanedout'] . '">' . $row['loanedout']  . '</a></td>'
        . '<td>' . $row['duedate'] . '</td><td>' . $row['givenback'] . '</td></tr> ' . "\n";
    //$mylist .= 'Description ' . $row['description'] . '<br>';
}

$mylist .= "</table>";
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
    <title><?= _('overview of lended objects')?></title>
</head>

<body>
<div class="container">
    <?php include 'nav.php';?>

    <script type="text/javascript">
        document.getElementById('lendingtab').className = 'active';
        document.getElementById('objectspane').className = 'tab-pane';
        document.getElementById('lendingpane').className = 'tab-pane active';
    </script>

    <div class="h1"><?= _('overview of lended objects')?></div>

    <?php if (isset ( $error ) && $error != "") echo "<span class='errorclass'>" . _("error:") . "$error </span>" ?>

    <div class="col-md-6 margin-top">
    <form method="get" action="listlendedobjects.php">
        <div class="form-group">
        <label for="datefrom"><?= _("date from") ?> &#x1f4c5;</label>
        <input type="text" class="form-control" id="datefrom" name="datefrom">
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
        <label for="dateuntil"><?= _("date until") ?> &#x1f4c5;</label>
        <input type="text" class="form-control" id="dateuntil" name="dateuntil">
        <script type="text/javascript">
            $( "#dateuntil" ).datepicker({
                dateFormat: "yy-mm-dd",
                firstDay: 1,
                changeYear: true
            });
        </script>
            </div>
        <input type="submit" class="btn btn-default" value="<?= _('search')?>">
    </form><p>
    <form>
        <input type="submit" class="btn btn-default" name="showrented" value="<?= _('show rented objects')?>">
        <input type="submit" class="btn btn-default" name="showoverdue" value="<?= _('show overdue objects')?>">
    </form><p>
    <h3 class="margin-top"><?= _('rented objects') . " " . $message?></h3>
</div>
    <div class="col-md-9">
    <?= $mylist?>
    <?= $pag['footer']?>
</div>
</div>
</body>
</html>