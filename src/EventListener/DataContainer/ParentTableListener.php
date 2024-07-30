<?php

declare(strict_types=1);

namespace Terminal42\ChangeLanguage\EventListener\DataContainer;

use Contao\CoreBundle\DataContainer\PaletteManipulator;
use Contao\Database;
use Contao\DataContainer;
use Contao\PageModel;
use Terminal42\ChangeLanguage\PageFinder;

class ParentTableListener
{
    private string $table;

    public function __construct(string $table)
    {
        $this->table = $table;
    }

    public function register(): void
    {
        if (!isset($GLOBALS['TL_DCA'][$this->table]['palettes']['default'])) {
            return;
        }

        $GLOBALS['TL_DCA'][$this->table]['fields']['master'] = [
            'label' => &$GLOBALS['TL_LANG'][$this->table]['master'],
            'exclude' => true,
            'inputType' => 'select',
            'options_callback' => fn (DataContainer $dc) => $this->onMasterOptions($dc),
            'eval' => [
                'includeBlankOption' => true,
                'blankOptionLabel' => &$GLOBALS['TL_LANG'][$this->table]['isMaster'],
                'tl_class' => 'w50',
            ],
            'save_callback' => [function ($value, DataContainer $dc) {
                $this->validateMaster($value, $dc);

                return $value;
            }],
            'sql' => "int(10) unsigned NOT NULL default '0'",
            'relation' => ['type' => 'hasOne', 'table' => $this->table],
        ];

        PaletteManipulator::create()
            ->addLegend('language_legend', 'title_legend')
            ->addField('master', 'language_legend', PaletteManipulator::POSITION_APPEND)
            ->applyToPalette('default', $this->table)
        ;
    }

    /**
     * @return array<int|string, string>
     */
    public function onMasterOptions(DataContainer $dc): array
    {
        if (null === ($jumpTo = PageModel::findById($dc->activeRecord->jumpTo))) {
            return [];
        }

        $associated = [];
        $pageFinder = new PageFinder();

        foreach ($pageFinder->findAssociatedForPage($jumpTo, true) as $page) {
            $associated[] = $page->id;
        }

        if (0 === \count($associated)) {
            return [];
        }

        $options = [];
        $result = Database::getInstance()
            ->prepare('
                SELECT id, title
                FROM '.$this->table.'
                WHERE jumpTo IN ('.implode(',', $associated).') AND master=0
                ORDER BY title
            ')
            ->execute()
        ;

        while ($result->next()) {
            $options[$result->id] = \sprintf($GLOBALS['TL_LANG'][$this->table]['isSlave'], $result->title);
        }

        return $options;
    }

    /**
     * @param string|null $value
     */
    private function validateMaster($value, DataContainer $dc): void
    {
        if (!$value) {
            return;
        }

        $result = Database::getInstance()
            ->prepare('
                SELECT title
                FROM '.$this->table.'
                WHERE jumpTo=? AND master=? AND id!=?
            ')
            ->limit(1)
            ->execute($dc->activeRecord->jumpTo, $value, $dc->id)
        ;

        if ($result->numRows > 0) {
            throw new \RuntimeException(\sprintf($GLOBALS['TL_LANG'][$this->table]['master'][2], $result->title));
        }
    }
}
