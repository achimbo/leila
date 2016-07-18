    <div class="row">
    	<div class="col-md-6">
            <div class="panel with-nav-tabs panel-default">
                <div class="panel-heading">
                        <ul class="nav nav-tabs">
                            <li class="active"><a href="#objects" data-toggle="tab">Objekte</a></li>
                            <li><a href="#members" data-toggle="tab">Mitglieder</a></li>
                            <li><a href="#lending" data-toggle="tab">Verleih</a></li>
                            <li class="dropdown">
                                <a href="#" data-toggle="dropdown">Sprache <span class="caret"></span></a>
                                <ul class="dropdown-menu" role="menu">
                                    <li><a href="#" data-toggle="tab">Deutsch</a></li>
                                    <li><a href="#" data-toggle="tab">English</a></li>
                                </ul>
                            </li>
                            <li><a href='login.php?logout=1'>Logout {$smarty.session.username}</a></li>
                        </ul>
                </div>
                <div class="panel-body">
                    <div class="tab-content">
                        <div class="tab-pane fade in active" id="objects"><a href='listobjects.php'>Objekte listen</a>&nbsp;<a href='addobject.php'>Objekt anlegen</a>&nbsp;<a href='categoriesadmin.php'>Kategorien verwalten</a></div>
                        <div class="tab-pane fade" id="members"><a href='listmembers.php'>Mitglieder listen</a>&nbsp;<a href='addmember.php'>Mitglied anlegen</a>&nbsp;<a href='listfees.php'>Geb&uuml;hren listen</a>&nbsp;<a href='sendnewsletter.php'>Newsletter senden</a></div>
                        <div class="tab-pane fade" id="lending"><a href='listlendedobjects.php'>Verleih &Uuml;bersicht</a>&nbsp;<a href='lendobject.php'>Objekte verleihen</a>&nbsp;<a href='statistics.php'>Statistik</a></div>
                    </div>
                </div>
            </div>
        </div>
	</div>