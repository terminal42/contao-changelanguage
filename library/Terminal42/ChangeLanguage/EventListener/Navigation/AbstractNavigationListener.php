<?php

/**
 * changelanguage Extension for Contao Open Source CMS
 *
 * @copyright  Copyright (c) 2008-2016, terminal42 gmbh
 * @author     terminal42 gmbh <info@terminal42.ch>
 * @license    http://opensource.org/licenses/lgpl-3.0.html LGPL
 * @link       http://github.com/terminal42/contao-changelanguage
 */

namespace Terminal42\ChangeLanguage\EventListener\Navigation;

use Contao\Model;
use Terminal42\ChangeLanguage\Event\ChangelanguageNavigationEvent;

abstract class AbstractNavigationListener
{
    /**
     * Find record based on languageMain field and parent master archive
     *
     * @param ChangelanguageNavigationEvent $event
     */
    public function onChangelanguageNavigation(ChangelanguageNavigationEvent $event)
    {
        if ($event->getNavigationItem()->isCurrentPage()) {
            return;
        }

        $current = $this->findCurrent();

        if (null === $current) {
            return;
        }

        $t      = $current::getTable();
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
            return;
        }

        $translated = $this->findPublishedBy(
            array(
                "($t.id=? OR $t.languageMain=?)",
                "$t.pid=(SELECT id FROM " . $parent::getTable() . ' WHERE (id=? OR master=?) AND language=?)'
            ),
            array($mainId, $mainId, $masterId, $masterId, $event->getNavigationItem()->getLanguageTag())
        );

        if (null === $translated) {
            $event->getNavigationItem()->setIsDirectFallback(false);
            return;
        }

        $urlParameters = $event->getUrlParameterBag();
        $urlKey        = $this->getUrlKey();

        if ($GLOBALS['TL_CONFIG']['useAutoItem'] && in_array($urlKey, $GLOBALS['TL_AUTO_ITEM'], true)) {
            $urlParameters->removeUrlAttribute($urlKey);
            $urlKey = 'auto_item';
        }

        $urlParameters->setUrlAttribute($urlKey, $translated->alias ?: $translated->id);
    }

    /**
     * Adds publishing conditions to Model query columns if backend user is not logged in.
     *
     * @param array  $columns
     * @param string $table
     * @param bool   $addStartStop
     *
     * @return array
     */
    protected function addPublishedConditions(array $columns, $table, $addStartStop = true)
    {
        if (true !== BE_USER_LOGGED_IN) {
            $columns[] = "$table.published='1'";

            if ($addStartStop) {
                $time      = \Date::floorToMinute();
                $columns[] = "($table.start='' OR $table.start<='$time')";
                $columns[] = "($table.stop='' OR $table.stop>'" . ($time + 60) . "')";
            }
        }

        return $columns;
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
     * @param array $columns
     * @param array $values
     * @param array $options
     *
     * @return Model|null
     */
    abstract protected function findPublishedBy(array $columns, array $values = array(), array $options = array());
}
