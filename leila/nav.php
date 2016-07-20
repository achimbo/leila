<?php
/**
 * Created by PhpStorm.
 * User: achim
 * Date: 20.07.16
 * Time: 13:20
 */
?>

<div class="row">
    <div class="col-md-9">
        <div class="panel with-nav-tabs panel-default">
            <div class="panel-heading">
                <ul id="navtabs" class="nav nav-tabs">
                    <li id="objectstab"><a href="#objectspane" data-toggle="tab"><?=_('objects')?></a></li>
                    <li id="memberstab"><a href="#memberspane" data-toggle="tab"><?=_('members')?></a></li>
                    <li id="lendingtab"><a href="#lendingpane" data-toggle="tab"><?=_('lending')?></a></li>
                    <li class="navbar-right"><a href='login.php?logout=1'> <?= _('logout') . ' ' . $_SESSION['username']?> </a></li>
                    <li class="dropdown navbar-right">
                        <a href="#" data-toggle="dropdown">Language <span class="glyphicon glyphicon-flag"></span> <span class="caret"></span></a>
                        <ul class="dropdown-menu" role="menu">
                            <?php foreach ($languages as $langkey => $langname) {
                                echo ('<li ><a href ="' . $_SERVER['PHP_SELF'] . '?lang=' . $langkey . '">' . $langname . '</a ></li >');
                            } ?>
                        </ul>
                    </li>
                </ul>
            </div>
            <div class="panel-body">
                <div class="tab-content">
                    <div class="tab-pane active" id="objectspane"><a href='listobjects.php'><?=_('list objects')?></a>&nbsp;<a href='addobject.php' class="margin-left"><?=_('create objects')?></a>&nbsp;<a href='categoriesadmin.php' class="margin-left"><?=_('manage categories')?></a></div>
                    <div class="tab-pane" id="memberspane"><a href='listmembers.php'><?=_('list members')?></a>&nbsp;<a href='addmember.php' class="margin-left"><?=_('add member')?></a>&nbsp;<a href='listfees.php' class="margin-left"><?=_('list fees')?></a>&nbsp;<a href='sendnewsletter.php' class="margin-left"><?=_('send newsletter')?></a></div>
                    <div class="tab-pane" id="lendingpane"><a href='listlendedobjects.php'><?=_('lending overview')?></a>&nbsp;<a href='lendobject.php' class="margin-left"><?=_('lend object')?></a>&nbsp;<a href='statistics.php' class="margin-left"><?=_('statistics')?></a></div>
                </div>
            </div>
        </div>
    </div>
</div>
