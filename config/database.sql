-- **********************************************************
-- *                                                        *
-- * IMPORTANT NOTE                                         *
-- *                                                        *
-- * Do not import this file manually but use the TYPOlight *
-- * install tool to create and maintain database tables!   *
-- *                                                        *
-- **********************************************************


--
-- Table `tl_module`
--

CREATE TABLE `tl_module` (
  `hideActiveLanguage` char(1) NOT NULL default '',
  `hideNoFallback` char(1) NOT NULL default '',
  `keepUrlParams` char(1) NOT NULL default '',
  `customLanguage` char(1) NOT NULL default '',
  `customLanguageText` mediumtext NULL,
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


----------------------------------------------------------

--
-- Table `tl_page`
--

CREATE TABLE `tl_page` (
  `languageRoot` int(10) unsigned NOT NULL default '0',
  `languageMain` int(10) unsigned NOT NULL default '0',
  KEY `type` (`type`),
  KEY `languageMain` (`languageMain`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

----------------------------------------------------------

--
-- Table `tl_article`
--

CREATE TABLE `tl_article` (
  `languageMain` int(10) unsigned NOT NULL default '0',
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

