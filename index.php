<?php

/**
 * @author Dan Verständig
 */
require('inc/header.php');

if (isset($_GET['position']) && $_GET['position'] == 'activate') {
    if (!isset($_GET['code']) || !strlen($_GET['code']) == 32) {
        header('Location:index.php');
    }
    $code = htmlspecialchars($_GET['code']);
    $data = array(':code' => $code);
    $db->query('SELECT UNIX_TIMESTAMP(expires) as expires, usermail FROM ' . table_users . ' WHERE change_pass=:code', $data);
    $row = $db->fetch();
    if ($db->rowCount() == 1) {
        if ($row['expires'] < time()) {
            $messageStack->add_session('general', PASSWORD_RESET_CODE_EXPIRED, 'error');
            header('Location:index.php');
        }
    }
}
if (isset($_GET['page']) && $_GET['page'] == 0) {
    header('Location: index.php');
}

?>
    <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
    <html xmlns="http://www.w3.org/1999/xhtml" xml:lang="de" lang="de" dir="ltr">

    <head>
        <meta http-equiv="Content-Script-Type" content="text/javascript"/>
        <meta http-equiv="Content-Style-Type" content="text/css"/>
        <meta http-equiv="content-language" content="de"/>

        <title><?php echo WORKSPACE_TITLE; ?></title>

        <script src="//code.jquery.com/jquery-1.10.2.js"></script>
        <script src="//code.jquery.com/ui/1.11.3/jquery-ui.js"></script>
        <link rel="stylesheet" href="//code.jquery.com/ui/1.11.3/themes/smoothness/jquery-ui.css">
        <link rel="stylesheet" type="text/css" href="inc/stylesheets/layout.css" media="screen"/>

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
                        <h2><?php echo TITLE_PASSWORD_FORGOTTEN;?></h2>
                        <form id="form" method="post" action="index.php?do=resendpw">
                            <p><?php echo TEXT_PASSWORD_FORGOTTEN;?></p>
                            <label><?php echo LABEL_EMAIL;?></label>
                            <input type="text" name="email"/> <input type="submit" value="<?php echo TEXT_SUBMIT;?>"
                                                                     id="submit"/>
                            </p>
                        </form>
                        <?php
                        break;
                    case 'activate':
                        ?>
                        <h2><?php echo TITLE_SET_PASSWORD;?></h2>

                        <form id="form" method="post" action="index.php?do=activate">
                            <label for="mail"><?php echo LABEL_EMAIL;?></label>

                            <p><input readonly="readonly" id="mail" type="text" name="email"
                                      value="<?php echo $row['usermail'];?>"/></p>

                            <label for="pass"><?php echo LABEL_PASSWORD;?></label>

                            <p><input id="pass" type="password" name="pass"/></p>

                            <label for="pass"><?php echo LABEL_PASSWORD_RPT;?></label>

                            <p><input id="pass_rpt" type="password" name="pass_rpt"/></p>

                            <div class="r2">
                                <p><input type="hidden" value="<?php echo $code;?>" name="code"/>
                                    <input type="submit" value="<?php echo TEXT_SUBMIT;?>" id="submit"/>
                                </p>
                            </div>
                        </form>
                        <?php
                        break;
                }
                ?>
            <?php
            } else {
                ?>
                <h2><?php echo WORKSPACE_TITLE . ' &mdash; ' . TITLE_LOGIN; ?></h2>

                <form id="form" method="post" action="index.php?do=login">
                    <label for="mail"><?php echo LABEL_EMAIL; ?></label>

                    <p><input id="mail" type="text" name="email"/></p>

                    <label for="pass"><?php echo LABEL_PASSWORD; ?></label>

                    <p><input id="pass" type="password" name="pass"/></p>

                    <label for="cookie"><?php echo LABEL_COOKIE; ?></label>

                    <p class="cookie">
                        <input id="cookie" type="checkbox" name="stay"/>

                    </p>
                    <div class="r2">
                        <input type="submit" value="<?php echo TEXT_SUBMIT; ?>" id="submit"/>
                    </div>
                </form>
            <?php
            }
        } else {
            ?>
            <?php
            $split = '';
            $cols = false;
            $db->query('SELECT tn.id, tn.title, UNIX_TIMESTAMP(tn.timestamp) AS timestamp, tn.text, u.firstname, u.lastname FROM ' . table_news . ' tn, ' . table_users . ' u WHERE tn.userid = u.id ORDER BY tn.timestamp ASC');

            if ($db->rowCount() > 0) {
                $cols = true;
                $split = 'id="right"';
                ?>
                <div>
                <div id="left">
                    <h2><?php echo TITLE_NEWS; ?></h2>
                    <ul id="newslist">
                        <?php
                        $n = 0;
                        while ($row = $db->fetch()) {
                            ?>
                            <li>
                                <h3><?php echo date('d.m.y', $row['timestamp']); ?>
                                    - <?php echo $row['title']; ?>
                                <?php
                                if ($User->__get('userlevel') > 2) {
                                    ?>
                                    <a href="news.php?position=edit&amp;eID=<?php echo $row['id']; ?>"><?php echo TEXT_EDIT; ?></a> |
                                    <a href="news.php?position=confirm_delete&amp;eID=<?php echo $row['id']; ?>"><?php echo TEXT_DELETE; ?></a>
                                <?php
                                }
                                ?></h3>
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
            <div <?php echo $split; ?>>
                <h2><?php echo TITLE_OVIERVEW; ?></h2>

                <p id="subtitle">
                        <span id="data-export">
                            <?php echo TEXT_EXPORT_ALL_START; ?><a href="csv_export.php">CSV</a> | <a href="txt_export.php">TXT</a> | <a href="xml_export.php">XML</a> <?php echo TEXT_EXPORT_ALL_END; ?>
                        </span>
                    </ul>
                    <?php
                    $db->query('SELECT COUNT(id) AS num FROM ' . table_survey);
                    $av_count_arr = $db->fetch();
                    $num_avatars = $av_count_arr['num'];
                    $userid = $User->__get('id');
                    $fields = array();
                    $db->query('SELECT id FROM ' . table_fields);
                    while ($row = $db->fetch()) {
                        $fields[] = $row['id'];
                    }
                    $db->query('SELECT COUNT(id) AS fieldsum FROM ' . table_fields);
                    $fieldcount = $db->fetch();
                    $numFields = $fieldcount['fieldsum'];
                    $pages = ceil($num_avatars / 6);
                    if (!isset($_GET['page'])) {
                        $page_param = 0;
                    } else {
                        $page_param = intval($_GET['page']);
                    }
                    if ($pages < $page_param) $page_param = $pages;
                    $start = $page_param * 6;
                    $db->query('SELECT ts.*, UNIX_TIMESTAMP(ts.timestamp) AS timestamp, u.firstname, u.lastname
                                      FROM ' . table_survey . ' ts, ' . table_users . ' u
                                      WHERE ts.userid = u.id
                                      ORDER BY ts.id
                                      DESC LIMIT ' . $start . ', 6');

                    if ($db->rowCount() > 0){

                    echo TEXT_PAGE_INTRO;
                    if ($page_param > 0) {
                        ?>
                        <a href="index.php?page=<?php echo $page_param - 1; ?>">&laquo;</a>
                    <?php
                    }

                    for ($i = 0; $i < $pages; $i++) {
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
                    if ($page_param < $pages && $pages > 1) {
                        ?>
                        <a href="index.php?page=<?php echo $page_param + 1; ?>">&raquo;</a>
                    <?php
                    }
                    ?>
                </p>
                <ul id="avatarlist">
                    <?php
                    $n = 0;
                    while ($row = $db->fetch()) {
                        $fieldcount = 0;
                        foreach ($fields as $field) {
                            if (!is_null($row['field_' . $field]))
                                $fieldcount++;
                        }
                        $progress = 0;
                        if ($fieldcount > 0) {
                            $progress = ($fieldcount / $numFields) * 100;
                        }
                        $infoComments = '<small>';
                        $numComment = $row['comments'];

                        if ($numComment < 1) {
                            $infoComments .= ' ' . TEXT_NO_COMMENTS;
                        } elseif ($numComment == 1) {
                            $infoComments .= ' ' . TEXT_ONE_COMMENT;
                        } else {
                            $infoComments .= $row['comments'] . ' ' . TEXT_COMMENTS;
                        }
                        $infoComments .= '</small>';
                        ?>
                        <li id="element-<?php echo $row['id']; ?>">
                            <h3 id="header-<?php echo $row['id']; ?>"><?php echo htmlspecialchars($row['title']); ?>
                                <small>
                                    <?php
                                    echo TEXT_CREATED .' '. date('d.m.y H:i', $row['timestamp']);
                                    if ($userid != $row['userid']) {
                                        ?>
                                        von <?php echo $row['firstname'] . ' ' . $row['lastname'];
                                    }
                                    ?>
                                </small>
                            </h3>

                            <div id="detail-<?php echo $row['id']; ?>">

                                <p>
                                    <a href="inquiry.php?position=view&amp;cID=1&amp;aID=<?php echo $row['id']; ?>"><?php echo TEXT_VIEW_SURVEY; ?></a>
                                    |
                                    <a href="feedback.php?position=view&amp;aID=<?php echo $row['id']; ?>"><?php echo TEXT_FEEDBACK; ?></a>
                                    (<?php echo $infoComments; ?>)
                                    |
                                    <?php
                                    if ($userid === $row['userid']) {
                                        ?>
                                        <a href="inquiry.php?position=edit&amp;aID=<?php echo $row['id']; ?>"><?php echo TEXT_EDIT_SURVEY; ?></a> |
                                        <a href="inquiry.php?position=evaluate&amp;cID=1&amp;aID=<?php echo $row['id']; ?>"><?php echo TEXT_EDIT_ENTRY; ?></a> |
                                        <a href="myinquiries.php?position=confirm_delete&amp;aID=<?php echo $row['id']; ?>"><?php echo TEXT_DELETE_ENTRY; ?></a>
                                    <?php
                                    }
                                    ?>
                                    | <?php echo TEXT_EXPORT; ?>
                                    <a href="csv_export.php?aID=<?php echo $row['id']; ?>">CSV</a> |
                                    <a href="txt_export.php?aID=<?php echo $row['id']; ?>">TXT</a> |
                                    <a href="xml_export.php?aID=<?php echo $row['id']; ?>">XML</a>
                                    <?php if (strlen($row['url'])) { ?>

                                <p><strong>URI</strong> <a
                                        href="<?php echo $row['url']; ?>"><?php echo $row['url']; ?></a>
                                </p>
                                <?php
                                }
                                if (strlen($row['description'])) {
                                    ?>
                                    <p>
                                        <strong><?php echo TEXT_DESCRIPTION ?> </strong> <?php echo $row['description']; ?>
                                    </p>
                                <?php
                                }
                                ?>
                                </p>


                                <div id="progress-<?php echo $row['id'];?>" class="progressbar">
                                    <p class="progresslabel"><?php echo TEXT_PROGRESS .' '. $progress.'%'; ?> </p>
                                </div>
                                <script type="text/javascript">
                                    $( "#progress-<?php echo $row['id'];?>" ).progressbar({
                                        value: <?php echo $progress;?>
                                    });
                                </script>
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
                    <p><?php echo TEXT_NO_ENTRIES; ?></p>
                <?php
                }
                ?>
            </div>
            <?php
            if ($cols) {
                ?>
                <div class="c"></div>
                </div>
            <?php
            }
        }
        ?>
    </div>
<?php require('inc/footer.php'); ?>