<?php

/*
 * changelanguage Extension for Contao Open Source CMS
 *
 * @copyright  Copyright (c) 2008-2019, terminal42 gmbh
 * @author     terminal42 gmbh <info@terminal42.ch>
 * @license    http://opensource.org/licenses/lgpl-3.0.html LGPL
 * @link       http://github.com/terminal42/contao-changelanguage
 */

namespace Terminal42\ChangeLanguage\EventListener;

abstract class AbstractTableListener
{
    /**
     * @var string
     */
    protected $table;

    /**
     * Constructor.
     *
     * @param string $table
     */
    public function __construct($table)
    {
        $this->table = $table;
    }

    /**
     * Register necessary callbacks for this listener.
     */
    abstract public function register();

    /**
     * Gets the table name for this listener.
     *
     * @return string
     */
    protected function getTable()
    {
        return $this->table;
    }
}
