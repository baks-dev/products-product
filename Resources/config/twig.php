<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Symfony\Config\TwigConfig;

return static function (TwigConfig $config, ContainerConfigurator $configurator)
{

    $config->path('%kernel.project_dir%/src/Module/Products/Product/Resources/view', 'Product');
    
    
    /** Абсолютный Путь для загрузки обложек товара галереи */
    $configurator->parameters()->set('product_image_dir', '%kernel.project_dir%/public/assets/images/products/gallery/');
    /** Относительный путь обложек товара галереи */
    $config->global('product_image_dir')->value('/images/products/gallery/');
    
    
    
    /** Абсолютный Путь для загрузки файлов галереи */
    $configurator->parameters()->set('product_file_dir', '%kernel.project_dir%/public/assets/files/products/gallery/');
    /** Относительный путь файлов галереи */
    $config->global('product_file_dir')->value('/files/products/gallery/');
    
    
    /** Абсолютный Путь для загрузки видео галереи */
    $configurator->parameters()->set('product_video_dir', '%kernel.project_dir%/public/assets/video/products/gallery/');
    /** Относительный путь файлов галереи */
    $config->global('product_video_dir')->value('/video/products/gallery/');
    
    
    /** Абсолютный Путь для загрузки обложек торгового предложения */
    $configurator->parameters()->set('product_offer_image_dir', '%kernel.project_dir%/public/assets/images/products/offer/');
    /** Относительный путь обложек торгового предложения */
    $config->global('product_offer_image_dir')->value('/images/products/offer/');
    
 
};




