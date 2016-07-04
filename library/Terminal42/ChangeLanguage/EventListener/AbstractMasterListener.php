<?php

/**
 * changelanguage Extension for Contao Open Source CMS
 *
 * @copyright  Copyright (c) 2008-2016, terminal42 gmbh
 * @author     terminal42 gmbh <info@terminal42.ch>
 * @license    http://opensource.org/licenses/lgpl-3.0.html LGPL
 * @link       http://github.com/terminal42/contao-changelanguage
 */

namespace Terminal42\ChangeLanguage\EventListener;

use Contao\Model;
use Terminal42\ChangeLanguage\Event\ChangelanguageNavigationEvent;

abstract class AbstractMasterListener
{
    /**
     * Find record based on languageMain field and parent master archive
     *
     * @param ChangelanguageNavigationEvent $event
     */
    public function onChangelanguageNavigation(ChangelanguageNavigationEvent $event)
    {
        $current = $this->findCurrent();

        if (null === $current) {
            return;
        }

        $parent = $current->getRelated('pid');
        $t = $current::getTable();

        if (0 === $parent->master) {
            $mainId = $current->id;
            $masterId = $current->pid;
        } else {
            // Abort if current record has no translated version
            if (0 === $current->languageMain) {
                return;
            }

            $mainId = $current->languageMain;
            $masterId = $parent->master;
        }

        $translated = $this->findPublishedBy(
            array(
                "($t.id=? OR $t.languageMain=?)",
                "$t.pid=(SELECT id FROM " . $parent::getTable() . " WHERE (id=? OR master=?) AND language=?)"
            ),
            array($mainId, $mainId, $masterId, $masterId, $event->getNavigationItem()->getLanguageTag())
        );

        if (null === $translated) {
            return;
        }

        $urlKey = $this->getUrlKey();

        if ($GLOBALS['TL_CONFIG']['useAutoItem'] && in_array($urlKey, $GLOBALS['TL_AUTO_ITEM'], true)) {
            $urlKey = 'auto_item';
        }

        $urlParameters = $event->getUrlParameterBag();
        $urlParameters->setUrlAttribute($urlKey, $translated->alias ?: $translated->id);
    }

    /**
     * Adds publishing conditions to Model query columns if backend user is not logged in.
     *
     * @param array  $columns
     * @param string $table
     *
     * @return array
     */
    protected function addPublishedConditions(array $columns, $table)
    {
        if (true !== BE_USER_LOGGED_IN) {
            $time      = \Date::floorToMinute();
            $columns[] = "($table.start='' OR $table.start<='$time')";
            $columns[] = "($table.stop='' OR $table.stop>'" . ($time + 60) . "')";
            $columns[] = "$table.published='1'";
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
