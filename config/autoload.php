<?php

/**
 * changelanguage Extension for Contao Open Source CMS
 *
 * @copyright  Copyright (c) 2008-2016, terminal42 gmbh
 * @author     terminal42 gmbh <info@terminal42.ch>
 * @license    http://opensource.org/licenses/lgpl-3.0.html LGPL
 * @link       http://github.com/terminal42/contao-changelanguage
 */


/**
 * Register PSR-0 namespaces
 */
if (class_exists('NamespaceClassLoader')) {
    NamespaceClassLoader::add('Terminal42\ChangeLanguage', 'system/modules/changelanguage/library');
}

/**
 * Register the templates
 */
TemplateLoader::addFiles(array
(
    'mod_changelanguage'           => 'system/modules/changelanguage/templates',
    'nav_dropdown'                 => 'system/modules/changelanguage/templates',
    'block_alternate_links'        => 'system/modules/changelanguage/templates',
));
