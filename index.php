<?php

/**
 * @author Dan Verständig
 */
require('inc/header.php');
?>
    <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
    <html xmlns="http://www.w3.org/1999/xhtml" xml:lang="de" lang="de" dir="ltr">

    <head>
        <meta http-equiv="Content-Script-Type" content="text/javascript"/>
        <meta http-equiv="Content-Style-Type" content="text/css"/>
        <meta http-equiv="content-language" content="de"/>

        <title>Start - <?php WORKSPACE_TITLE;?></title>

        <link rel="stylesheet" type="text/css" href="inc/stylesheets/layout.css" media="screen"/>

        <script type="text/javascript" src="inc/javascripts/prototype.js"></script>

        <script type="text/javascript" src="inc/javascripts/scriptaculous.js"></script>

        <script type="text/javascript" src="inc/javascripts/effects.js"></script>

        <script type="text/javascript">
            more_info = function (div_id) {

                $('detail-' + div_id).style.display = "block";
                $('element-' + div_id).style.paddingLeft = "20px";
                $('header-' + div_id).style.color = "#000000";
                $('element-' + div_id).style.backgroundImage = "url('img/avatarhover.gif')";
                $('element-' + div_id).style.backgroundRepeat = "no-repeat";
            };
            less_info = function (div_id) {
                $('detail-' + div_id).style.display = "none";
                $('element-' + div_id).style.paddingLeft = "5px";
                $('header-' + div_id).style.color = "#666666";
                $('element-' + div_id).style.backgroundImage = "none";

            }
        </script>
    </head>

<body>
<div id="wrapper">
<?php
require('inc/navigation.php');

