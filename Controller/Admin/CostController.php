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

namespace BaksDev\Products\Product\Controller\Admin;

use BaksDev\Core\Controller\AbstractController;
use BaksDev\Core\Listeners\Event\Security\RoleSecurity;
use BaksDev\Products\Product\UseCase\Admin\Cost\ProductCostDispatch;
use BaksDev\Products\Product\UseCase\Admin\Cost\ProductCostDTO;
use BaksDev\Products\Product\UseCase\Admin\Cost\ProductCostForm;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
#[RoleSecurity('ROLE_PRODUCT_COST')]
final class CostController extends AbstractController
{
    #[Route('/admin/product/cost', name: 'admin.cost', methods: ['GET', 'POST'])]
    public function news(
        Request $request,
        ProductCostDispatch $ProductCostHandler,
    ): Response
    {

        $ProductCostDTO = new ProductCostDTO();

        // Форма
        $form = $this->createForm(ProductCostForm::class, $ProductCostDTO, [
            'action' => $this->generateUrl('products-product:admin.cost'),
        ]);

        $form->handleRequest($request);
        $view = $form->createView();

        if($form->isSubmitted() && $form->isValid() && $form->has('product_cost'))
        {
            $this->refreshTokenForm($form);

            $handle = $ProductCostHandler->handle($ProductCostDTO);

            $this->addFlash(
                'page.cost',
                $handle === true ? 'success.cost' : 'danger.cost',
                'products-product.admin',
            );

            return $this->redirectToReferer();
        }

        return $this->render(['form' => $view]);
    }
}
