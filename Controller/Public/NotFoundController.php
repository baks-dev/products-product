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

namespace BaksDev\Products\Product\Controller\Public;

use BaksDev\Core\Controller\AbstractController;
use BaksDev\Products\Product\Entity\Info\ProductInfo;
use BaksDev\Products\Product\Repository\ProductModel\ProductModelInterface;
use BaksDev\Products\Product\Repository\ProductModel\ProductModelResult;
use BaksDev\Products\Review\BaksDevProductsReviewBundle;
use BaksDev\Products\Review\Repository\AllReviews\AllReviewsInterface;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
final class NotFoundController extends AbstractController
{
    /** Not Found Product */
    #[Route('/catalog/{category}/{url}/{offer}/{variation}/{modification}/notfound',
        name: 'public.notfound',
        priority: 10
    )]
    public function notfound(
        #[MapEntity(mapping: ['url' => 'url'])] ProductInfo $info,

        ProductModelInterface $productModel,
        ?AllReviewsInterface $AllReviewsRepository = null,

        ?string $offer = null,
        ?string $variation = null,
        ?string $modification = null,
    ): Response
    {
        for($i = 0; $i < 2; $i++)
        {
            $card = $productModel
                ->byProduct($info->getProduct())
                ->byOffer($offer)
                ->byVariation($variation)
                ->byModification($modification)
                ->find();

            if($card instanceof ProductModelResult)
            {
                break;
            }

            if($i === 0)
            {
                $modification = null;
                continue;
            }

            if($i === 1)
            {
                $variation = null;
                continue;
            }

            if($i === 2)
            {
                $offer === null;
            }
        }

        if(false === ($card instanceof ProductModelResult))
        {
            return new Response('Page Not Found', status: 404);
        }

        /** Отзывы */
        // Получаем список
        $ProductsReviews = true === class_exists(BaksDevProductsReviewBundle::class) ?
            $AllReviewsRepository
                ->product($info->getProduct())
                ->findPaginator() :
            false;

        return $this->render(
            parameters: [
                'card' => $card,
                'reviews' => $ProductsReviews,
                'offer' => $offer,
                'variation' => $variation,
                'modification' => $modification,
                'notfound' => true,
            ],

            module: 'products-product',
            dir: '/public/model',

            response: new Response(status: 404),
        );
    }
}
