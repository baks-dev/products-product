<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

//use App\Module\Product\Type\Category\Id\CategoryUidConverter;
//use App\Module\Users\Entity\User;
//use App\Module\Product\Entity;
//use App\Module\Product\EntityListeners;

return static function (ContainerConfigurator $configurator)
{
    $services = $configurator->services()
      ->defaults()
      ->autowire()      // Automatically injects dependencies in your services.
      ->autoconfigure() // Automatically registers your services as commands, event subscribers, etc.
    ;
    

    /** Services */
    
    $services->load('BaksDev\Products\Product\Controller\\', '../../Controller')
      ->tag('controller.service_arguments');
    
    $services->load('BaksDev\Products\Product\Repository\\', '../../Repository')
      ->exclude('../../Repository/**/*DTO.php');
      //->tag('controller.service_arguments');
    
    $services->load('BaksDev\Products\Product\UseCase\\', '../../UseCase')
      ->exclude('../../UseCase/**/*DTO.php');
     // ->tag('controller.service_arguments');
    
    $services->load('BaksDev\Products\Product\DataFixtures\\', '../../DataFixtures')
      ->exclude('../../DataFixtures/**/*DTO.php');
    
    $services->load('BaksDev\Products\Product\Forms\\', '../../Forms');
    
};

