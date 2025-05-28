<?php
declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\TypeDeclaration\Rector\ClassMethod\AddReturnArrayDocblockBasedOnArrayMapRector;
use Rector\Set\ValueObject\SetList;
use Rector\Php83\Rector\ClassMethod\AddOverrideAttributeToOverriddenMethodsRector;
use Rector\ValueObject\PhpVersion;
use Rector\Php74\Rector\Closure\ClosureToArrowFunctionRector;

return RectorConfig::configure()
    ->withPaths([
        __DIR__ . '/src',
        __DIR__ . '/tests',
    ])
    ->withPhpLevel(PhpVersion::PHP_84)
	->withSets([
		SetList::PHP_84,
	])

    ->withRules([
        //AddReturnArrayDocblockBasedOnArrayMapRector::class,
    ])
    ->withSkip([
        AddOverrideAttributeToOverriddenMethodsRector::class,
        ClosureToArrowFunctionRector::class,
    ])
    ->withPreparedSets(
        typeDeclarations: true,
    );
