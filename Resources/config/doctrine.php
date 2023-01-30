<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use BaksDev\Products\Product\Type\Event\ProductEventType;
use BaksDev\Products\Product\Type\Event\ProductEventUid;
use BaksDev\Products\Product\Type\File\ProductFileType;
use BaksDev\Products\Product\Type\File\ProductFileUid;
use BaksDev\Products\Product\Type\Id\ProductType;
use BaksDev\Products\Product\Type\Id\ProductUid;
use BaksDev\Products\Product\Type\Offers\ConstId\ProductOfferConst;
use BaksDev\Products\Product\Type\Offers\ConstId\ProductOfferConstType;
use BaksDev\Products\Product\Type\Offers\Id\ProductOfferType;
use BaksDev\Products\Product\Type\Offers\Id\ProductOfferUid;
use BaksDev\Products\Product\Type\Offers\Image\ProductOfferImageType;
use BaksDev\Products\Product\Type\Offers\Image\ProductOfferImageUid;
use BaksDev\Products\Product\Type\Photo\ProductPhotoType;
use BaksDev\Products\Product\Type\Photo\ProductPhotoUid;
use BaksDev\Products\Product\Type\Settings\ProductSettingsIdentifier;
use BaksDev\Products\Product\Type\Settings\ProductSettingsType;
use BaksDev\Products\Product\Type\Video\ProductVideoType;
use BaksDev\Products\Product\Type\Video\ProductVideoUid;
use Symfony\Config\DoctrineConfig;

return static function(ContainerConfigurator $container, DoctrineConfig $doctrine) {
	
	/* ProductUid */
	
	$doctrine->dbal()->type(ProductUid::TYPE)->class(ProductType::class);
	$doctrine->dbal()->type(ProductEventUid::TYPE)->class(ProductEventType::class);
	$doctrine->dbal()->type(ProductOfferConst::TYPE)->class(ProductOfferConstType::class);
	$doctrine->dbal()->type(ProductFileUid::TYPE)->class(ProductFileType::class);
	$doctrine->dbal()->type(ProductOfferUid::TYPE)->class(ProductOfferType::class);
	$doctrine->dbal()->type(ProductSettingsIdentifier::TYPE)->class(ProductSettingsType::class);
	$doctrine->dbal()->type(ProductPhotoUid::TYPE)->class(ProductPhotoType::class);
	$doctrine->dbal()->type(ProductVideoUid::TYPE)->class(ProductVideoType::class);
	$doctrine->dbal()->type(ProductOfferImageUid::TYPE)->class(ProductOfferImageType::class);
	
	$emDefault = $doctrine->orm()->entityManager('default');
	
	$emDefault->autoMapping(true);
	$emDefault->mapping('Product')
		->type('attribute')
		->dir(__DIR__.'/../../Entity')
		->isBundle(false)
		->prefix('BaksDev\Products\Product\Entity')
		->alias('Product')
	;
};