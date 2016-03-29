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
 * Return if the module is not active
 */
if (!in_array('faq', \ModuleLoader::getActive()))
{
    return;
}


/**
 * Palettes
 */
$GLOBALS['TL_DCA']['tl_faq_category']['palettes']['default'] = str_replace('jumpTo;', 'jumpTo;{language_legend},language,master;', $GLOBALS['TL_DCA']['tl_faq_category']['palettes']['default']);


/**
 * Fields
 */
$GLOBALS['TL_DCA']['tl_faq_category']['fields']['master'] = array
(
    'label'                   => &$GLOBALS['TL_LANG']['tl_faq_category']['master'],
    'exclude'                 => true,
    'inputType'               => 'select',
    'options_callback'        => array('tl_faq_category_language', 'getCategories'),
    'eval'                    => array('includeBlankOption'=>true, 'blankOptionLabel'=>&$GLOBALS['TL_LANG']['tl_faq_category']['isMaster']),
    'sql'                     => "int(10) unsigned NOT NULL default '0'"
);

$GLOBALS['TL_DCA']['tl_faq_category']['fields']['language'] = array
(
    'label'                   => &$GLOBALS['TL_LANG']['tl_faq_category']['language'],
    'exclude'                 => true,
    'search'                  => true,
    'filter'                  => true,
    'inputType'               => 'text',
    'eval'                    => array('mandatory'=>true, 'rgxp'=>'language', 'maxlength'=>5, 'nospace'=>true, 'tl_class'=>'w50'),
    'sql'                     => "varchar(5) NOT NULL default ''"
);


class tl_faq_category_language extends Backend
{

    /**
     * Get an array of possible categories
     */
    public function getCategories(DataContainer $dc)
    {
        $arrCalendars = array();
        $objCategories = $this->Database->prepare("SELECT * FROM tl_faq_category WHERE language!=? AND id!=? AND master=0 ORDER BY title")->execute($dc->activeRecord->language, $dc->id);

        while( $objCategories->next() )
        {
            $arrCalendars[$objCategories->id] = sprintf($GLOBALS['TL_LANG']['tl_faq_category']['isSlave'], $objCategories->title);
        }

        return $arrCalendars;
    }
}
