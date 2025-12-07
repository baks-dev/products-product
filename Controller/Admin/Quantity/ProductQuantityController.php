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

namespace BaksDev\Products\Product\Controller\Admin\Quantity;

use BaksDev\Core\Controller\AbstractController;
use BaksDev\Core\Listeners\Event\Security\RoleSecurity;
use BaksDev\Core\Type\UidType\ParamConverter;
use BaksDev\Products\Product\Forms\ProductQuantityForm\ProductQuantityDTO;
use BaksDev\Products\Product\Forms\ProductQuantityForm\ProductQuantityForm;
use BaksDev\Products\Product\Repository\ProductDetail\ProductDetailByEventInterface;
use BaksDev\Products\Product\Repository\ProductDetail\ProductDetailByEventResult;
use BaksDev\Products\Product\Type\Event\ProductEventUid;
use BaksDev\Products\Product\Type\Offers\Id\ProductOfferUid;
use BaksDev\Products\Product\Type\Offers\Variation\Id\ProductVariationUid;
use BaksDev\Products\Product\Type\Offers\Variation\Modification\Id\ProductModificationUid;
use BaksDev\Products\Product\UseCase\Admin\Quantity\Offer\UpdateOfferQuantityDTO;
use BaksDev\Products\Product\UseCase\Admin\Quantity\Offer\UpdateOfferQuantityHandler;
use BaksDev\Products\Product\UseCase\Admin\Quantity\Offer\Variation\Modification\UpdateModificationQuantityDTO;
use BaksDev\Products\Product\UseCase\Admin\Quantity\Offer\Variation\Modification\UpdateModificationQuantityHandler;
use BaksDev\Products\Product\UseCase\Admin\Quantity\Offer\Variation\UpdateVariationQuantityDTO;
use BaksDev\Products\Product\UseCase\Admin\Quantity\Offer\Variation\UpdateVariationQuantityHandler;
use BaksDev\Products\Product\UseCase\Admin\Quantity\UpdateProductQuantityDTO;
use BaksDev\Products\Product\UseCase\Admin\Quantity\UpdateProductQuantityHandler;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
#[RoleSecurity(['ROLE_PRODUCT_QUANTITY'])]
final class ProductQuantityController extends AbstractController
{
    #[Route('/admin/product/quantity/{product}/{offer}/{variation}/{modification}', name: 'admin.quantity.product', methods: ['GET', 'POST'])]
    public function index(
        Request $request,
        UpdateModificationQuantityHandler $UpdateModificationQuantityHandler,
        UpdateVariationQuantityHandler $UpdateVariationQuantityHandler,
        UpdateOfferQuantityHandler $UpdateOfferQuantityHandler,
        UpdateProductQuantityHandler $UpdateProductQuantityHandler,
        ProductDetailByEventInterface $ProductDetailByUidRepository,
        #[ParamConverter(ProductEventUid::class)] ProductEventUid $product,
        #[ParamConverter(ProductOfferUid::class)] ?ProductOfferUid $offer = null,
        #[ParamConverter(ProductVariationUid::class)] ?ProductVariationUid $variation = null,
        #[ParamConverter(ProductModificationUid::class)] ?ProductModificationUid $modification = null,
    ): Response
    {
        /** @var ProductDetailByEventResult $productDetailByUid */
        $productDetailByUid = $ProductDetailByUidRepository
            ->event($product)
            ->offer($offer)
            ->variation($variation)
            ->modification($modification)
            ->findResult();

        $productQuantityFormDTO = new ProductQuantityDTO();

        if(false !== $productDetailByUid)
        {
            $productQuantityFormDTO
                ->setTotal($productDetailByUid->getProductQuantity())
                ->setReserve($productDetailByUid->getProductReserve());
        }

        $quantityForm = $this
            ->createForm(
                type: ProductQuantityForm::class,
                data: $productQuantityFormDTO,
                options: ['action' => $this->generateUrl('products-product:admin.quantity.product', [
                    'product' => $product,
                    'offer' => $offer,
                    'variation' => $variation,
                    'modification' => $modification
                ])]
            )
            ->handleRequest($request);

        if($quantityForm->isSubmitted() && $quantityForm->isValid() && $quantityForm->has('product_quantity_form_edit'))
        {
            $this->refreshTokenForm($quantityForm);

            /** @var ProductQuantityDTO $data */
            $data = $quantityForm->getData();

            if(false === empty($modification))
            {
                $updateModificationQuantityDTO = new UpdateModificationQuantityDTO(
                    $modification,
                    $data->getTotal(),
                    $data->getReserve(),
                );

                $handle = $UpdateModificationQuantityHandler->handle($updateModificationQuantityDTO);
            }

            if(empty($modification) && false === empty($variation))
            {
                $updateVariationQuantityDTO = new UpdateVariationQuantityDTO(
                    $variation,
                    $data->getTotal(),
                    $data->getReserve(),
                );

                $handle = $UpdateVariationQuantityHandler->handle($updateVariationQuantityDTO);
            }

            if(empty($varaition) && false === empty($offer))
            {
                $updateOfferQuantityDTO = new UpdateOfferQuantityDTO(
                    $offer,
                    $data->getTotal(),
                    $data->getReserve(),
                );

                $handle = $UpdateOfferQuantityHandler->handle($updateOfferQuantityDTO);
            }

            if(empty($offer))
            {
                $updateProductQuantityDTO = new UpdateProductQuantityDTO(
                    $product,
                    $data->getTotal(),
                    $data->getReserve(),
                );

                $handle = $UpdateProductQuantityHandler->handle($updateProductQuantityDTO);
            }

            $this->addFlash
            (
                'page.quantity',
                is_string($handle) || is_bool($handle) ? 'danger.quantity.product' : 'success.quantity.product',
                'products-product.admin',
            );

            return $this->redirectToRoute('products-product:admin.index');
        }

        return $this->render(['form' => $quantityForm->createView()]);
    }
}