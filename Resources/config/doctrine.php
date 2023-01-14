<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use App\Module\Products\Product\Type\Event\ProductEventUid;
use App\Module\Products\Product\Type\Event\ProductEventType;
use App\Module\Products\Product\Type\File\FileUid;
use App\Module\Products\Product\Type\File\FileUidType;
use App\Module\Products\Product\Type\Id\ProductUid;
use App\Module\Products\Product\Type\Id\ProductUidType;
use App\Module\Products\Product\Type\Offers\ConstId\ProductOfferConst;
use App\Module\Products\Product\Type\Offers\ConstId\ProductOfferConstType;
use App\Module\Products\Product\Type\Offers\Id\ProductOfferUid;
use App\Module\Products\Product\Type\Offers\Id\ProductOfferType;
use App\Module\Products\Product\Type\Offers\Image\ImageUid;
use App\Module\Products\Product\Type\Offers\Image\ImageUidType;
use App\Module\Products\Product\Type\Photo\PhotoUid;
use App\Module\Products\Product\Type\Photo\PhotoUidType;
use App\Module\Products\Product\Type\Settings\ProductSettings;
use App\Module\Products\Product\Type\Settings\ProductSettingsType;
use App\Module\Products\Product\Type\Video\VideoUid;
use App\Module\Products\Product\Type\Video\VideoUidType;

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
      ->prefix('App\Module\Products\Product\Entity')
      ->alias('Product')
    ;
};