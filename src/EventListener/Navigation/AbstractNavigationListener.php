<?php

declare(strict_types=1);

namespace Terminal42\ChangeLanguage\EventListener\Navigation;

use Contao\CoreBundle\Security\Authentication\Token\TokenChecker;
use Contao\Date;
use Contao\Input;
use Contao\Model;
use Terminal42\ChangeLanguage\Event\ChangelanguageNavigationEvent;

abstract class AbstractNavigationListener
{
    private TokenChecker $tokenChecker;

    public function __construct(TokenChecker $tokenChecker)
    {
        $this->tokenChecker = $tokenChecker;
    }

    /**
     * Find record based on languageMain field and parent master archive.
     */
    public function onChangelanguageNavigation(ChangelanguageNavigationEvent $event): void
    {
        $current = $this->findCurrent();

        if (null === $current) {
            return;
        }

        $navigationItem = $event->getNavigationItem();

        if ($navigationItem->isCurrentPage()) {
            if ($this instanceof NavigationHandlerInterface) {
                $this->handleNavigation($event, $current);
            } else {
                $event->getUrlParameterBag()->setUrlAttribute($this->getUrlKey(), $current->alias ?: $current->id);
            }

            return;
        }

        // Remove the news/event/faq alias from the URL if there is no actual
        // reader page assigned
        if (!$navigationItem->isDirectFallback()) {
            $event->getUrlParameterBag()->removeUrlAttribute($this->getUrlKey());
        }

        $t = $current::getTable();
        $parent = $current->getRelated('pid');

        if (0 === (int) $parent->master) {
            $mainId = (int) $current->id;
            $masterId = (int) $current->pid;
        } else {
            $mainId = (int) $current->languageMain;
            $masterId = (int) $parent->master;
        }

        // Abort if current record has no translated version
        if (0 === $mainId || 0 === $masterId) {
            $navigationItem->setIsDirectFallback(false);

            return;
        }

        $targetPage = $navigationItem->getTargetPage();

        if (null === $targetPage) {
            return;
        }

        $translated = $this->findPublishedBy(
            [
                "($t.id=? OR $t.languageMain=?)",
                sprintf('%s.pid=(SELECT id FROM %s WHERE (id=? OR master=?) AND jumpTo=?)', $t, $parent::getTable()),
            ],
            [$mainId, $mainId, $masterId, $masterId, $targetPage->id],
        );

        if (null === $translated) {
            $navigationItem->setIsDirectFallback(false);

            return;
        }

        if ($this instanceof NavigationHandlerInterface) {
            $this->handleNavigation($event, $translated);
        } else {
            $event->getUrlParameterBag()->setUrlAttribute($this->getUrlKey(), $translated->alias ?: $translated->id);
        }
    }

    /**
     * Adds publishing conditions to Model query columns if backend user is not logged in.
     */
    protected function addPublishedConditions(array $columns, string $table, bool $addStartStop = true): array
    {
        if (!$this->tokenChecker->isPreviewMode()) {
            $columns[] = "$table.published='1'";

            if ($addStartStop) {
                $time = Date::floorToMinute();
                $columns[] = "($table.start='' OR $table.start<='$time')";
                $columns[] = "($table.stop='' OR $table.stop>'".($time + 60)."')";
            }
        }

        return $columns;
    }

    protected function getAutoItem(): string
    {
        $strKey = $this->getUrlKey();

        if (
            !isset($GLOBALS['TL_CONFIG']['useAutoItem'])
            || (
                $GLOBALS['TL_CONFIG']['useAutoItem']
                && isset($GLOBALS['TL_AUTO_ITEM'])
                && \in_array($strKey, $GLOBALS['TL_AUTO_ITEM'], true)
            )
        ) {
            $strKey = 'auto_item';
        }

        return (string) Input::get($strKey, false, true);
    }

    /**
     * @return string
     */
    abstract protected function getUrlKey();

    /**
     * @return Model|null
     */
    abstract protected function findCurrent();

    /**
     * @return Model|null
     */
    abstract protected function findPublishedBy(array $columns, array $values = [], array $options = []);
}
