<?php
/**
 * Federated Login for DokuWiki - ajax calls handler
 *
 * Enables your DokuWiki to provide users with
 * Hybrid OAuth + OpenId federated login.
 *
 * @copyright  2012 Aoi Karasu
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @link       http://www.dokuwiki.org/plugin:fedauth
 * @author     Aoi Karasu <aoikarasu@gmail.com>
 */

//fix for Opera XMLHttpRequests
if(!count($_POST) && !empty($HTTP_RAW_POST_DATA)){
  parse_str($HTTP_RAW_POST_DATA, $_POST);
}

if(!defined('DOKU_INC_LOC')) define('DOKU_INC_LOC',dirname(__FILE__).'/../../../');
require_once(DOKU_INC_LOC.'inc/init.php');
//close session
session_write_close();

if(!auth_isadmin()) die('for admins only');
if(!checkSecurityToken()) die('CRSF Attack');

$ID = getID();

$fa = plugin_load('admin','fedauth');
$fa->handle();

$ajax = $_REQUEST['ajax'];
header('Content-Type: text/html; charset=utf-8');

$fa->ajax();

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */
