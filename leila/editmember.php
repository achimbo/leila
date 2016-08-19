<?php
require_once 'variables.php';
require_once 'tools.php';

session_start();
require_once('configlocale.php');

if (!isset($_SESSION['usertype']) || $_SESSION['usertype'] != "admin") die ("Bitte <a href='login.php'>anmelden</a>");

$connection = new mysqli ( $db_hostname, $db_username, $db_password, $db_database );
if ($connection->connect_error)
    die ( $connection->connect_error );

if (isset ( $_GET ['ID'] )) {
    $uid = sanitizeMySQL ( $connection, $_GET ['ID'] );
} else {
    die ( "missing ID" );
}

if (isset($_GET['deletefee'])) {
    $fromfee = sanitizeMySQL ( $connection, $_GET ['from'] );
    $untilfee = sanitizeMySQL ( $connection, $_GET ['until'] );
    $query = "DELETE FROM membershipfees WHERE membershipfees.user_id= '$uid' AND membershipfees.from = '$fromfee' AND membershipfees.until = '$untilfee'";
    $result = $connection->query ( $query );
    if (! $result) {
        die ( _("member fee or date not valid ") . $connection->error );
    } else {
        $message = '<div class="message">' . _("fee deleted") . '</div>';
    }
}

if (isset($_POST['addfee'])) {
    $fromfee = sanitizeMySQL ( $connection, $_POST ['fromfee'] );
    $untilfee = sanitizeMySQL ( $connection, $_POST ['untilfee'] );
    $amount = sanitizeMySQL ($connection, $_POST ['amount']);

    $error = datepresent($fromfee);
    $error .= datepresent($untilfee);
    $error .= isint($amount);

    if ($error == "") {
        $query = "INSERT INTO membershipfees (`user_id`, `from`, `until`, `amount`) VALUES ('$uid', '$fromfee', '$untilfee', '$amount')";
        $result = $connection->query ( $query );
        if (! $result) {
            die ( _("member fee or date not valid ") .  $connection->error );
        } else {
            $message = '<div class="message">' . _("fee added") . '</div>';
        }
    }
}

if (isset ( $_POST ['deletemember'] )) {
    $query = "DELETE FROM users WHERE user_id = $uid";
    $result = $connection->query ( $query );
    if (! $result) die ( "Database query error" . $connection->error );
    echo '<head> <link rel="stylesheet" href="leila.css" type="text/css"></head>';
    include "menu.php";
    die ( "<div id='content'><h3>" . _("member deleted") . "</h3></div>" );
}

