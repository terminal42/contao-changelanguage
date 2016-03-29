<?php

/**
 * changelanguage Extension for Contao Open Source CMS
 *
 * @copyright  Copyright (c) 2008-2016, terminal42 gmbh
 * @author     terminal42 gmbh <info@terminal42.ch>
 * @license    http://opensource.org/licenses/lgpl-3.0.html LGPL
 * @link       http://github.com/terminal42/contao-changelanguage
 */


class ModuleLanguageRedirect extends Module
{

    /**
     * Module does not output anything...
     * Redirect if the user is logged in
     * @return string
     */
    public function generate()
    {
        if (TL_MODE == 'BE')
        {
            $objTemplate = new BackendTemplate('be_wildcard');
            $objTemplate->wildcard = '### LANGUAGE REDIRECT ###';
            $objTemplate->title = $this->headline;
            $objTemplate->id = $this->id;
            $objTemplate->link = $this->name;
            $objTemplate->href = 'contao/main.php?do=themes&amp;table=tl_module&amp;act=edit&amp;id=' . $this->id;

            return $objTemplate->parse();
        }

        // If user is logged in, redirect him
        if (FE_USER_LOGGED_IN && !BE_USER_LOGGED_IN)
        {
            $this->import('FrontendUser', 'User');

            // try to switch the language/page
            if ($this->User->language != $GLOBALS['TL_LANGUAGE'])
            {
                global $objPage;
                $mainLanguageID = $objPage->languageMain != 0 ? $objPage->languageMain : $objPage->id;
                $objPages =  $this->Database->prepare("SELECT * FROM tl_page WHERE languageMain=? OR id=? AND published=?")
                                            ->execute($mainLanguageID, $mainLanguageID, 1);

                while( $objPages->next() )
                {
                    // redirect
                    if ($objPages->language == $this->User->language)
                    {
                        $strParam = '';
                        $strGet = '?';
                        foreach( $_GET as $key => $value )
                        {
                            switch( $key )
                            {
                                case 'page':
                                case 'keywords':
                                    $strGet .= $key.'='.$value.'&';
                                    break;

                                default:
                                    $strParam .= '/'.$key.'/'.$value;
                            }
                        }

                        $this->redirect($this->generateFrontendUrl($objPages->row(), $strParam).$strGet);
                    }
                }
            }
        }

        // if user is not logged in, we have the correct language, or no page exists, we do nothing
        // assume TYPOlight has found the right language...
        return '';
    }


    /**
     * Not in use, but must be declared because parent method is abstract
     */
    protected function compile() {}
}

