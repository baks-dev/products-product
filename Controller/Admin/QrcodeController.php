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

declare(strict_types=1);

namespace BaksDev\Products\Product\Controller\Admin;

use BaksDev\Core\Controller\AbstractController;
use BaksDev\Core\Listeners\Event\Security\RoleSecurity;
use BaksDev\Core\Type\UidType\ParamConverter;
use BaksDev\Products\Product\Repository\ProductDetail\ProductDetailByUidInterface;
use BaksDev\Products\Product\Type\Event\ProductEventUid;
use BaksDev\Products\Product\Type\Offers\Id\ProductOfferUid;
use BaksDev\Products\Product\Type\Offers\Variation\Id\ProductVariationUid;
use BaksDev\Products\Product\Type\Offers\Variation\Modification\Id\ProductModificationUid;
use chillerlan\QRCode\QRCode;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
#[RoleSecurity('ROLE_PRODUCT')]
final class QrcodeController extends AbstractController
{
    #[Route('/admin/product/qrcode/{product}', name: 'admin.qrcode', methods: ['GET', 'POST'])]
    public function qrcode(
        ProductDetailByUidInterface $productInfo,
        #[ParamConverter(ProductEventUid::class)] ProductEventUid $product,
        #[ParamConverter(ProductOfferUid::class)] $offer = null,
        #[ParamConverter(ProductVariationUid::class)] $variation = null,
        #[ParamConverter(ProductModificationUid::class)] $modification = null,
    ): Response {

        $info = $productInfo->fetchProductDetailByEventAssociative(
            $product,
            $offer,
            $variation,
            $modification
        );

        if(!$info)
        {
            throw new InvalidArgumentException('Продукт не найден');
        }

        $data = null;

        if($modification)
        {
            $data = sprintf('%s', $modification);
        }

        if($data === null && $variation)
        {
            $data = sprintf('%s', $variation);
        }

        if($data === null && $offer)
        {
            $data = sprintf('%s', $offer);
        }

        /** Идентификатор События!!! продукта */
        if($data === null && $product)
        {
            $data = sprintf('%s', $product);
        }


        return $this->render(
            [
                'qrcode' => (new QRCode())->render($data),
                'item' => $info
            ]
        );
    }
}
