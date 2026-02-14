<?php
/*
 *  Copyright 2026.  Baks.dev <admin@baks.dev>
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

declare(strict_types=1);

namespace BaksDev\Products\Product\Controller\Public;

use BaksDev\Core\Controller\AbstractController;
use BaksDev\Core\Form\Search\SearchDTO;
use BaksDev\Core\Form\Search\SearchForm;
use BaksDev\Orders\Order\UseCase\Public\Basket\Add\PublicOrderProductDTO;
use BaksDev\Orders\Order\UseCase\Public\Basket\Add\PublicOrderProductForm;
use BaksDev\Products\Product\Entity\Info\ProductInfo;
use BaksDev\Products\Product\Repository\Cards\ProductAlternative\ProductAlternativeInterface;
use BaksDev\Products\Product\Repository\ProductDetailByValue\ProductDetailByValueInterface;
use BaksDev\Products\Product\Repository\ProductDetailByValue\ProductDetailByValueResult;
use BaksDev\Products\Product\Repository\ProductDetailOffer\ProductDetailOfferInterface;
use BaksDev\Products\Review\Form\Status\ReviewStatusDTO;
use BaksDev\Products\Review\Repository\AllReviews\AllReviewsInterface;
use BaksDev\Products\Review\Type\Status\ReviewStatus;
use BaksDev\Products\Review\Type\Status\ReviewStatus\Collection\ReviewStatusActive;
use DateTimeImmutable;
use InvalidArgumentException;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
final class DetailController extends AbstractController
{
    #[Route('/catalog/{category}/{url}/{offer}/{variation}/{modification}/{postfix}', name: 'public.detail')]
    public function index(
        Request $request,
        HttpKernelInterface $httpKernel,
        #[MapEntity(mapping: ['url' => 'url'])] ProductInfo $info,
        ProductDetailByValueInterface $productDetail,
        ProductDetailOfferInterface $productDetailOffer,
        ProductAlternativeInterface $productAlternative,
        AllReviewsInterface $AllReviewsRepository,
        ?string $offer = null,
        ?string $variation = null,
        ?string $modification = null,
        ?string $postfix = null,
    ): Response
    {
        /** @var ProductDetailByValueResult|false $productCard */
        $productCard = $productDetail
            ->byProduct($info->getProduct())
            ->byOfferValue($offer)
            ->byVariationValue($variation)
            ->byModificationValue($modification)
            ->byPostfix($postfix)
            ->find();

        if(false === ($productCard instanceof ProductDetailByValueResult))
        {
            throw new InvalidArgumentException('Page Not Found', code: 404);
        }

        /** Другие ТП данного продукта */
        $productOffer = $productDetailOffer->fetchProductOfferAssociative($info->getProduct());

        /** Если у продукта имеются торговые предложения - показываем модель */
        if($offer === null && count($productOffer) > 1)
        {
            $path['_controller'] = ModelController::class.'::model';
            $path['_route'] = 'products-product:public.model';
            $path['category'] = $productCard->getCategoryUrl();
            $path['url'] = $productCard->getProductUrl();

            $subRequest = $request->duplicate([], null, $path);

            return $httpKernel->handle($subRequest, HttpKernelInterface::SUB_REQUEST);
        }

        /** Статус, если товара с ТП, вариантом или модификацией не существует */

        $NOW = new DateTimeImmutable();

        if(
            !$productCard ||
            ($offer !== null && $productCard->getProductOfferValue() === null) ||
            ($variation !== null && $productCard->getProductVariationValue() === null) ||
            ($modification !== null && $productCard->getProductModificationValue() === null) ||
            $productCard->isActiveProduct() === false ||
            (!empty($productCard->getProductActiveFrom()) && new DateTimeImmutable($productCard->getProductActiveFrom()) > $NOW) ||
            (!empty($productCard->getProductActiveTo()) && new DateTimeImmutable($productCard->getProductActiveTo()) < $NOW)
        )
        {
            $path['_controller'] = NotFoundController::class.'::notfound';
            $path['_route'] = 'products-product:public.notfound';
            $path['category'] = $productCard->getCategoryUrl();
            $path['url'] = $productCard->getProductUrl();
            $path['offer'] = $offer;
            $path['variation'] = $variation;
            $path['modification'] = $modification;

            $subRequest = $request->duplicate([], null, $path);

            return $httpKernel->handle($subRequest, HttpKernelInterface::SUB_REQUEST);
        }

        /** Удаляем сессию фильтра каталога */
        $request->getSession()->set('catalog_filter', null);

        /** Список альтернатив  */
        $alternativeProperty = $productCard->getCategorySectionField();

        /** Получаем свойства, участвующие в фильтре альтернатив */
        $alternativeField = array_filter($alternativeProperty, function($v) {
            return $v->field_alternative === true;
        }, ARRAY_FILTER_USE_BOTH);

        $alternative = null;

        if(!empty($productCard->getProductOfferValue()))
        {
            $alternative = $productAlternative
                ->forOfferValue($productCard->getProductOfferValue())
                ->forVariationValue($productCard->getProductVariationValue())
                ->forModificationValue($productCard->getProductModificationValue())
                ->byProperty($alternativeField)
                ->excludeProductInvariable($productCard->getProductInvariableId())
                ->toArray();
        }

        /** Корзина */
        $form = null;

        if(class_exists(PublicOrderProductDTO::class))
        {
            $AddProductBasketDTO = new PublicOrderProductDTO();

            $form = $this->createForm(PublicOrderProductForm::class, $AddProductBasketDTO, [
                'action' => $this->generateUrl(
                    'orders-order:public.add',
                    [
                        'product' => $productCard->getProductEvent(),
                        'offer' => $productCard->getProductOfferUid(),
                        'variation' => $productCard->getProductVariationUid(),
                        'modification' => $productCard->getProductModificationUid(),
                    ],
                ),
            ]);
        }


        // Поиск по всему сайту
        $allSearch = new SearchDTO();
        $allSearchForm = $this->createForm(SearchForm::class, $allSearch, [
            'action' => $this->generateUrl('search:public.search'),
        ]);


        /** Отзывы */
        $statusDTO = new ReviewStatusDTO()->setStatus(new ReviewStatus(ReviewStatusActive::PARAM));

        // Получаем список
        $ProductsReviews = $AllReviewsRepository
            ->filter($statusDTO)
            ->product($productCard->getProductId())
            ->findPaginator();

        return $this->render([
            'card' => $productCard,
            'offers' => $productOffer,
            'alternative' => $alternative,
            'offer' => $offer,
            'variation' => $variation,
            'modification' => $modification,
            'basket' => $form?->createView(),
            'all_search' => $allSearchForm->createView(),
            'reviews' => $ProductsReviews,
        ]);
    }
}
