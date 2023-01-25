<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use BaksDev\Products\Product\Type\Event\ProductEventUid;
use BaksDev\Products\Product\Type\Event\ProductEventType;
use BaksDev\Products\Product\Type\File\FileUid;
use BaksDev\Products\Product\Type\File\FileUidType;
use BaksDev\Products\Product\Type\Id\ProductUid;
use BaksDev\Products\Product\Type\Id\ProductUidType;
use BaksDev\Products\Product\Type\Offers\ConstId\ProductOfferConst;
use BaksDev\Products\Product\Type\Offers\ConstId\ProductOfferConstType;
use BaksDev\Products\Product\Type\Offers\Id\ProductOfferUid;
use BaksDev\Products\Product\Type\Offers\Id\ProductOfferType;
use BaksDev\Products\Product\Type\Offers\Image\ImageUid;
use BaksDev\Products\Product\Type\Offers\Image\ImageUidType;
use BaksDev\Products\Product\Type\Photo\PhotoUid;
use BaksDev\Products\Product\Type\Photo\PhotoUidType;
use BaksDev\Products\Product\Type\Settings\ProductSettings;
use BaksDev\Products\Product\Type\Settings\ProductSettingsType;
use BaksDev\Products\Product\Type\Video\VideoUid;
use BaksDev\Products\Product\Type\Video\VideoUidType;

use App\Module\Users\Auth\Email\Type\Event\AccountEventUid;
use Symfony\Config\DoctrineConfig;

return static function (ContainerConfigurator $container, DoctrineConfig $doctrine)
{
    
    /* ProductUid */
    
    $doctrine->dbal()->type(ProductUid::TYPE)->class(ProductUidType::class);
	$container->services()->set(ProductUid::class)
		->tag('controller.argument_value_resolver', ['priority' => 100])
	;
	
	
    $doctrine->dbal()->type(ProductEventUid::TYPE)->class(ProductEventType::class);
	
    $doctrine->dbal()->type(ProductOfferConst::TYPE)->class(ProductOfferConstType::class);
	$container->services()->set(ProductOfferConst::class)
		->tag('controller.argument_value_resolver', ['priority' => 100]);
	
    $doctrine->dbal()->type(FileUid::TYPE)->class(FileUidType::class);
	
	
	$doctrine->dbal()->type(ProductOfferUid::TYPE)->class(ProductOfferType::class);
	//$container->services()->set(ProductOfferUid::class)
	//	->tag('controller.argument_value_resolver', ['priority' => 100]);
	
  
	
    $doctrine->dbal()->type(ProductSettings::TYPE)->class(ProductSettingsType::class);
    $doctrine->dbal()->type(PhotoUid::TYPE)->class(PhotoUidType::class);
    $doctrine->dbal()->type(VideoUid::TYPE)->class(VideoUidType::class);
    $doctrine->dbal()->type(ImageUid::TYPE)->class(ImageUidType::class);
    
    
    
    $emDefault = $doctrine->orm()->entityManager('default');
    
    $emDefault->autoMapping(true);
    $emDefault->mapping('Product')
      ->type('attribute')
      ->dir('%kernel.project_dir%/src/Module/Products/Product/Entity')
      ->isBundle(false)
      ->prefix('BaksDev\Products\Product\Entity')
      ->alias('Product')
    ;
};