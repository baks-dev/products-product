<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use BaksDev\Products\Product\Entity\Files\ProductFiles;
use BaksDev\Products\Product\Entity\Photo\ProductPhoto;
use BaksDev\Products\Product\Entity\Video\ProductVideo;
use Symfony\Config\TwigConfig;

return static function(TwigConfig $config, ContainerConfigurator $configurator) {
	
	$config->path(__DIR__.'/../view', 'Product');
	
	/** Абсолютный Путь для загрузки обложек товара галереи */
	$configurator->parameters()->set(ProductPhoto::TABLE,
		'%kernel.project_dir%/public/upload/'.ProductPhoto::TABLE.'/'
	);
	
	/** Относительный путь обложек товара галереи */
	$config->global(ProductPhoto::TABLE)->value('/upload/'.ProductPhoto::TABLE.'/');
	
	/**/
	
	/** Абсолютный Путь для загрузки файлов галереи */
	$configurator->parameters()->set(ProductFiles::TABLE,
		'%kernel.project_dir%/public/upload/'.ProductFiles::TABLE.'/'
	);
	
	/** Относительный путь файлов галереи */
	$config->global(ProductFiles::TABLE)->value('/upload/'.ProductFiles::TABLE.'/');
	
	/** Абсолютный Путь для загрузки видео галереи */
	$configurator->parameters()->set(ProductVideo::TABLE,
		'%kernel.project_dir%/public/upload/'.ProductVideo::TABLE.'/'
	);
	
	/** Относительный путь файлов галереи */
	$config->global(ProductVideo::TABLE)->value('/upload/'.ProductVideo::TABLE.'/');
	
	/* TODO Абсолютный Путь для загрузки обложек торгового предложения */
	
	//	/** Абсолютный Путь для загрузки обложек торгового предложения */
	//    $configurator->parameters()->set('product_offer_image_dir', '%kernel.project_dir%/public/assets/images/products/offer/');
	//    /** Относительный путь обложек торгового предложения */
	//    $config->global('product_offer_image_dir')->value('/images/products/offer/');
	
};




