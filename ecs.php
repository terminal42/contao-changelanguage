<?php

declare(strict_types=1);

use PhpCsFixer\Fixer\Phpdoc\NoSuperfluousPhpdocTagsFixer;
use Symplify\EasyCodingStandard\Config\ECSConfig;

return static function (ECSConfig $ecsConfig): void {
    $ecsConfig->skip([NoSuperfluousPhpdocTagsFixer::class]);
};
