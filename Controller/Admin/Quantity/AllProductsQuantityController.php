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

namespace BaksDev\Products\Product\Controller\Admin\Quantity;

use BaksDev\Core\Controller\AbstractController;
use BaksDev\Core\Listeners\Event\Security\RoleSecurity;
use BaksDev\Core\Messenger\MessageDispatch;
use BaksDev\Products\Product\Forms\AllProductsQuantityForm\AllProductsQuantityDTO;
use BaksDev\Products\Product\Forms\AllProductsQuantityForm\AllProductsQuantityForm;
use BaksDev\Products\Product\Messenger\Quantity\FindProductsForQuantityUpdateMessage;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
#[RoleSecurity(['ROLE_PRODUCT_QUANTITY'])]
final class AllProductsQuantityController extends AbstractController
{
    #[Route('/admin/products/quantity/', name: 'admin.quantity.all', methods: ['GET', 'POST',])]
    public function index(
        Request $request,
        MessageDispatch $messageDispatch,
    ): Response
    {
        $dto = new AllProductsQuantityDTO();

        $form = $this
            ->createForm(
                type: AllProductsQuantityForm::class,
                data: $dto,
                options: ['action' => $this->generateUrl('products-product:admin.quantity.all')]
            )
            ->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {
            /** @var AllProductsQuantityDTO $data */
            $data = $form->getData();

            $message = new FindProductsForQuantityUpdateMessage(
                $data->getCategory(),
                $data->getQuantity(),
                $data->getOffer(),
                $data->getVariation(),
                $data->getModification(),
            );

            /** Отправляем сообщение в шину */
            $messageDispatch->dispatch(
                message: $message,
                transport: 'products-product'
            );

            $this->addFlash
            (
                'page.quantity',
                'success.quantity.all',
                'products-product.admin',
            );

            return $this->redirectToReferer();
        }

        return $this->render(['form' => $form->createView(),]);
    }
}