<?php
defined('_JEXEC') or die('Restricted access');

global $option;

define('ARI_QUIZ_STATUS_ACTIVE', 1);
define('ARI_QUIZ_STATUS_INACTIVE', 2);
define('ARI_QUIZ_STATUS_DELETE', 4);

define('ARI_QUIZ_QUE_STATUS_ACTIVE', 1);
define('ARI_QUIZ_QUE_STATUS_DELETE', 2);

define('ARI_QUIZ_RESTEMPLATE_KEY', 'QuizResult');

define('ARI_QUIZ_ENTITY_KEY', 'AriQuiz');

// Quiz Text Templates
define('ARI_QUIZ_TT_SUCCESSFUL', 'QuizSuccessful');
define('ARI_QUIZ_TT_FAILED', 'QuizFailed');
define('ARI_QUIZ_TT_SUCCESSFULEMAIL', 'QuizSuccessfulEmail');
define('ARI_QUIZ_TT_FAILEDEMAIL', 'QuizFailedEmail');
define('ARI_QUIZ_TT_SUCCESSFULPRINT', 'QuizSuccessfulPrint');
define('ARI_QUIZ_TT_FAILEDPRINT', 'QuizFailedPrint');
define('ARI_QUIZ_ADMIN_MAIL', 'QuizAdminEmail');

define('ARI_ENTITY_GROUP', '_Entity');
define('ARI_QUESTIONENTITY_GROUP', '_QuestionEntity');

define('ARI_QUIZ_FILE_TEMPLATEGROUP', 'css');
define('ARI_QUIZ_FILE_HOTSPOTGROUP', 'hotspot');
define('ARI_QUIZ_FILE_TABLE', '#__ariquizfile');
define('ARI_QUIZ_FILE_LANGBACKEND', 'lbackend');
define('ARI_QUIZ_FILE_LANGFRONTEND', 'lfrontend');

define('ARI_QUIZ_CONFIG_VERSION', 'Version');
define('ARI_QUIZ_CONFIG_BLANG', 'BLang');
define('ARI_QUIZ_CONFIG_FLANG', 'FLang');

define('ARI_RESPONSE_CHARSET', 'UTF-8');
define('ARI_QUIZ_DB_CHARSET', 'UTF-8');

define('ARI_QUIZ_CACHE_DIR', JPATH_SITE . '/administrator/components/' . $option . '/cache/files/');
?>