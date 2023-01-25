<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return static function (ContainerConfigurator $configurator) {
    $services = $configurator->services()
      ->defaults()
      ->autowire()      // Automatically injects dependencies in your services.
      ->autoconfigure() // Automatically registers your services as commands, event subscribers, etc.
    ;
    
    //$services->set('app:wb:products:update', ProductsUpdateCommand::class);
    $services->load('BaksDev\Products\Product\Command\\', '../../Command');

    
};