if (isset ( $_POST ['savemember'] )) {
    $firstname = sanitizeMySQL ( $connection, $_POST ['firstname'] );
    $lastname = sanitizeMySQL ( $connection, $_POST ['lastname'] );
    $usertype = sanitizeMySQL ( $connection, $_POST ['usertype'] );
    $password = sanitizeMySQL ( $connection, $_POST ['password'] );
    $street = sanitizeMySQL ( $connection, $_POST ['street'] );
    $city = sanitizeMySQL ( $connection, $_POST ['city'] );
    $zipcode = sanitizeMySQL ( $connection, $_POST ['zipcode'] );
    $country = sanitizeMySQL ( $connection, $_POST ['country'] );
    $telephone = sanitizeMySQL ( $connection, $_POST ['telephone'] );
    $email = sanitizeMySQL ( $connection, $_POST ['email'] );
    $idnumber = sanitizeMySQL ( $connection, $_POST ['idnumber'] );
    $comment = sanitizeMySQL ( $connection, $_POST ['comment'] );
    $comember = sanitizeMySQL ( $connection, $_POST ['comember'] );

    if (isset($_POST['getsnewsletter']) ){
        $getsnewsletter = 1;	}
    else {
        $getsnewsletter = 0;
    }

    if (isset($_POST['islocked']) ){
        $islocked = 1;	}
    else {
        $islocked = 0;
    }

    $error = isempty ( $firstname, _("firstname") );
    $error .= isempty ( $lastname, _("lastname") );
    $error .= isempty ( $street, _("street") );
    $error .= isempty ( $city, _("city") );
    $error .= isempty ( $zipcode, _("po code") );
    $error .= isempty ( $country, _("country") );

    if($usertype == 1){
        $error .= isempty($email, _("email"));
    }

    if ($usertype == 1 && isset ( $_POST ['updatepassword'] ) && ! passwordvalid ( $password )) {
        $error .= _("password needs to have 6 characters and a special character");
    } elseif ($usertype == 1 && isset ( $_POST ['updatepassword'] ) && passwordvalid ( $password )) {
        $password = hash ( 'ripemd160', "$salt$password" );
        if ($error == "") {
            $query = "UPDATE users SET usertype = $usertype, password = '$password', firstname = '$firstname', lastname = '$lastname',
			street = '$street', city = '$city', zipcode = '$zipcode', country = '$country', telephone = '$telephone',
			email = '$email', idnumber = '$idnumber', comment = '$comment', comember = '$comember', getsnewsletter = '$getsnewsletter', islocked = '$islocked' WHERE user_id = $uid";
            $result = $connection->query ( $query );
            if (! $result) {
                die (_("data invalid, member not saved") . $connection->error );
                $message = '<div class="errorclass">' . _("error, member not saved") . '</div>';
            } else {
                $message = '<div class="message">' . _("member saved") . '</div>';
            }
        }
    } else {
        if ($error == "") {
            $query = "UPDATE users SET usertype = $usertype, firstname = '$firstname', lastname = '$lastname', 
		street = '$street', city = '$city', zipcode = '$zipcode', country = '$country', telephone = '$telephone', 
		email = '$email', idnumber = '$idnumber', comment = '$comment', comember = '$comember', getsnewsletter = '$getsnewsletter', islocked = '$islocked' WHERE user_id = $uid";
            $result = $connection->query ( $query );
            if (! $result) {
                die (_("data invalid, member not saved") . $connection->error );
                $message = '<div class="errorclass">' . _("error, member not saved") . '</div>';
            } else {
                $message = '<div class="message">' . _("member saved") . '</div>';
            }
        }
    }
}

$query = "SELECT * FROM users WHERE user_id = " . $uid;
$result = $connection->query ( $query );

if (! $result)
    die ( "Database query error" . $connection->error );

$result->data_seek ( 0 );
$row = $result->fetch_array ( MYSQLI_ASSOC );

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
    <title><?= _('add member')?></title>
</head>