if ($messageStack->size('general') > 0) echo $messageStack->output('general');
?>
    <div id="content">
        <?php
        if (!$SessionManager->logged_in()) {
            if (isset($_GET['position'])) {
                switch ($_GET['position']) {
                    case 'password':
                        ?>
                        <form method="post" action="index.php?do=resendpw">
                            <p class="text">
                                Passwort vergessen?<br/>
                                Gib einfach Deine E-Mailadresse an und Du bekommst eine E-Mail mit weiteren
                                Informationen.
                            </p>

                            <p class="left">E-Mail:</p>

                            <p>
                                <input type="text" name="email"/>&nbsp;<input type="submit" value="abschicken"
                                                                              id="submit"/><br/>
                            </p>
                        </form>
                        <?php
                        break;
                }
                ?>
            <?php
            } else {
                ?>

                <form method="post" action="index.php?do=login">
                    <p class="left">E-Mail:</p>

                    <p>
                        <input type="text" name="email"/>
                    </p>

                    <p class="left">Passwort:</p>

                    <p>
                        <input type="password" name="pass"/>
                    </p>

                    <p class="left">eingeloggt bleiben</p>

                    <p>
                        <input type="checkbox" name="staylogged"/>&nbsp;&nbsp;&nbsp;
                        <input type="submit" value="login" id="submit"/><br/>
                    </p>
                </form>
                <div style="margin:2% 10%;padding:4%;border:1px solid #e9ba00;background-color:#fffef8;">
                    <h2>Werkzeug zur Kategorisierung von Objekten in digitalen Welten</h2>

                    <p>Entstanden ist das Projekt im Rahmen des Seminars <em>Avatare – Körperinszenierungen in
                            Online-Communities, Sozialen Netzwerksites und Virtuellen Welten</em> bei Benjamin Jörissen,
                        an der Otto-von-Guericke Universität Magdeburg im Wintersemester 2007/2008.<br/>
                        <br/>Das Framework basiert auf einer PHP-Lösung in Verbindung zu einer MySQL Datenbank.<br/>
                        Mit diesem Framework können unterschiedliche komplexe Objekte erfasst und kategorisiert werden.
                        In diesem Falle wurden Avatare in virtuellen Welten systematisch untersucht. Es befindet sich
                        noch ein Datensatz als Beispiel online.
                        <br/><br/>
                        Das Framework kann mit dem Demo-Konto angesehen werden: </p>
                <pre>
                Name:       demo@pixelspace.org
                Passwort:   demo
                </pre>

                </div>
            <?php
            }
        } else {

            if (isset($_GET['position'])) {
                switch ($_GET['position']) {
                    case 'edit':
                        $aID = $_GET['aID'];
                        $db->query('SELECT id,title,url,description FROM ' . table_survey . ' WHERE id="' . $aID . '"');
                        $result = $db->fetchArray();
                        ?>
                        <h2><?php echo $result['title']; ?></h2>
                        <p class="left">URI</p>
                        <p>
                            <?php
                            echo $result['url'];
                            ?>
                        </p>
                        <p class="left">Beschreibung</p>
                        <p>
                            <?php
                            echo nl2br($result['description']); ?>
                        </p>
                        <?php
                        break;
                }
            } else {
                ?>
                <?php
                $split = '';
                $cols = false;
                $db->query('SELECT tn.id, tn.title, UNIX_TIMESTAMP(tn.timestamp) AS timestamp, tn.text, u.firstname, u.lastname FROM ' . table_news . ' tn, ' . table_users . ' u WHERE tn.userid = u.id ORDER BY tn.timestamp ASC');
                ?>

                <?php
                if ($db->numRows() > 0) {
                    $cols = true;
                    $split = ' style="width:60%;float:right;"';
                    ?>
                    <div>
                    <div style="width:38%;float:left;">
                        <h2>Informationen</h2>
                        <ul id="newslist">
                            <?php
                            $n = 0;
                            while ($row = $db->fetchArray()) {
                                $infoComments = '<small style="color:#999;font-weight:normal;">';
                                $infoComments .= '</small>';
                                ?>
                                <li <?php if ($n % 2 == 0) echo 'style="background:#efefef;"'; ?>>
                                    <h3><?php echo date('d.m.y', $row['timestamp']); ?>
                                        - <?php echo $row['title']; ?></h3>
                                    <?php
                                    if ($User->__get('userlevel') > 2) {
                                        ?>
                                        <a href="news.php?position=edit&amp;eID=<?php echo $row['id']; ?>">bearbeiten</a>&nbsp;|&nbsp;
                                        <a href="news.php?position=confirm_delete&amp;eID=<?php echo $row['id']; ?>">löschen</a>
                                    <?php
                                    }
                                    ?>
                                    <p><?php echo $row['text']; ?></p>
                                </li>
                                <?php
                                $n++;
                            }
                            ?>
                        </ul>
                    </div>
                <?php
                }
                ?>
                <div<?php echo $split; ?>>
                    <h2>Übersicht</h2>

                    <p id="subtitle">
		    <span style="float:right;">
		  	Alle Datensätze als:
			  <a href="csv_export.php">CSV</a>&nbsp;|&nbsp;
			  <a href="txt_export.php">TXT</a>&nbsp;|&nbsp;
			  <a href="xml_export.php">XML</a> exportieren
			</span>
                        <?php

                        $db->query('SELECT COUNT(id) AS num FROM ' . table_survey);

                        $av_count_arr = $db->fetchArray();

                        $num_avatars = $av_count_arr['num'];

                        $userid = $User->__get('id');

                        $fields = array();

                        $db->query('SELECT id FROM ' . table_fields);

                        while ($row = $db->fetchArray()) {
                            $fields[] = $row['id'];
                        }

                        $db->query('SELECT COUNT(id) AS fieldsum FROM ' . table_fields);
                        $fieldcount = $db->fetchArray();

                        $numFields = $fieldcount['fieldsum'];


                        $seiten = ceil($num_avatars / 6) - 1;

                        if (!isset($_GET['page'])) {
                            $page_param = 0;
                        } else {
                            $page_param = $_GET['page'];
                        }
                        if ($seiten < $page_param) $page_param = $seiten;
                        $start = $page_param * 6;
                        $db->query('SELECT ts.*, UNIX_TIMESTAMP(ts.timestamp) AS timestamp, u.firstname, u.lastname FROM ' . table_survey . ' ts, ' . table_users . ' u WHERE ts.userid = u.id ORDER BY ts.id  DESC LIMIT ' . $start . ', 6');

                        if ($db->numRows() > 0){

                        echo 'Seite: ';
                        if ($page_param > 0) {
                            ?>
                            <a href="index.php?page=<?php echo $page_param - 1; ?>">&laquo;</a>
                        <?php
                        }

                        for ($i = 0; $i < $seiten; $i++) {
                            if ($page_param > $i) {
                                ?>
                                <a href="index.php?page=<?php echo $i; ?>"><?php echo $i + 1; ?></a>
                            <?php
                            } elseif ($page_param == $i) {
                                ?>
                                <strong><?php echo $i + 1; ?></strong>
                            <?php
                            } else {
                                ?>
                                <a href="index.php?page=<?php echo $i; ?>"><?echo $i + 1; ?></a>
                            <?php
                            }
                        }
                        if ($page_param < $seiten && $seiten > 1) {
                            ?>
                            <a href="index.php?page=<?php echo $page_param + 1; ?>">&raquo;</a>
                        <?php
                        }
                        ?>
                    </p>
                    <ul id="avatarlist">
                        <?php
                        $n = 0;
                        while ($row = $db->fetchArray()) {
                            $fieldcount = 0;
                            foreach ($fields as $field) {
                                if ($row['field_' . $field] !== NULL)
                                    $fieldcount++;
                            }

                            $progress = 0;
                            if ($fieldcount > 0) {
                                $progress = ($fieldcount / $numFields) * 100;
                            }
                            $infoComments = '<small style="color:#999;font-weight:normal;">';
                            $numComment = $row['comments'];

                            if ($numComment < 1) {
                                $infoComments .= 'keine Kommentare';
                            } elseif ($numComment == 1) {
                                $infoComments .= '1 Kommentare';
                            } else {
                                $infoComments .= $row['comments'] . ' Kommentare';
                            }
                            $infoComments .= '</small>';
                            ?>
                            <li id="element-<?php echo $row['id']; ?>" <?php if ($n % 2 == 0) echo 'style="background:#efefef;"'; ?>
                                onmouseover="more_info('<?php echo $row['id'] ?>');"
                                onmouseout="less_info('<?php echo $row['id'] ?>')">
                                <h3 id="header-<?php echo $row['id']; ?>"
                                    style="color:#666;"><?php echo htmlspecialchars($row['title']); ?>
                                    <small style="color:#999;font-weight:normal;">
                                        am <?php echo date('d.m.y H:i', $row['timestamp']); ?>
                                        <?php
                                        if ($userid != $row['userid']) {
                                            ?>
                                            von <?php echo $row['firstname'] . ' ' . $row['lastname'];
                                        }
                                        ?> erstellt
                                    </small>
                                </h3>

                                <div id="detail-<?php echo $row['id']; ?>" style="display:none;">

                                    <p>
                                        <a href="inquiry.php?position=view&amp;cID=1&amp;aID=<?php echo $row['id']; ?>">Kategorisierung
                                            ansehen</a>&nbsp;|&nbsp;
                                        <?php
                                        if ($userid === $row['userid']) {
                                            ?>
                                            <a href="inquiry.php?position=evaluate&amp;cID=1&amp;aID=<?php echo $row['id']; ?>">Eintrag
                                                bearbeiten</a>&nbsp;|&nbsp;
                                            <a href="inquiry.php?position=edit&amp;aID=<?php echo $row['id']; ?>">Rahmendaten
                                                bearbeiten</a>&nbsp;|&nbsp;
                                            <a href="myinquiries.php?position=confirm_delete&amp;aID=<?php echo $row['id']; ?>">Eintrag
                                                löschen</a>&nbsp;|&nbsp;
                                        <?php
                                        }
                                        ?>
                                        <a href="feedback.php?position=view&amp;aID=<?php echo $row['id']; ?>">Feedback</a>
                                        (<?php echo $numComment; ?>)
                                        &nbsp;|&nbsp;Exportieren als:
                                        <a href="csv_export.php?aID=<?php echo $row['id']; ?>">CSV</a>&nbsp;|&nbsp;
                                        <a href="txt_export.php?aID=<?php echo $row['id']; ?>">TXT</a>&nbsp;|&nbsp;
                                        <a href="xml_export.php?aID=<?php echo $row['id']; ?>">XML</a>
                                        <br/><br/>
                                        <?php if (strlen($row['url'])) { ?>
                                            <strong>URI:</strong> <a
                                                href="<?php echo $row['url']; ?>"><?php echo $row['url']; ?></a><br/>
                                        <?php
                                        }
                                        if (strlen($row['description'])) {
                                            ?>
                                            <strong>Beschreibung:</strong> <?php echo $row['description']; ?>
                                        <?php
                                        }
                                        ?>
                                    </p>
                                    <strong style="float:left;line-height:19px;">Fortschritt:</strong>

                                    <div style="margin-left:80px;">
                                        <div class="progressbox">
                                            <div class="progressbar"
                                                 style="width:<?php echo $progress ?>%;"><?php echo $progress ?>%
                                            </div>
                                        </div>
                                    </div>

                                </div>
                            </li>
                            <?php
                            $n++;
                        }
                        ?>
                    </ul>
                    <?php
                    } else {
                        ?>
                        <p>noch keine Avatare.</p>
                    <?php
                    }
                    ?>
                </div>
                <?php
                if ($cols) {
                    ?>

                    <div style="clear:both;">&nbsp;</div>

                    </div>
                <?php
                }
            }
        }
        ?>
    </div>

<?php require('inc/footer.php'); ?>