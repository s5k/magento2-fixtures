<?php

/**
 * See LICENSE file for license details.
 */

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Php84\Rector\FuncCall\AddEscapeArgumentRector;
use Rector\Php84\Rector\FuncCall\RoundingModeEnumRector;
use Rector\Php84\Rector\Param\ExplicitNullableParamTypeRector;
use Rector\ValueObject\PhpVersion;

return RectorConfig::configure()
    ->withPaths([
        __DIR__ . '*',
    ])
    ->withSkipPath(__DIR__ . '/vendor')
    ->withPhpVersion(PhpVersion::PHP_84)
    ->withRules([
        // This is the key rule to fix PHP 8.4 deprecation warnings
        ExplicitNullableParamTypeRector::class,
        // Add other needed PHP 8.4 compatibility rules
        AddEscapeArgumentRector::class,
        RoundingModeEnumRector::class,
    ])
    // Skip feature sets and rules that might cause issues
    ->withSkip([]);
