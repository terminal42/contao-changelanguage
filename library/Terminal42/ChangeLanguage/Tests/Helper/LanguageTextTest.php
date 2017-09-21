<?php

/*
 * changelanguage Extension for Contao Open Source CMS
 *
 * @copyright  Copyright (c) CTS GmbH
 * @author     CTS GmbH <info@cts-media.eu>
 * @license    http://opensource.org/licenses/lgpl-3.0.html LGPL
 * @link       http://github.com/terminal42/contao-changelanguage
 */

namespace Terminal42\ChangeLanguage\Tests\Helper;

use Contao\PageModel;
use Terminal42\ChangeLanguage\Helper\LanguageText;
use Terminal42\ChangeLanguage\Navigation\NavigationItem;
use Terminal42\ChangeLanguage\Tests\ContaoTestCase;

class LanguageTextTest extends ContaoTestCase
{

    public function testOrdersNavigationItemsAccordingToCustomMap()
    {
        $map = [
            'en'    => 'International',
            'de-CH' => 'Switzerland (German)',
            'de'    => 'Germany',
            'fr-FR' => 'France',
            'pl'    => 'Poland',
        ];

        $languageText = new LanguageText($map);

        $fooComId = $this->createRootPage('foo.com', 'en');
        $barChId = $this->createRootPage('bar.ch', 'de-CH');
        $bazDeId = $this->createRootPage('baz.de', 'de');
        $helloFrId = $this->createRootPage('hello.fr', 'fr-FR');
        $worldPlId = $this->createRootPage('world.pl', 'pl');

        //items do not get added in "correct" order on purpose to test the sorting
        $items = [];
        $items[] = new NavigationItem(PageModel::findById($barChId));
        $items[] = new NavigationItem(PageModel::findById($worldPlId));
        $items[] = new NavigationItem(PageModel::findById($fooComId));
        $items[] = new NavigationItem(PageModel::findById($helloFrId));
        $items[] = new NavigationItem(PageModel::findById($bazDeId));

        $languageText->orderNavigationItems($items);
        $keys = array_keys($map);

        foreach ($items as $i => $item) {
            //items order should be equal to the order in the map which was passed to LanguageText
            $this->assertEquals($keys[$i], $item->getLanguageTag());
        }
    }

    public function testHasLanguageInMap()
    {
        $map = [
            'en'    => 'International',
            'de'    => 'Germany',
            'de-CH' => 'Switzerland (German)',
        ];

        $languageText = new LanguageText($map);

        $this->assertTrue($languageText->has('en'));
        $this->assertTrue($languageText->has('de'));
        $this->assertTrue($languageText->has('de-CH'));
        $this->assertFalse($languageText->has('fr'));
    }

    private function createRootPage($dns, $language)
    {
        return $this->query("
            INSERT INTO tl_page 
            (type, title, dns, language, published) 
            VALUES 
            ('root', 'foobar', '$dns', '$language', '1')
        ");
    }
}
