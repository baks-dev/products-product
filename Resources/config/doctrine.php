<?php
/*
 *  Copyright 2023.  Baks.dev <admin@baks.dev>
 *  
 *  Permission is hereby granted, free of charge, to any person obtaining a copy
 *  of this software and associated documentation files (the "Software"), to deal
 *  in the Software without restriction, including without limitation the rights
 *  to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 *  copies of the Software, and to permit persons to whom the Software is furnished
 *  to do so, subject to the following conditions:
 *  
 *  The above copyright notice and this permission notice shall be included in all
 *  copies or substantial portions of the Software.
 *  
 *  THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 *  IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 *  FITNESS FOR A PARTICULAR PURPOSE AND NON INFRINGEMENT. IN NO EVENT SHALL THE
 *  AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 *  LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 *  OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 *  THE SOFTWARE.
 */

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
use BaksDev\Products\Product\Type\Offers\Variation\ConstId\ProductOfferVariationConst;
use BaksDev\Products\Product\Type\Offers\Variation\ConstId\ProductOfferVariationConstType;
use BaksDev\Products\Product\Type\Offers\Variation\Image\ProductOfferVariationImageType;
use BaksDev\Products\Product\Type\Offers\Variation\Image\ProductOfferVariationImageUid;
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
	$doctrine->dbal()->type(ProductOfferVariationImageUid::TYPE)->class(ProductOfferVariationImageType::class);
	$doctrine->dbal()->type(ProductOfferVariationConst::TYPE)->class(ProductOfferVariationConstType::class);
	
	
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