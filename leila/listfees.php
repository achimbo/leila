<?php
require_once 'variables.php';
require_once 'tools.php';
session_start();
require_once('configlocale.php');

if (!isset($_SESSION['usertype']) || $_SESSION['usertype'] != "admin") die ("Bitte <a href='login.php'>anmelden</a>");

$connection = new mysqli ( $db_hostname, $db_username, $db_password, $db_database );
if ($connection->connect_error)
    die ( $connection->connect_error );

$feesum = "";
$mylist = "";

if (isset($_GET['datefrom']) && isset($_GET['dateuntil']) && $_GET['datefrom'] != NULL && $_GET['dateuntil'] != NULL) {
    $from = sanitizeMySQL($connection, $_GET['datefrom']);
    $until = sanitizeMySQL($connection, $_GET['dateuntil']);
    if (datepresent($from) == "" && datepresent($until) == "") {

        $query = "SELECT COUNT(*) AS count FROM membershipfees WHERE membershipfees.from BETWEEN CAST('$from' AS DATE) AND CAST('$until' AS DATE) ";
        $result = $connection->query($query);
        $row = $result->fetch_array(MYSQLI_ASSOC);
        $count = $row['count'];
        $pag = paginate($count);

        $query = "SELECT SUM(amount) as amount FROM membershipfees WHERE membershipfees.from BETWEEN CAST('$from' AS DATE) AND CAST('$until' AS DATE) ";
        $result = $connection->query($query);
        $row = $result->fetch_array(MYSQLI_ASSOC);

        $feesum = sprintf(_('sum in the time between %1$s and %2$s: %3$s Euro'), $from, $until, $row['amount']);

        $message = "die zwischen $from und $until verliehen wurden";
        $query = "SELECT mf.*, u.firstname, u.lastname FROM membershipfees mf INNER JOIN users u ON mf.user_id = u.user_id WHERE mf.from BETWEEN CAST('$from' AS DATE) AND CAST('$until' AS DATE) ORDER BY mf.from ASC " . $pag['query'];
    } else {
        $error = _("date malformed");
    }
} else {
    $query = "SELECT COUNT(*) AS count FROM membershipfees";
    $result = $connection->query($query);
    $row = $result->fetch_array(MYSQLI_ASSOC);
    $count = $row['count'];
    $pag = paginate($count);

    $query = "SELECT SUM(amount) as amount FROM membershipfees";
    $result = $connection->query($query);
    $row = $result->fetch_array(MYSQLI_ASSOC);
    $feesum = _("total sum: ") . $row['amount'] . " Euro";

    $message = "insgesamt";
    $query = "SELECT mf.*, u.firstname, u.lastname FROM membershipfees mf INNER JOIN users u ON mf.user_id = u.user_id ORDER BY mf.from ASC " . $pag['query'];
}

$result = $connection->query($query);
if (!$result) die ("Database query error" . $connection->error);
$rows = $result->num_rows;

$mylist .= "<table id='feelist' class='margin-top table table-bordered'>";
$mylist .= "<thead><tr><th>" . _("user") . "</th><th>" . _("from") . "</th><th>" . _("until") . "</th><th>" . _("amount") . "</th></tr></thead>";

for ($r = 0; $r < $rows; ++$r) {
    $result->data_seek($r);
    $row = $result->fetch_array(MYSQLI_ASSOC);

    $mylist .= "<tr><td><a href='editmember.php?ID=" . $row['user_id'] . "'>" . $row['firstname'] . " " . $row['lastname'] . "</a></td>"
        . "<td>". $row['from'] . "</td><td>" . $row['until'] . "</td><td>" . $row['amount'] . "</td></tr>\n";
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
    <title><?= _('statistics')?></title>
</head>

<body>
<div class="container">

    <?php include 'nav.php';?>

    <script type="text/javascript">
        document.getElementById('memberstab').className = 'active';
        document.getElementById('objectspane').className = 'tab-pane';
        document.getElementById('memberspane').className = 'tab-pane active';
    </script>

    <h1><?= _('fees')?></h1>
    <?php if (isset ( $error ) && $error != "") echo "<span class='alert alert-danger'>" . _("error:") . " $error </span>" ?>
    <div class="row margin-top">
        <div class="col-md-6">
            <form method="get" action="listfees.php">
                <div class="form-group">
                    <label for="datefrom"> <?= _('from date')?> &#x1f4c5;</label>
                    <input type="text" class="form-control" id="datefrom" name="datefrom"><br>
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
        </div>
    </div>

    <h3><?= $feesum ?></h3><p>
    <div class="col-md-9">
        <?= $mylist ?>
        <?= $pag['footer']?>
    </div>

</div>
</body>
</html>