## Survey Workspace

This framework basically let's you create a survey on a specific topic. It is a collaborative workspace with ACL, UserManagement, a really simple News System and some feedback/comment function.  

## About this Framework

The idea came up in a seminar/class back in 2008 where we tried to evaluate and analyze different avatar systems in virtual worlds.

You can see a demo of this framework on https://avatare.pixelspace.org/ with the following account

user: demo@pixelspace.org

pass: demo

## Installation

1) Download the Package

2) Import the database tables from survey.sql

3) create a user - you need to do this manually since the tool needs some further development

4) set the db_config.inc.php in inc/cfg/ with your parameters and desired table names.

## Requirements

* [PHP 5.2+] (http://www.php.net)
* [MySQL 4.0+] (http://www.mysql.com/)

## Note

This Tool is far away from being perfect, it lacks of several things e.g. better session handling, multi language support, a handy installer and even a code proper documentation and so on. 
Also, please consider some security issues as well, since the idea and many parts of the code are from 2008. Feel free to build upon this framework and use it for your purpose.
 
 
