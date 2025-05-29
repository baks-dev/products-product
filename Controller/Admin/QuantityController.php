<?php
/*
 * Copyright 2025.  Baks.dev <admin@baks.dev>
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
use BaksDev\Core\Messenger\MessageDispatch;
use BaksDev\Products\Product\Forms\ProductsQuantityForm\ProductsQuantityDTO;
use BaksDev\Products\Product\Messenger\Quantity\FindProductsForQuantityUpdateMessage;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;
use BaksDev\Products\Product\Forms\ProductsQuantityForm\ProductsQuantityForm;

#[AsController]
#[RoleSecurity(['ROLE_PRODUCT', 'ROLE_PRODUCT_QUANTITY'])]
final class QuantityController extends AbstractController
{
    #[Route('/admin/products/quantity/', name: 'admin.quantity', methods: ['GET', 'POST',])]
    public function index(
        Request $request,
        MessageDispatch $messageDispatch,
    ): Response
    {
        $dto = new ProductsQuantityDTO();

        $form = $this
            ->createForm(
                type: ProductsQuantityForm::class,
                data: $dto,
                options: ['action' => $this->generateUrl('products-product:admin.quantity')]
            )
            ->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {
            /** @var ProductsQuantityDTO $data */
            $data = $form->getData();

            $message = new FindProductsForQuantityUpdateMessage()
                ->setCategory($data->getCategory())
                ->setOffer($data->getOffer())
                ->setVariation($data->getVariation())
                ->setModification($data->getModification())
                ->setQuantity($data->getQuantity());

            /** Отправляем сообщение в шину */
            $messageDispatch->dispatch(
                message: $message,
                transport: 'products-product'
            );

            /** todo какой тип? */
            $this->addFlash
            (
                'admin.page.quantity',
                'Запрос на изменение количества принят',
                'admin.products.product',
            );

            return $this->redirectToReferer();
        }

        return $this->render(['form' => $form->createView(),]);
    }
}