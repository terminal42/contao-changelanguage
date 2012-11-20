<?php

/**
 * Contao Open Source CMS
 * Copyright (C) 2005-2012 Leo Feyer
 *
 * Formerly known as TYPOlight Open Source CMS.
 *
 * This program is free software: you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation, either
 * version 3 of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this program. If not, please visit the Free
 * Software Foundation website at <http://www.gnu.org/licenses/>.
 *
 * PHP version 5
 * @copyright  Felix Pfeiffer 2008, terminal42 gmbh 2008-2012
 * @author     Andreas Schempp <andreas.schempp@terminal42.ch>
 * @author     Felix Pfeiffer <info@felixpfeiffer.com>
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 */

 
/**
 * Palettes
 */
$GLOBALS['TL_DCA']['tl_module']['palettes']['__selector__'][]		= 'customLanguage';
$GLOBALS['TL_DCA']['tl_module']['palettes']['changelanguage'] 		= '{title_legend},name,headline,type;{config_legend},hideActiveLanguage,hideNoFallback,keepUrlParams,customLanguage;{template_legend:hide},navigationTpl;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID,space';
$GLOBALS['TL_DCA']['tl_module']['palettes']['languageRedirect']		= '{title_legend},name,type;{protected_legend:hide},guests,protected';
$GLOBALS['TL_DCA']['tl_module']['subpalettes']['customLanguage']	= 'customLanguageText';
 
 
/**
 * Fields
 */
$GLOBALS['TL_DCA']['tl_module']['fields']['hideActiveLanguage'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_module']['hideActiveLanguage'],
	'exclude'                 => true,
	'inputType'               => 'checkbox',
	'eval'					  => array('tl_class'=>'w50'),
);

$GLOBALS['TL_DCA']['tl_module']['fields']['hideNoFallback'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_module']['hideNoFallback'],
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