<body>
<div class="container">

    <?php include 'nav.php';?>

    <script type="text/javascript">
        document.getElementById('memberstab').className = 'active';
        document.getElementById('objectspane').className = 'tab-pane';
        document.getElementById('memberspane').className = 'tab-pane active';
    </script>

    <h1><?= _('edit member')?></h1>

    <?php
    if (isset ( $error ) && $error != "")
        echo "<div class='alert alert-danger'>" . _('error') . ":" . $error . " </div><p>";
    if (isset ( $message ) && $message != "") {
        echo "<div class='alert alert-success'>" . $message . " </div><p>";
    }
    ?>
    <div class="row margin-top">
        <div class="col-md-6">
    <form method="post" action="editmember.php?ID=<?=$uid?>">
        <div class="form-group">
            <label for="id">ID</label>
            <input disabled="disabled" class="form-control" name="id" id="id" type="text" value="<?= $row['user_id']?>">
        </div>
        <div class="form-group">
            <label for="usertype"><?= _('user type')?></label>
            <select name="usertype" class="form-control" id="usertype" size="1">
                <option value="1" <?php if ($row['usertype'] == 1) {echo "selected=\"selected\" ";}?>>Admin</option>
                <option value="2" <?php if ($row['usertype'] == 2) {echo "selected=\"selected\" ";}?>>Benutzer_in</option>
                <option value="3" <?php if ($row['usertype'] == 3) {echo "selected=\"selected\" ";}?>>Verleiher_in</option>
            </select>
        </div>
        <div class="form-group">
            <label for="firstname"><?= _('first name')?></label>
            <input type="text" class="form-control" name="firstname" id="firstname" value="<?= $row['firstname']?>">
        </div>
        <div class="form-group">
            <label for="lastname"><?= _('last name')?></label>
            <input type="text" class="form-control" name="lastname" id="lastname" value="<?= $row['lastname']?>">
        </div>
        <div class="form-group">
            <label for="password"><?= _('password')?></label>
            <input type="password" class="form-control" name="password" id="password">
        </div>
        <div class="form-group">
            <label for="updatepassword"><?= _('change password')?></label>
            <input type="checkbox" id="updatepassword" name="updatepassword" value="update">
        </div>
        <div class="form-group">
            <label for="street"><?= _('street')?></label>
            <input type="text" class="form-control" name="street" id="street" value="<?= $row['street']?>">
        </div>
        <div class="form-group">
            <label for="city"><?= _('city')?></label>
            <input type="text" class="form-control" name="city" id="city" value="<?= $row['city']?>">
        </div>
        <div class="form-group">
            <label for="zipcode"><?= _('po code')?></label>
            <input type="text" class="form-control" name="zipcode" id="zipcode" value="<?= $row['zipcode']?>">
        </div>
        <div class="form-group">
            <label for="country"><?= _('country')?></label>
            <input type="text" class="form-control" name="country" id="country" value="<?= $row['country']?>">
        </div>
        <div class="form-group">
            <label for="telephone"><?= _('telephone')?></label>
            <input type="text" class="form-control" name="telephone" id="telephone" value="<?= $row['telephone']?>">
        </div>
        <div class="form-group">
            <label for="email"><?= _('email')?></label>
            <input type="text" class="form-control" name="email" id="email" value="<?= $row['email']?>">
        </div>
        <div class="form-group">
            <label for="getsnewsletter"><?= _('receive newsletter')?></label>
            <input type="checkbox" id="getsnewsletter" name="getsnewsletter" value="1" <?php if ($row['getsnewsletter'] == 1) echo "checked='checked'";?>>
        </div>
        <div class="form-group">
            <label for="idnumber"><?= _('ID number')?></label>
            <input type="text" class="form-control" name="idnumber" id="idnumber" value="<?= $row['idnumber']?>">
        </div>
        <div class="form-group">
            <label for="comment"><?= _('comment')?></label>
            <textarea name="comment" class="form-control" id="comment" rows="5" cols="20"><?=$row['comment']?></textarea>
        </div>
        <div class="form-group">
            <label for="comember"><?= _('co member')?></label>
            <input type="text" class="form-control" name="comember" id="comember" value="<?=$row['comember']?>">
        </div>
        <div class="form-group">
            <label for="islocke"><?= _('lock user')?></label>
            <input type="checkbox" id="islocked" name="islocked" value="1" <?php if ($row['islocked'] == 1) echo "checked='checked'";?>>
        </div>
        <input type="submit" class="btn btn-default" name="savemember" value="<?= _('save changes')?>"><p>
            <input type="submit" class="margin-top btn btn-default" name="deletemember" value="<?= _('delete member')?>"
                   onclick="return confirm('<?= _('Are you sure you want to delete?')?>');">
    </form>
    <form method="post" action="printmember.php?ID=<?=$row['user_id']?>">
        <input type="submit" class="btn btn-default" name="printmember" value="<?= _('print member form')?>"><br>
    </form>
    <p>
    <form method="post" action="lendobject.php?userid=<?=$row['user_id']?>">
        <input type="submit" class="btn btn-default" name="lendobjecttouser" value="<?= _('lend object to user')?>"><br>
    </form>
            </div>
        </div>
    <p>
    <hr>
    <p>
        <div class="col-md-9">

        <?php
        $fees = getfees($uid);
        echo "<table id='feelist' class='margin-top table table-bordered table-striped'>";
        switch (isvaliduser($uid)) {
            case -3:
                echo "<caption><div class='invalid'>" . _('user locked') . "</div></caption>";
                break;

            case -1:
                echo "<caption><div class='invalid'>" . _('not a user') . "</div></caption>";
                break;

            case 0:
                echo "<caption><div class='invalid'>" . _('no membership fees paid') . "</div></caption>";
                break;

            case 1:
                echo "<caption><div class='tempvalid'>" . _('member soon invalid') . "</div></caption>";
                break;

            case 2:
                echo "<caption><div class='valid'>" . _('fees paid') . "</div></caption>";
                break;

            case 4:
                echo "<caption><div class='valid'>" . _('user is admin') . "</div></caption>";
                break;
        }
        echo "<thead><tr><th>" . _("from") . "</th><th>" . _("until") . "</th><th>" . _("amount") . "</th><th>" . _("delete") . "</thead>";
        foreach ($fees as $fee) {
            echo "<tr><td>" . $fee['from'] . "</td><td>" . $fee['until'] . "</td><td>" . $fee['amount'] . "</td><td><a onclick=\"return confirm('" . _('Are you sure you want to delete?') . "');\" href='?deletefee=1&ID=" . $uid . "&from=" . $fee['from'] . "&until=" . $fee['until'] . "'>" . _("delete") . "</a></td></tr>\n" ;
        }
        echo "</table><br>";
        ?>
    </div>
    <div class="row margin-top">
    <div class="col-md-6">

    <form method="post" action="editmember.php?ID=<?=$uid?>">
        <div class="form-group">
        <label for="fromfee"><?= _('fee from')?> &#x1f4c5;</label>
        <input type="text" class="form-control" name="fromfee" id="fromfee" value="<?= getcurrentdate()?>">
        <script type="text/javascript">
            $( "#fromfee" ).datepicker({
                dateFormat: "yy-mm-dd",
                firstDay: 1,
                changeYear: true
            });
        </script>
            </div>
        <div class="form-group">
        <label for="untilfee"><?= _('fee until')?> &#x1f4c5;</label>
        <input type="text" class="form-control" name="untilfee" id="untilfee" value="<?= date("Y-m-d", (time() + 60 * 60 * 24 * 365))?>">
        <script type="text/javascript">
            $( "#untilfee" ).datepicker({
                dateFormat: "yy-mm-dd",
                firstDay: 1,
                defaultDate: +365,
                changeYear: true
            });
        </script>
            </div>
        <div class="form-group">
        <label for="amount"><?= _('amount')?></label>
        <input type="text" class="form-control" name="amount" id="amount">
            </div>
        <input type="submit" class="btn-default btn" name="addfee" value="<?= _('add fee')?>">
    </form>
        </div>
        </div>


    <div class="row margin-top">
        <div class="col-md-9">
        <?php
        // rented object list
        $lendedobjects = getlendedobjects($uid);
        echo "<table id='lendedobjectslist' class='margin-top table table-bordered table-striped'>";
        echo "<caption>" . _("objects lended to LOT") . "</caption>";
        switch (isvaliduser($uid)) {
            case -2:
                echo "<caption><div class='invalid'>" . _("no object lended") . "</div></caption>";
                break;

            case 3:
                echo "<caption><div class='valid'>" . _("object lended") . "</div></caption>";
                break;
        }
        echo "<thead><tr><th>Objekt Name</th><th>" . _("lended until") . "</th></thead>";
        foreach ($lendedobjects as $object) {
            echo "<tr><td><a href='showobject.php?ID=" . $object['oid'] . "'>" . $object['name'] . "</a></td><td>" . $object['until'] . "</td></tr>\n" ;
        }
        echo "</table>";
