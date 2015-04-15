<?php
/**
 * @category Webinse
 * @package Webinse_All
 * @author Dmitriy Perederiy <perederiy1993@yandex.ua>
 */
$link = mysql_connect('localhost', 'root', 'admin123');
$db_selected = mysql_select_db('ashop_db', $link);
$q = mysql_query("DELETE FROM social_info ");
echo $q;