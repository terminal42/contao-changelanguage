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
 * Prevent DB fields to be added to an empty DCA
 */
if (in_array('calendar', \ModuleLoader::getActive(), true))
{

    /**
     * Palettes
     */
    $GLOBALS['TL_DCA']['tl_calendar']['palettes']['default'] = str_replace('jumpTo;', 'jumpTo;{language_legend},language,master;', $GLOBALS['TL_DCA']['tl_calendar']['palettes']['default']);


    /**
     * Fields
     */
    $GLOBALS['TL_DCA']['tl_calendar']['fields']['master'] = array
    (
        'label'                   => &$GLOBALS['TL_LANG']['tl_calendar']['master'],
        'exclude'                 => true,
        'inputType'               => 'select',
        'options_callback'        => array('Terminal42\ChangeLanguage\DataContainer\Calendar', 'getCalendars'),
        'eval'                    => array('includeBlankOption'=>true, 'blankOptionLabel'=>&$GLOBALS['TL_LANG']['tl_calendar']['isMaster']),
        'sql'                     => "int(10) unsigned NOT NULL default '0'"
    );

    $GLOBALS['TL_DCA']['tl_calendar']['fields']['language'] = array
    (
        'label'                   => &$GLOBALS['TL_LANG']['tl_calendar']['language'],
        'exclude'                 => true,
        'search'                  => true,
        'filter'                  => true,
        'inputType'               => 'text',
        'eval'                    => array('mandatory'=>true, 'rgxp'=>'language', 'maxlength'=>5, 'nospace'=>true, 'tl_class'=>'w50'),
        'sql'                     => "varchar(5) NOT NULL default ''"
    );

}
