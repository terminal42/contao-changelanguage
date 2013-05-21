
changelanguage
==============

### Version 2.2.0 stable (2013-05-21) ###

- Added support for auto_item parameter


### Version 2.1.1 stable (2012-11-20) ###

- Fixed exception in Contao 3 (#6)
- Updated copyright statement


### Version 2.1.0 stable (2012-11-09) ###

- Added support for Contao 3
- Option to hide languages without direct fallback
- Redirect and forward pages can now have fallback assigned
- Can now assign translations of articles (for article teasers)
- Added hreflang=".." to the navigation links
- Automatically inject <link rel="alternate" hreflang=".."> into the page header for direct fallbacks
- Added compatibility with Avisota
- Fixed issues with language parameter if no direct fallback was available (#4)


### Version 2.0.3 stable (2012-02-20) ###

- Fixed issue in Contao 2.11 if the pag eon the first level did not have a fallback


### Version 2.0.2 stable (2012-01-16) ###

- Added support for unknown page types (e.g. folderpage)
- Now supports Contao 2.11 "language in URL" feature


### Version 2.0.1 stable (2011-11-20) ###

- Improved: Now supports Contao 2.11 "language in URL" feature
- Improved: Added support for unknown page types (e.g. folderpage)
- Fixed: forward & redirect pages should not have a fallback assigned
- Fixed: Custom sorting did not work, added language to array (#2369 & #1917)


### Version 2.0.0 stable (2011-08-16) ###

- Switched to nav_default templates for navigation listing
- Added hook to customize the fallback URLs (Ticket #1460)
- Added css class to li and anchor/span tag to match the contao standard
- Added .html5 and .xhtml templates for Contao 2.10
- Moved module "language redirect" from extension "redirect" to "changelanguage"
- Fixed issue in "edit all" mode
- Removed compatibility with Contao 2.7
- Removed country flag icons, they can be added using CSS background images


### Version 1.0.1 stable (2010-12-24) ###

- fix a problem with multiple languages domains


### Version 1.0.0 stable (2010-06-02) ###

- allow the user to change the language in a multi domain setup


### Version 0.7.2 stable (2010-02-17) ###

- fix a problem with TYPOlight 2.8


### Version 0.7.1 stable (2009-11-30) ###

- improve the position of the select menu
- add support for TYPOlight 2.8
- add compatibility to the extension "cacheicon"


### Version 0.7.0 stable (2009-10-14) ###

- generate the active element in a <span> tag
- see in the page tree witch page misses a fallback language
- add the css class "nofallback"
- add with and hidth to the images in the template (flaggs)


### Version 0.6.3 stable (2009-07-06) ###

- change the language order (own language texts)


### Version 0.6.2 stable (2009-05-04) ###

- add support for TYPOlight 2.7
- keep the url parameters on change
- add some code documentation
- optimize the code base


### Version 0.6.1 stable (2009-03-31) ###

- set the sub pages as 0


### Version 0.6.0 stable (2009-09-20) ###

- get the parent page as fallback
- add support for the extension "newslanguage"
- the imput field allows now html code
- add the default palette to tl_module


### Version 0.5.4 stable (2009-01-30) ###

- fix the wrong fallback page
- fix the language order (same as the page tree)
- fix a wrong date format


### Version 0.5.3 stable (2008-11-03) ###
- Initial stable release