?>
        </div>
    </div>

    <div class="row margin-top">
        <div class="col-md-9">
            <?php
        $rentals = getrentalsbyuser($uid);
        echo "<table id='rentallist' class='margin-top table table-bordered table-striped'>";
        echo "<caption>" . _("rental history") . "</caption>";
        echo "<thead><tr><th>" . _("object name") . "</th><th>" . _("from") . "</th><th>" . _("until") . "</th><th>" . _("given back") . "</th><th>" . _("comment") . "</th></thead>";

        foreach ($rentals as $rent) {
            // echo "<tr><td><a href='showobject.php?ID=" . $rent['objectid'] . "'>" . $rent['objectname'] . "</a></td>";
            // echo "<td>" . $rent['loanedout'] . "</td><td>" . $rent['duedate'] . "</td><td>" . $rent['givenback'] . "</td><td>" . $rent['comment'] . "</td></tr>";
            echo "<tr><td><a href='showobject.php?ID=" . $rent['objectid'] . "'>" . $rent['objectname'] . "</a></td>";
            echo "<td><a href='lendobject.php?edit=1&userid=" . $uid . "&objectid=" . $rent['objectid'] . "&loanedout=" . $rent['loanedout'] . "'>". $rent['loanedout'] . "</a></td>\n" ;
            echo "<td>" . $rent['duedate'] . "</td><td>" . $rent['givenback'] . "</td><td>" . $rent['comment'] . "</td></tr>";
        }
        echo "</table><p>"
        ?>
            </div>
        </div>
</div>
</body>
</html>