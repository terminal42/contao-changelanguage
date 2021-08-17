<?php

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
