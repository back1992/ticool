<?php
class JConfig {
	public $offline = '0';
	public $offline_message = '网站在维护中。<br /> 请稍候访问。';
	public $display_offline_message = '1';
	public $offline_image = '';
	public $sitename = 'quiz';
	public $editor = 'tinymce';
	public $captcha = '0';
	public $list_limit = '20';
	public $access = '1';
	public $debug = '0';
	public $debug_lang = '0';
	public $dbtype = 'mysqli';
	public $host = 'localhost';
	public $user = 'root';
	public $password = 'Joomla8';
	public $db = 'joomla';
	public $dbprefix = 'jos_';
	public $live_site = '';
	public $secret = 'A4eIYRFy3aQFyD3O';
	public $gzip = '0';
	public $error_reporting = 'default';
	public $helpurl = 'http://help.joomla.org/proxy/index.php?option=com_help&amp;keyref=Help{major}{minor}:{keyref}';
	public $ftp_host = '';
	public $ftp_port = '';
	public $ftp_user = '';
	public $ftp_pass = '';
	public $ftp_root = '';
	public $ftp_enable = '';
	public $offset = 'UTC';
	public $mailonline = '1';
	public $mailer = 'mail';
	public $mailfrom = 'back1992@gmail.com';
	public $fromname = 'quiz';
	public $sendmail = '/usr/sbin/sendmail';
	public $smtpauth = '0';
	public $smtpuser = '';
	public $smtppass = '';
	public $smtphost = 'localhost';
	public $smtpsecure = 'none';
	public $smtpport = '25';
	public $caching = '0';
	public $cache_handler = 'file';
	public $cachetime = '15';
	public $MetaDesc = 'quiz';
	public $MetaKeys = '';
	public $MetaTitle = '1';
	public $MetaAuthor = '1';
	public $MetaVersion = '0';
	public $robots = '';
	public $sef = '1';
	public $sef_rewrite = '0';
	public $sef_suffix = '0';
	public $unicodeslugs = '0';
	public $feed_limit = '10';
	public $log_path = '/var/www/html/joomla/logs';
	public $tmp_path = '/var/www/html/joomla/tmp';
	public $lifetime = '15';
	public $session_handler = 'database';
}