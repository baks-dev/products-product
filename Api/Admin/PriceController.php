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

declare(strict_types=1);

namespace BaksDev\Products\Product\Api\Admin;


use BaksDev\Core\Controller\AbstractController;
use BaksDev\Core\Form\Search\SearchDTO;
use BaksDev\Core\Form\Search\SearchForm;
use BaksDev\Core\Listeners\Event\Security\RoleSecurity;
use BaksDev\Products\Product\Repository\CurrentProductByArticle\CurrentProductDTO;
use BaksDev\Products\Product\Repository\CurrentProductByArticle\ProductConstByArticleInterface;
use BaksDev\Products\Product\Repository\ProductDetail\ProductDetailByEventInterface;
use BaksDev\Products\Product\Repository\ProductDetail\ProductDetailByEventResult;
use BaksDev\Reference\Money\Type\Money;
use BaksDev\Users\Profile\UserProfile\Repository\UserProfileById\UserProfileByIdInterface;
use BaksDev\Users\Profile\UserProfile\Repository\UserProfileById\UserProfileResult;
use BaksDev\Users\Profile\UserProfile\Type\Id\UserProfileUid;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Target;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
final class PriceController extends AbstractController
{
    /** Метод запроса на изменение цены  */
    #[Route('/api/admin/price', name: 'api.admin.price', methods: ['POST'])]
    public function index(
        #[Target('productsProductLogger')] LoggerInterface $logger,
        Request $request,
        UserProfileByIdInterface $UserProfileById,
        ProductConstByArticleInterface $ProductConstByArticle,
        ProductDetailByEventInterface $ProductDetailByUidInterface
    ): Response
    {
        $profile = $request->headers->get('Authorization');

        if(empty($profile))
        {
            return new JsonResponse(['status' => 403], status: 403);
        }

        /** Получаем профиль пользователя */

        $UserProfileResult = $UserProfileById->profile(new UserProfileUid($profile))->find();

        if(false === ($UserProfileResult instanceof UserProfileResult))
        {
            $logger->critical('Профиль авторизации не найден', [$profile, self::class.':'.__LINE__]);
            return new JsonResponse(['status' => 403], status: 403);
        }

        if(false === json_validate($request->getContent()))
        {
            $logger->critical('Ошибка валидации JSON', [$request->getContent(), self::class.':'.__LINE__]);
            return new JsonResponse(['status' => 500], status: 500);
        }


        /** Получаем продукт по артикулу */

        $content = json_decode($request->getContent(), false, 512, JSON_THROW_ON_ERROR);

        $CurrentProductDTO = $ProductConstByArticle->find($content->article);

        if(false === ($CurrentProductDTO instanceof CurrentProductDTO))
        {
            $logger->critical('Продукт по артикулу не найден', [$content->article, self::class.':'.__LINE__]);
            return new JsonResponse(['status' => 404], status: 404);
        }

        /** Получаем детальную информацию о продукте */

        $ProductDetailByUidResult = $ProductDetailByUidInterface
            ->event($CurrentProductDTO->getEvent())
            ->offer($CurrentProductDTO->getOffer())
            ->variation($CurrentProductDTO->getVariation())
            ->modification($CurrentProductDTO->getModification())
            ->findResult();

        if(false === ($ProductDetailByUidResult instanceof ProductDetailByEventResult))
        {
            $logger->critical(sprintf('%s: Продукт по артикулу не найден', $content->article), [
                var_export($CurrentProductDTO, true),
                self::class.':'.__LINE__,
            ]);

            return new JsonResponse(['status' => 404], status: 404);
        }


        /** Нет в наличии */
        if(empty($content->price))
        {
            /** Выставляем стоимость по рыночной цене */

            $logger->critical(
                sprintf('%s: => %s', $content->article, 'Нет в наличии! Выставляем рыночную стоимость!'),
                [self::class.':'.__LINE__]);

            return new JsonResponse(['status' => 200]);
        }


        $price = floor($content->price / 50) * 50;

        if((int) $price === $content->price)
        {
            $price -= 50;
        }

        if(false === ($ProductDetailByUidResult->getProductPrice() instanceof Money))
        {
            /** Выставляем рекомендуемую стоимость */

            $logger->critical(
                sprintf('%s: => %s (рекомендуемая стоимость)', $content->article, $price),
                [self::class.':'.__LINE__]);

            return new JsonResponse(['status' => 200]);
        }

        if($price !== $ProductDetailByUidResult->getProductPrice()->getValue())
        {
            $logger->critical(
                sprintf('%s: %s => %s (рекомендуемая стоимость)',
                    $content->article,
                    $ProductDetailByUidResult->getProductPrice()->getValue(),
                    $price,
                ), [self::class.':'.__LINE__],
            );

            return new JsonResponse(['status' => 200]);
        }

        return new JsonResponse(['status' => 200]);
    }
}