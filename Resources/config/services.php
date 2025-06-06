<?php
/*
 *  Copyright 2025.  Baks.dev <admin@baks.dev>
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

use BaksDev\Products\Product\BaksDevProductsProductBundle;
use BaksDev\Products\Product\Repository\Search\AllProducts\SearchAllProductsRepository;
use BaksDev\Products\Product\Repository\Search\AllProductsToIndex\AllProductsToIndexRepository;
use BaksDev\Search\Repository\DataToIndex\DataToIndexInterface;
use BaksDev\Search\Repository\SearchRepository\SearchRepositoryInterface;

return static function(ContainerConfigurator $container) {

    $services = $container->services()
        ->defaults()
        ->public()
        ->autowire()
        ->autoconfigure();

    $services->load(BaksDevProductsProductBundle::NAMESPACE, BaksDevProductsProductBundle::PATH)
        ->exclude([
            BaksDevProductsProductBundle::PATH.'{Entity,Resources,Type}',
            BaksDevProductsProductBundle::PATH.'**'.DIRECTORY_SEPARATOR.'*Message.php',
            BaksDevProductsProductBundle::PATH.'**'.DIRECTORY_SEPARATOR.'*Result.php',
            BaksDevProductsProductBundle::PATH.'**'.DIRECTORY_SEPARATOR.'*DTO.php',
            BaksDevProductsProductBundle::PATH.'**'.DIRECTORY_SEPARATOR.'*Test.php',
        ]);

    $services
        ->set(SearchRepositoryInterface::class)
        ->class(SearchAllProductsRepository::class);

    $services
        ->set(DataToIndexInterface::class)
        ->class(AllProductsToIndexRepository::class);

    $NAMESPACE = BaksDevProductsProductBundle::NAMESPACE;
    $PATH = BaksDevProductsProductBundle::PATH;
    $services->load($NAMESPACE.'Type\SearchTags\\', $PATH.implode(DIRECTORY_SEPARATOR, ['Type', 'SearchTags']));
};

