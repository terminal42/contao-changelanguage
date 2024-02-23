<?php

declare(strict_types=1);

namespace Terminal42\ChangeLanguage\EventListener;

abstract class AbstractTableListener
{
    protected string $table;

    public function __construct(string $table)
    {
        $this->table = $table;
    }

    /**
     * Register necessary callbacks for this listener.
     */
    abstract public function register(): void;

    /**
     * Gets the table name for this listener.
     */
    protected function getTable(): string
    {
        return $this->table;
    }
}
