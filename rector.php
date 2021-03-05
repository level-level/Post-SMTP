<?php

declare(strict_types=1);

use Rector\CodeQuality\Rector\Class_\CompleteDynamicPropertiesRector;
use Rector\Core\Configuration\Option;
use Rector\Core\ValueObject\PhpVersion;
use Rector\Php74\Rector\Property\TypedPropertyRector;
use Rector\Set\ValueObject\SetList;
use Rector\TypeDeclaration\Rector\ClassMethod\AddReturnTypeDeclarationRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    // get parameters
    $parameters = $containerConfigurator->parameters();
    $parameters->set(Option::PHP_VERSION_FEATURES, PhpVersion::PHP_72);
    $parameters->set(Option::PHPSTAN_FOR_RECTOR_PATH, getcwd() . '/phpstan.neon.dist');
    // Define what rule sets will be applied
    // $parameters->set(Option::SETS, [
    //     SetList::DEAD_DOC_BLOCK,
    // ]);

    // get services (needed for register a single rule)
    $services = $containerConfigurator->services();

    // register a single rule
    $services->set(TypedPropertyRector::class);
};
