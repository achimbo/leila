<div class="row">
    <div class="col-md-9">
        <div class="panel with-nav-tabs panel-default">
            <div class="panel-heading">
                <ul class="nav nav-tabs">
                    <li class="active"><a href="#objects" data-toggle="tab">Objects</a></li>
                    <li><a href="#members" data-toggle="tab">Members</a></li>
                    <li><a href="#lending" data-toggle="tab">Lending</a></li>
                    <li class="navbar-right"><a href='login.php?logout=1'>Logout {$smarty.session.username}</a></li>
                    <li class="dropdown navbar-right">
                        <a href="#" data-toggle="dropdown">Language <span class="caret"></span></a>
                        <ul class="dropdown-menu" role="menu">
                            {foreach $languages as $langkey => $langname}
                                <li><a href="{$smarty.server.SCRIPT_NAME}?lang={$langkey}">{$langname}</a></li>
                            {/foreach}
                        </ul>
                    </li>
                </ul>
            </div>
            <div class="panel-body">
                <div class="tab-content">
                    <div class="tab-pane fade in active" id="objects"><a href='listobjects.php'>List Objects</a>&nbsp;<a href='addobject.php' class="margin-left">Create Objects</a>&nbsp;<a href='categoriesadmin.php' class="margin-left">Manage Categories</a></div>
                    <div class="tab-pane fade" id="members"><a href='listmembers.php'>List Members</a>&nbsp;<a href='addmember.php' class="margin-left">Add Member</a>&nbsp;<a href='listfees.php' class="margin-left">List Fees</a>&nbsp;<a href='sendnewsletter.php' class="margin-left">Send Newsletter</a></div>
                    <div class="tab-pane fade" id="lending"><a href='listlendedobjects.php'>Lending Overview</a>&nbsp;<a href='lendobject.php' class="margin-left">Lend Object</a>&nbsp;<a href='statistics.php' class="margin-left">Statistic</a></div>
                </div>
            </div>
        </div>
    </div>
</div>