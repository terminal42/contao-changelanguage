<?php

namespace Terminal42\ChangeLanguage\Helper;

use Contao\Controller;
use Contao\System;

class LabelCallback
{
    /**
     * @var callable
     */
    private $previous;

    /**
     * Registers callback for given table.
     *
     * @param string   $table
     * @param callable $callback
     */
    public function register($table, callable $callback)
    {
        Controller::loadDataContainer($table);

        $chain = function () use ($callback) {
            $args   = func_get_args();
            $result = null;

            if (is_callable($this->previous)) {
                $result = $this->executeCallback($this->previous, $args);
            }

            return $this->executeCallback($callback, [$args, $result]);
        };

        if (4 === $GLOBALS['TL_DCA'][$table]['list']['sorting']['mode']) {
            $this->previous = $GLOBALS['TL_DCA'][$table]['list']['sorting']['child_record_callback'];
            $GLOBALS['TL_DCA'][$table]['list']['sorting']['child_record_callback'] = $chain;
        } else {
            $this->previous = $GLOBALS['TL_DCA'][$table]['list']['label']['label_callback'];
            $GLOBALS['TL_DCA'][$table]['list']['label']['label_callback'] = $chain;
        }
    }

    /**
     * @param \Closure|array $callback
     * @param array $args
     *
     * @return mixed
     */
    private function executeCallback($callback, array $args)
    {
        // Support Contao's getInstance() method when callback is an array
        if (is_array($callback)) {
            return call_user_func_array(
                [System::importStatic($callback[0]), $callback[1]],
                $args
            );
        }

        return call_user_func_array($callback, $args);
    }

    /**
     * Creates and registers new LabelCallback.
     *
     * @param string   $table
     * @param callable $callback
     *
     * @return static
     */
    public static function createAndRegister($table, callable $callback)
    {
        $instance = new static();
        $instance->register($table, $callback);

        return $instance;
    }
}
