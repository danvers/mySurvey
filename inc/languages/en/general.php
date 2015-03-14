<?php
/**
 * Created by PhpStorm.
 * User: dan
 * Date: 07.03.2015
 * Time: 01:29
 */

define('TEXT_SUBMIT','submit');
define('TEXT_BACK','&laquo; back');
define('TEXT_EDIT','edit');
define('LABEL_PASSWORD','password');
define('LABEL_PASSWORD_RPT','repeat password');
define('LABEL_COOKIE','stay logged in');
define('LABEL_EMAIL','e-mail');
define('TEXT_CANCEL','cancel');
define('TEXT_DELETE','delete');
define('TEXT_SAVE','save');

define('LABEL_FIRSTNAME','firstname');
define('LABEL_LASTNAME','lastname');

define('TEXT_DESCRIPTION','description');

define('MSG_E_MAIL','either the email is too short or not consistent');
define('MSG_E_FIRSTNAME','the firstname must be at least %s characters');
define('MSG_E_LASTNAME','the lastname must be at least %s characters');
define('MSG_E_LOGIN','password or email wrong');
define('MSG_E_PASS','no data updated');
define('MSG_E_PASS_UPDATE_ERROR','the password has not been updated');
define('MSG_E_PASS_RPT','the passwords are not similar');
define('MSG_E_PASS_MIN_LENGTH','the pass must have at least 6 characters');
define('MSG_PASS_RESEND','an email with additional info was sent to %s');
define('MSG_PROFILE_UPDATE_SUCCESS','profile has been updated');
define('MSG_PASS_UPDATE_SUCCESS','password successfully changed');
define('MSG_UPDATE_SUCCESS','all data successfully updated');
define('TITLE_OVIERVEW','overview');
define('MSG_ENTRY_DELETED','entry deleted');
define('TITLE_ENTRY_ADD','add entry');
define('TEXT_NOTES','annotations');

define('FIELD_POLAR','Polar (Slider)');
define('FIELD_CHECKBOX','Multiple Choice (Checkboxes)');
define('FIELD_TEXTAREA','Textfeild (max. %s characters)');
define('FIELD_DROPDOWN','Dropdown');

define('NAV_OVERVIEW','overview');
define('NAV_MY_ENTRIES','my entries');
define('NAV_EDIT_SURVEY','edit survey');
define('NAV_USER','users');
define('NAV_NEWS','news');
define('NAV_LOGOUT','logout');
define('NAV_LOGIN','login');
define('NAV_MY_PROFILE','my profile');
define('NAV_BREADCRUMB','yo are here: ');
define('NAV_HOME','home');

define('NAV_ADD','Hinzufügen');
define('NAV_EDIT','Bearbeiten');
define('NAV_DELETE','Löschen');
define('NAV_PREVIEW','Vorschau');

define('TITLE_NEWS','Information');

define('MAIL_TITLE_PASS_RESET', WORKSPACE_TITLE .' - Passwortänderung');
define('MAIL_TEXT_PASS_RESET', 'Diese E-Mail wurde gesendet, weil dein Passwort zurückgesetzt werden soll. Dein neues Passwort kannst Du unter folgendem Link eingeben: %s\n\nEinloggen kannst Du dich unter: ' . HOME_DIR);

define('MAIL_TITLE_INVITE', WORKSPACE_TITLE .' - Einladung zum Arbeitsbereich');
define('MAIL_TEXT_INVITE', 'dies ist eine Einladung zum Arbeitsbereich\nBitte nutze den folgenden Link, um dein Passwort festzulegen: %s\n\nEinloggen kannst Du dich danach unter: ' . HOME_DIR);

define('TEXT_MY_COMMENT','von mir');
define('TEXT_COMMENT_BY','von');
define('TEXT_CREATED','erstellt am');