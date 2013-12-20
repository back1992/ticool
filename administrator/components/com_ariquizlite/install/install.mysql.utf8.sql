CREATE TABLE IF NOT EXISTS `#__arigenerictemplate` (
	`TemplateId` int(10) unsigned NOT NULL auto_increment,
	`BaseTemplateId` int(11) NOT NULL,
	`TemplateName` varchar(255) NOT NULL,
	`Value` text,
	`Created` datetime NOT NULL,
	`CreatedBy` int(10) unsigned NOT NULL default '0',
	`Modified` datetime default NULL,
	`ModifiedBy` int(10) unsigned default '0',
	PRIMARY KEY  (`TemplateId`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
				
CREATE TABLE IF NOT EXISTS `#__arigenerictemplatebase` (
	`BaseTemplateId` int(10) unsigned NOT NULL auto_increment,
	`DefaultValue` text,
	`TemplateDescription` text,
	`Group` varchar(255) NOT NULL,
	PRIMARY KEY  (`BaseTemplateId`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__arigenerictemplateentitymap` (
	`TemplateId` int(11) NOT NULL,
	`EntityName` varchar(255) NOT NULL,
	`TemplateType` varchar(255) NOT NULL,
	`EntityId` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__arigenerictemplateparam` (
	`ParamId` int(10) unsigned NOT NULL auto_increment,
	`BaseTemplateId` int(11) NOT NULL,
	`ParamName` varchar(255) NOT NULL,
	`ParamDescription` text,
	`ParamType` varchar(255) default NULL,
	PRIMARY KEY  (`ParamId`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__ariquiz` (
  `QuizId` int(10) unsigned NOT NULL auto_increment,
  `QuizName` varchar(255) NOT NULL,
  `CreatedBy` int(10) unsigned NOT NULL,
  `Created` datetime NOT NULL,
  `ModifiedBy` int(10) unsigned default NULL,
  `Modified` datetime default NULL,
  `AccessType` int(10) unsigned default NULL,
  `Status` int(10) unsigned NOT NULL,
  `TotalTime` int(10) unsigned default NULL,
  `PassedScore` int(10) unsigned NOT NULL default '0',
  `QuestionCount` int(10) unsigned default NULL,
  `QuestionTime` int(10) unsigned default NULL,
  `Description` longtext,
  `CanSkip` tinyint(1) unsigned NOT NULL default '0',
  `RandomQuestion` tinyint(1) unsigned NOT NULL default '0',
  `LagTime` int(11) unsigned NOT NULL default '0',
  `AttemptCount` int(11) unsigned NOT NULL default '0',
  `CssTemplateId` int(11) unsigned NOT NULL default '0',
  `AdminEmail` text,
  PRIMARY KEY  (`QuizId`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__ariquizaccess` (
	`QuizId` int(10) unsigned NOT NULL,
	`GroupId` int(10) unsigned NOT NULL,
	PRIMARY KEY  (`QuizId`,`GroupId`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__ariquizcategory` (
	`CategoryId` int(10) unsigned NOT NULL auto_increment,
	`CategoryName` varchar(255) NOT NULL,
	`Description` text NOT NULL,
	`Created` datetime NOT NULL,
	`CreatedBy` int(10) unsigned NOT NULL,
	`Modified` datetime default NULL,
	`ModifiedBy` int(10) unsigned default NULL,
	PRIMARY KEY  (`CategoryId`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__ariquizconfig` (
	`ParamName` varchar(100) NOT NULL,
	`ParamValue` varchar(255) NOT NULL,
	PRIMARY KEY  (`ParamName`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__ariquizquestion` (
	`QuestionId` int(10) unsigned NOT NULL auto_increment,
	`QuizId` int(10) unsigned NOT NULL,
	`QuestionVersionId` bigint(20) default NULL,
	`Created` datetime NOT NULL,
	`CreatedBy` int(10) unsigned NOT NULL,
	`Modified` datetime default NULL,
	`ModifiedBy` int(10) unsigned default NULL,
	`Status` int(11) unsigned NOT NULL,
	`QuestionIndex` int(11) unsigned default NULL,
	PRIMARY KEY  (`QuestionId`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__ariquizquestioncategory` (
	`QuestionCategoryId` int(10) unsigned NOT NULL auto_increment,
	`QuizId` int(10) unsigned NOT NULL,
	`CategoryName` varchar(255) NOT NULL,
	`Description` text,
	`Created` datetime NOT NULL,
	`CreatedBy` int(10) unsigned NOT NULL,
	`Modified` datetime default NULL,
	`ModifiedBy` int(10) unsigned default NULL,
	`QuestionCount` int(10) unsigned default NULL,
	`QuestionTime` int(10) unsigned default NULL,
	`RandomQuestion` tinyint(1) unsigned NOT NULL default '0',
	`Status` int(11) unsigned NOT NULL default '1',
	PRIMARY KEY  (`QuestionCategoryId`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__ariquizquestiontemplate` (
  `TemplateId` int(10) unsigned NOT NULL auto_increment,
  `TemplateName` varchar(255) NOT NULL,
  `QuestionTypeId` int(11) NOT NULL,
  `Data` longtext,
  `Created` datetime NOT NULL,
  `CreatedBy` int(11) unsigned NOT NULL,
  `Modified` datetime default NULL,
  `ModifiedBy` int(11) unsigned default NULL,
  `DisableValidation` tinyint(1) unsigned NOT NULL default '0',
  PRIMARY KEY  (`TemplateId`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__ariquizquestiontype` (
  `QuestionTypeId` int(10) unsigned NOT NULL auto_increment,
  `QuestionType` varchar(255) NOT NULL,
  `ClassName` varchar(255) NOT NULL,
  `Default` tinyint(1) unsigned NOT NULL,
  `CanHaveTemplate` tinyint(1) unsigned NOT NULL default '1',
  `TypeOrder` int(11) unsigned NOT NULL default '0',
  PRIMARY KEY  (`QuestionTypeId`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__ariquizquestionversion` (
  `QuestionVersionId` bigint(20) unsigned NOT NULL auto_increment,
  `QuestionId` int(10) unsigned NOT NULL,
  `QuestionCategoryId` int(10) unsigned default NULL,
  `QuestionTime` int(10) unsigned default NULL,
  `QuestionTypeId` int(11) unsigned NOT NULL,
  `Question` text NOT NULL,
  `HashCode` char(32) NOT NULL,
  `Created` datetime NOT NULL,
  `CreatedBy` int(10) unsigned NOT NULL,
  `Data` longtext NOT NULL,
  `ShowAsImage` tinyint(1) unsigned NOT NULL default '0',
  `Score` int(11) unsigned NOT NULL,
  PRIMARY KEY  (`QuestionVersionId`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__ariquizquizcategory` (
	`QuizId` int(10) unsigned NOT NULL,
	`CategoryId` int(10) unsigned NOT NULL,
	PRIMARY KEY  (`QuizId`,`CategoryId`),
	UNIQUE KEY `SSCUniquePair` (`QuizId`,`CategoryId`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__ariquizstatistics` (
	`StatisticsId` bigint(20) unsigned NOT NULL auto_increment,
	`QuestionVersionId` bigint(20) unsigned NOT NULL,
	`StatisticsInfoId` bigint(20) NOT NULL,
	`Data` longtext,
	`StartDate` datetime default NULL,
	`EndDate` datetime default NULL,
	`SkipDate` datetime default NULL,
	`SkipCount` int(11) unsigned NOT NULL default '0',
	`UsedTime` int(11) unsigned NOT NULL default '0',
	`QuestionIndex` int(10) unsigned NOT NULL,
	`Score` int(10) unsigned default NULL,
	`QuestionTime` int(10) unsigned default NULL,
	`QuestionCategoryId` int(10) unsigned NOT NULL,
	`IpAddress` int(10) unsigned default NULL,
	PRIMARY KEY  (`StatisticsId`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__ariquizstatisticsinfo` (
	`StatisticsInfoId` bigint(20) unsigned NOT NULL auto_increment,
	`QuizId` int(10) unsigned NOT NULL,
	`UserId` int(10) unsigned default NULL,
	`Status` set('Prepare','Process','Finished') NOT NULL default 'Process',
	`TicketId` char(32) NOT NULL,
	`StartDate` datetime default NULL,
	`EndDate` datetime default NULL,
	`PassedScore` int(11) unsigned NOT NULL default '0',
	`UserScore` int(11) unsigned NOT NULL default '0',
	`MaxScore` int(11) unsigned NOT NULL default '0',
	`Passed` tinyint(1) unsigned NOT NULL default '0',
	`CreatedDate` datetime NOT NULL,
	`QuestionCount` int(11) unsigned NOT NULL default '0',
	`TotalTime` int(10) unsigned default NULL,
	`ResultEmailed` tinyint(1) unsigned NOT NULL default '0',
	PRIMARY KEY  (`StatisticsInfoId`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__ariquizfile` (
  `FileId` int(11) unsigned NOT NULL auto_increment,
  `Content` longblob NOT NULL,
  `FileName` varchar(255) NOT NULL,
  `Group` varchar(255) NOT NULL,
  `Size` int(11) unsigned NOT NULL,
  `Description` varchar(255) default NULL,
  `ShortDescription` varchar(255) default NULL,
  `Created` datetime NOT NULL,
  `CreatedBy` int(11) unsigned NOT NULL default '0',
  `Modified` datetime default NULL,
  `ModifiedBy` int(11) unsigned default NULL,
  `Extension` varchar(255) NOT NULL,
  `Height` int(11) unsigned NOT NULL default '0',
  `Width` int(11) unsigned NOT NULL default '0',
  `Flags` int(11) unsigned NOT NULL default '0',
  PRIMARY KEY  (`FileId`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `#__arigenerictemplate` (`TemplateId`, `BaseTemplateId`, `TemplateName`, `Value`, `Created`, `CreatedBy`, `Modified`, `ModifiedBy`) VALUES  (1,1,'Base Template','<div align=\"center\"><p>Dear, {$UserName}! You have <span style=\"text-transform: lowercase;\">{$Passed}</span> quiz \'{$QuizName}\'. </p></div> <table border=\"0\" style=\"border: 1px solid #cccccc; width: 100%\"> \t<tbody><tr> \t\t<th class=\"sectiontableheader\" colspan=\"2\" style=\"text-align: center\">Quiz Result</th> \t</tr> \t<tr> \t\t<td style=\"text-align: left; width: 50%; white-space: nowrap\">Result :</td> \t\t<td style=\"text-align: left\">{$UserScore} / {$MaxScore}</td> \t</tr> \t<tr> \t\t<td style=\"text-align: left; white-space: nowrap\">Percentage :</td> \t\t<td style=\"text-align: left\">{$PercentScore} %</td> \t</tr> \t<tr> \t\t<td style=\"text-align: left; white-space: nowrap\">Passed :</td> \t\t<td style=\"text-align: left\">{$Passed}</td> \t</tr> \t<tr> \t\t<td style=\"text-align: left; white-space: nowrap\">Start Date :</td> \t\t<td style=\"text-align: left\">{$StartDate}</td> \t</tr> \t<tr> \t\t<td style=\"text-align: left; white-space: nowrap\">End Date :</td> \t\t<td style=\"text-align: left\">{$EndDate}</td> \t</tr> \t<tr> \t\t<td style=\"text-align: left; white-space: nowrap\">Spent Time :</td> \t\t<td style=\"text-align: left\">{$SpentTime}</td> \t</tr> <tr><td>Passed Percentage :<br /></td><td>{$PassedScore} %<br /></td></tr></tbody></table>','2008-02-10 10:52:47',62,'2008-02-12 09:58:18',62)
	ON DUPLICATE KEY UPDATE BaseTemplateId=BaseTemplateId;

INSERT INTO `#__arigenerictemplatebase` (`BaseTemplateId`, `DefaultValue`, `TemplateDescription`, `Group`) VALUES 
	(1,NULL,'Using for','QuizResult')
	ON DUPLICATE KEY UPDATE BaseTemplateId=BaseTemplateId;

INSERT INTO `#__arigenerictemplateparam` (`ParamId`, `BaseTemplateId`, `ParamName`, `ParamDescription`, `ParamType`) VALUES 
	(1,1,'UserName','Display user name',NULL),
	(2,1,'SpentTime','Display spent time',NULL),
	(3,1,'StartDate','Display start date',NULL),
	(5,1,'QuizName','Display quiz name',NULL),
	(6,1,'MaxScore','Display max score',NULL),
	(7,1,'UserScore','Display user score',NULL),
	(8,1,'PercentScore','Display percent score',NULL),
	(9,1,'PassedScore','Display passed score',NULL),
	(10,1,'Passed','Display passed',NULL),
	(11,1,'EndDate','Display end date',NULL)
	ON DUPLICATE KEY UPDATE BaseTemplateId=BaseTemplateId;

INSERT INTO `#__ariquizquestiontemplate` (`TemplateId`, `TemplateName`, `QuestionTypeId`, `Data`, `Created`, `CreatedBy`, `Modified`, `ModifiedBy`, `DisableValidation`) VALUES 
	(1,'Yes / No',1,'\n<answers>\n\t<answer id=\"4772579e93e2e8.32874767\">Yes</answer>\n\t<answer id=\"4772579e93e5f1.02150736\" correct=\"true\">No</answer>\n</answers>','2007-12-26 13:14:25',62,'2008-02-02 10:39:13',62,1)
	ON DUPLICATE KEY UPDATE TemplateId=TemplateId;

INSERT INTO `#__ariquizquestiontype` (`QuestionTypeId`, `QuestionType`, `ClassName`, `Default`, `CanHaveTemplate`, `TypeOrder`) VALUES 
	  (1,'Single Question','SingleQuestion',1,1,0),
	  (2,'Multiple Question','MultipleQuestion',0,1,0),
	  (4,'Free Text','FreeTextQuestion',0,1,0)
	ON DUPLICATE KEY UPDATE QuestionTypeId=QuestionTypeId;