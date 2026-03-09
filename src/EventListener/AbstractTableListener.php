<?php

declare(strict_types=1);

namespace Terminal42\ChangeLanguage\EventListener;

abstract class AbstractTableListener
{
    protected string $table;

    /**
     * Register necessary callbacks for this listener.
     */
    abstract public function register(string $table): void;

    /**
     * Gets the table name for this listener.
     */
    protected function getTable(): string
    {
        return $this->table;
    }
}
