<?php
declare(strict_types=1);

use Rector\Set\ValueObject\SetList;
use Rector\Config\RectorConfig;
use Rector\ValueObject\PhpVersion;
use Rector\Php83\Rector\ClassMethod\AddOverrideAttributeToOverriddenMethodsRector;

return RectorConfig::configure()
    ->withPaths([
        __DIR__ . '/src',
        __DIR__ . '/tests',
    ])
    //->withPhpLevel(PhpVersion::PHP_83)
	->withSets([
        //SetList::TYPE_DECLARATION,
	//	SetList::PHP_83,
	])

    ->withPreparedSets(
        typeDeclarations: true,
        //deadCode: true,
        //codeQuality: true
    );
