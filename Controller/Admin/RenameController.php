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

namespace BaksDev\Products\Product\Controller\Admin;

use BaksDev\Core\Controller\AbstractController;
use BaksDev\Core\Listeners\Event\Security\RoleSecurity;
use BaksDev\Products\Product\Entity\Event\ProductEvent;
use BaksDev\Products\Product\Entity\Product;
use BaksDev\Products\Product\UseCase\Admin\Rename\RenameProductDTO;
use BaksDev\Products\Product\UseCase\Admin\Rename\RenameProductForm;
use BaksDev\Products\Product\UseCase\Admin\Rename\RenameProductHandler;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
#[RoleSecurity('ROLE_PRODUCT_RENAME')]
final class RenameController extends AbstractController
{
    /**
     * Переименовать товар
     */
    #[Route('/admin/product/rename/{id}', name: 'admin.rename', methods: ['GET', 'POST'])]
    public function edit(
        #[MapEntity] ProductEvent $Event,
        Request $request,
        RenameProductHandler $renameProductHandler
    ): Response
    {

        $RenameProductDTO = new RenameProductDTO();
        $Event->getDto($RenameProductDTO);

        /* Форма переименования */
        $form = $this->createForm(
            RenameProductForm::class,
            $RenameProductDTO,
            ['action' => $this->generateUrl('products-product:admin.rename', ['id' => $RenameProductDTO->getEvent()])]
        );
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid() && $form->has('rename_product'))
        {
            $this->refreshTokenForm($form);

            $handle = $renameProductHandler->handle($RenameProductDTO);

            $this->addFlash(
                'page.edit',
                $handle instanceof Product ? 'success.rename' : 'danger.rename',
                'products-product.admin',
                $handle
            );

            return $this->redirectToRoute('products-product:admin.index');

        }

        return $this->render(['form' => $form->createView()]);
    }
}
