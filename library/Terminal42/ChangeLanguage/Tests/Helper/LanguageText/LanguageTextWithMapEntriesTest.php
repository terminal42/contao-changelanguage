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

class LanguageTextWithMapEntriesTest extends ContaoTestCase
{
    /**
     * @var LanguageText
     */
    private $languageText;

    /**
     * @var NavigationItem[]
     */
    private $items;

    /**
     * @var array this defined both the displayed text AND the sorting order
     */
    private $map = [
        'en'    => 'International',
        'de-CH' => 'Switzerland (German)',
        'de'    => 'Germany',
        'fr-FR' => 'France',
        'pl'    => 'Poland',
    ];

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();

        $this->languageText = new LanguageText($this->map);

        $fooComId = $this->createRootPage('foo.com', 'en');
        $barChId = $this->createRootPage('bar.ch', 'de-CH');
        $bazDeId = $this->createRootPage('baz.de', 'de');
        $helloFrId = $this->createRootPage('hello.fr', 'fr-FR');
        $worldPlId = $this->createRootPage('world.pl', 'pl');

        $fooCom = PageModel::findById($fooComId);
        $barCh = PageModel::findById($barChId);
        $bazDe = PageModel::findById($bazDeId);
        $helloFr = PageModel::findById($helloFrId);
        $worldPl = PageModel::findById($worldPlId);

        //items do not get added in "correct" order on purpose to test the sorting
        $this->items[] = new NavigationItem($barCh);
        $this->items[] = new NavigationItem($worldPl);
        $this->items[] = new NavigationItem($fooCom);
        $this->items[] = new NavigationItem($helloFr);
        $this->items[] = new NavigationItem($bazDe);
    }

    public function testOrderNavigationItemsResultsInExpectedOrder()
    {
        $this->languageText->orderNavigationItems($this->items);
        $keys = array_keys($this->map);

        foreach ($this->items as $item) {
            //items order should be equal to the order in the map which was passed to LanguageText
            $this->assertEquals(array_shift($keys), $item->getLanguageTag());
        }
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
