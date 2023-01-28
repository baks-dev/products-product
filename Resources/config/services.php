<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

//use App\Module\Product\Type\Category\Id\CategoryUidConverter;
//use BaksDev\Users\Entity\User;
//use App\Module\Product\Entity;
//use App\Module\Product\EntityListeners;

return static function (ContainerConfigurator $configurator)
{
    $services = $configurator->services()
      ->defaults()
      ->autowire()
      ->autoconfigure()
    ;
    
	$namespace = 'BaksDev\Products\Product';

    $services->load($namespace.'\Controller\\', '../../Controller')
      ->tag('controller.service_arguments');
    
    $services->load($namespace.'\Repository\\', __DIR__.'/../../Repository')
      ->exclude(__DIR__.'/../../Repository/**/*DTO.php');
      //->tag('controller.service_arguments');
    
    $services->load($namespace.'\UseCase\\', __DIR__.'/../../UseCase')
      ->exclude(__DIR__.'/../../UseCase/**/*DTO.php');

    $services->load($namespace.'\DataFixtures\\', __DIR__.'/../../DataFixtures')
      ->exclude(__DIR__.'/../../DataFixtures/**/*DTO.php');
    
    $services->load($namespace.'\Forms\\', __DIR__.'/../../Forms');
    
};

