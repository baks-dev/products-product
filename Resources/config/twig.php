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

use BaksDev\Products\Product\Entity\Files\ProductFiles;
use BaksDev\Products\Product\Entity\Offers\Image\ProductOfferImage;
use BaksDev\Products\Product\Entity\Offers\Variation\Image\ProductOfferVariationImage;
use BaksDev\Products\Product\Entity\Offers\Variation\Modification\Image\ProductOfferVariationModificationImage;
use BaksDev\Products\Product\Entity\Photo\ProductPhoto;
use BaksDev\Products\Product\Entity\Video\ProductVideo;
use Symfony\Config\TwigConfig;

return static function(TwigConfig $config, ContainerConfigurator $configurator) {
	
	$config->path(__DIR__.'/../view', 'Product');
	
	
	
	
	/** ОБЛОЖКИ товара галереи */
	
	/* Абсолютный Путь для загрузки обложек товара галереи */
	$configurator->parameters()->set(ProductPhoto::TABLE,
		'%kernel.project_dir%/public/upload/'.ProductPhoto::TABLE.'/'
	);
	
	/* Относительный путь обложек товара галереи */
	$config->global(ProductPhoto::TABLE)->value('/upload/'.ProductPhoto::TABLE.'/');
	
	
	
	
	/** ФАЙЛЫ товара галереи */
	
	/* Абсолютный Путь для загрузки файлов галереи */
	$configurator->parameters()->set(ProductFiles::TABLE,
		'%kernel.project_dir%/public/upload/'.ProductFiles::TABLE.'/'
	);
	
	/* Относительный путь файлов галереи */
	$config->global(ProductFiles::TABLE)->value('/upload/'.ProductFiles::TABLE.'/');
	
	
	
	
	/** ВИДЕО товара галереи */
	
	/* Абсолютный Путь для загрузки видео галереи */
	$configurator->parameters()->set(ProductVideo::TABLE,
		'%kernel.project_dir%/public/upload/'.ProductVideo::TABLE.'/'
	);
	
	/* Относительный путь файлов галереи */
	$config->global(ProductVideo::TABLE)->value('/upload/'.ProductVideo::TABLE.'/');
	
	
	
	
	/** ОБЛОЖКИ торгового предложения */
	
	/* Абсолютный Путь для загрузки обложек торгового предложения */
	$configurator->parameters()->set(ProductOfferImage::TABLE,
		'%kernel.project_dir%/public/upload/'.ProductOfferImage::TABLE.'/'
	);
	
	/* Относительный путь обложек торгового предложения */
	$config->global(ProductOfferImage::TABLE)->value('/upload/'.ProductOfferImage::TABLE.'/');
	
	
	
	
	/** ОБЛОЖКИ множественных вариантов */
	
	/* Абсолютный Путь для загрузки обложек множественных вариантов */
	$configurator->parameters()->set(ProductOfferVariationImage::TABLE,
		'%kernel.project_dir%/public/upload/'.ProductOfferVariationImage::TABLE.'/'
	);
	
	/* Относительный путь обложек множественных вариантов */
	$config->global(ProductOfferVariationImage::TABLE)->value('/upload/'.ProductOfferVariationImage::TABLE.'/');
	
	
	
	
	
	/** ОБЛОЖКИ модификаций множественных вариантов */
	
	/* Абсолютный Путь для загрузки обложек множественных вариантов */
	$configurator->parameters()->set(ProductOfferVariationModificationImage::TABLE,
		'%kernel.project_dir%/public/upload/'.ProductOfferVariationModificationImage::TABLE.'/'
	);
	
	/* Относительный путь обложек множественных вариантов */
	$config->global(ProductOfferVariationModificationImage::TABLE)->value('/upload/'.ProductOfferVariationModificationImage::TABLE.'/');
	
	
};




