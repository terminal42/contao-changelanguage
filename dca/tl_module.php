<?php if (!defined('TL_ROOT')) die('You can not access this file directly!');

/**
 * TYPOlight webCMS
 * Copyright (C) 2005 Leo Feyer
 *
 * This program is free software: you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation, either
 * version 2.1 of the License, or (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.
 * 
 * You should have received a copy of the GNU Lesser General Public
 * License along with this program. If not, please visit the Free
 * Software Foundation website at http://www.gnu.org/licenses/.
 *
 * PHP version 5
 * @copyright  Felix Pfeiffer : Neue Medien 2008 / Andreas Schempp 2009
 * @author 	   Felix Pfeiffer <info@felixpfeiffer.com>, Andreas Schempp <andreas@schempp.ch>
 * @license	   LGPL
 */

 
/**
 * Palettes
 */
$GLOBALS['TL_DCA']['tl_module']['palettes']['__selector__'][] = 'customLanguage';
$GLOBALS['TL_DCA']['tl_module']['palettes']['changelanguage'] = '{title_legend},name,headline,type;{config_legend},useImages,hideActiveLanguage,keepUrlParams,customLanguage;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID,space';
$GLOBALS['TL_DCA']['tl_module']['subpalettes']['customLanguage'] = 'customLanguageText';
 
 
/**
 * Fields
 */
$GLOBALS['TL_DCA']['tl_module']['fields']['useImages'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_module']['useImages'],
	'exclude'                 => true,
	'inputType'               => 'checkbox',
	'eval'					  => array('tl_class'=>'w50'),
);

$GLOBALS['TL_DCA']['tl_module']['fields']['hideActiveLanguage'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_module']['hideActiveLanguage'],
	'exclude'                 => true,
	'inputType'               => 'checkbox',
	'eval'					  => array('tl_class'=>'w50'),
);

$GLOBALS['TL_DCA']['tl_module']['fields']['keepUrlParams'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_module']['keepUrlParams'],
	'exclude'                 => true,
	'inputType'               => 'checkbox',
	'eval'					  => array('tl_class'=>'w50'),
);

$GLOBALS['TL_DCA']['tl_module']['fields']['customLanguage'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_module']['customLanguage'],
	'exclude'                 => true,
	'inputType'               => 'checkbox',
	'eval'					  => array('submitOnChange'=>true, 'tl_class'=>'clr'),
);

$GLOBALS['TL_DCA']['tl_module']['fields']['customLanguageText'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_module']['customLanguageText'],
	'exclude'                 => true,
	'inputType'               => 'optionWizard',
	'eval'					  => array('allowHtml'=>true, 'tl_class'=>'long'),
);

