<?php declare(strict_types=1);

use Chiiya\CodeStyle\CodeStyle;
use Symplify\EasyCodingStandard\Config\ECSConfig;

return ECSConfig::configure()
    ->withSets([CodeStyle::ECS])
    ->withPaths([
        __DIR__.'/src',
        __DIR__.'/config',
    ]);
