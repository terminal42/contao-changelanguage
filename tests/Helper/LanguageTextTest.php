<?php

declare(strict_types=1);

namespace Terminal42\ChangeLanguage\Tests\Helper;

use Contao\CoreBundle\InsertTag\InsertTagParser;
use Contao\PageModel;
use Contao\System;
use Contao\TestCase\ContaoTestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Terminal42\ChangeLanguage\Helper\LanguageText;
use Terminal42\ChangeLanguage\Navigation\NavigationItem;

class LanguageTextTest extends ContaoTestCase
{
    protected function setUp(): void
    {
        $insertTagParser = $this->createMock(InsertTagParser::class);
        $insertTagParser
            ->method('replace')
            ->willReturnArgument(0)
        ;

        $container = $this->createMock(ContainerInterface::class);
        $container
            ->method('get')
            ->with('contao.insert_tag.parser')
            ->willReturn($insertTagParser)
        ;

        System::setContainer($container);
    }

    public function testHasLanguageInMap(): void
    {
        $map = [
            'en' => 'International',
            'de' => 'Germany',
            'de_CH' => 'Switzerland (German)',
        ];

        $languageText = new LanguageText($map);

        $this->assertTrue($languageText->has('en'));
        $this->assertTrue($languageText->has('de'));
        $this->assertTrue($languageText->has('de_CH'));
        $this->assertFalse($languageText->has('fr'));
    }

    public function testCanSetLabelForLanguage(): void
    {
        $languageText = new LanguageText();

        $this->assertFalse($languageText->has('en'));

        $languageText->set('en', 'English');

        $this->assertTrue($languageText->has('en'));
    }

    public function testReturnsLabelForLanguage(): void
    {
        $languageText = new LanguageText(['en' => 'English']);

        $this->assertSame('English', $languageText->get('en'));
    }

    public function testReturnsUppercaseLanguageWhenNotInMap(): void
    {
        $languageText = new LanguageText();

        $this->assertSame('EN', $languageText->get('en'));
    }

    public function testOrdersNavigationItemsAccordingToCustomMap(): void
    {
        $map = [
            'en' => 'International',
            'de_CH' => 'Switzerland (German)',
            'de' => 'Germany',
            'fr_FR' => 'France',
            'pl' => 'Poland',
        ];

        $languageText = new LanguageText($map);

        // items do not get added in "correct" order on purpose to test the sorting
        $items = [];
        $items[] = new NavigationItem($this->createRootPage('bar.ch', 'de_CH'));
        $items[] = new NavigationItem($this->createRootPage('world.pl', 'pl'));
        $items[] = new NavigationItem($this->createRootPage('foo.com', 'en'));
        $items[] = new NavigationItem($this->createRootPage('hello.fr', 'fr_FR'));
        $items[] = new NavigationItem($this->createRootPage('baz.de', 'de'));

        $languageText->orderNavigationItems($items);
        $keys = array_keys($map);

        foreach ($items as $i => $item) {
            // items order should be equal to the order in the map which was passed to LanguageText
            $this->assertSame($keys[$i], $item->getLocaleId());
        }
    }

    public function testIgnoresOrderIfMapIsEmpty(): void
    {
        $languageText = new LanguageText();

        /** @var array<NavigationItem> $items */
        $items = [
            new NavigationItem($this->createRootPage('foo.com', 'en')),
            new NavigationItem($this->createRootPage('bar.ch', 'de_CH')),
        ];

        $languageText->orderNavigationItems($items);

        $this->assertSame('en', $items[0]->getLocaleId());
        $this->assertSame('de_CH', $items[1]->getLocaleId());
    }

    public function testIsCreatedFromOptionWizard(): void
    {
        $config = [
            ['label' => 'English', 'value' => 'en'],
        ];

        $languageText = LanguageText::createFromOptionWizard(serialize($config));

        $this->assertTrue($languageText->has('en'));
    }

    public function testIsCreatedFromEmptyOptionWizard(): void
    {
        $languageText = LanguageText::createFromOptionWizard('');

        $this->assertFalse($languageText->has('en'));
    }

    public function testIgnoresEmptyOptionWizardRows(): void
    {
        $config = [
            ['label' => 'English', 'value' => 'en'],
            ['label' => '', 'value' => 'de'],
            ['label' => 'French', 'value' => ''],
        ];

        $languageText = LanguageText::createFromOptionWizard(serialize($config));

        $this->assertTrue($languageText->has('en'));
        $this->assertFalse($languageText->has('de'));
        $this->assertFalse($languageText->has('fr'));
    }

    /**
     * @return PageModel&MockObject
     */
    private function createRootPage(string $dns, string $language): PageModel
    {
        $pageModel = $this->mockClassWithProperties(PageModel::class, [
            'type' => 'root',
            'title' => 'foobar',
            'dns' => $dns,
            'language' => $language,
            'published' => '1',
        ]);

        $pageModel
            ->method('loadDetails')
            ->willReturnSelf()
        ;

        return $pageModel;
    }
}
