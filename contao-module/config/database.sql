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

