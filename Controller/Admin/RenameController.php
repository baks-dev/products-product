<?php
/*
 *  Copyright Baks.dev <admin@baks.dev>
 *
 *  Licensed under the Apache License, Version 2.0 (the "License");
 *  you may not use this file except in compliance with the License.
 *  You may obtain a copy of the License at
 *
 *  http://www.apache.org/licenses/LICENSE-2.0
 *
 *  Unless required by applicable law or agreed to in writing, software
 *  distributed under the License is distributed on an "AS IS" BASIS,
 *  WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 *  See the License for the specific language governing permissions and
 *   limitations under the License.
 *
 */

namespace BaksDev\Products\Product\Controller\Admin;


//use App\Module\Products\Category\Repository\CategoryPropertyById\CategoryPropertyByIdInterface;
//use App\Module\Products\Product\Entity as ProductEntity;
//use App\Module\Products\Product\UseCase\Admin\NewEdit\ProductDTO;
//use App\Module\Products\Product\UseCase\Admin\NewEdit\ProductForm;
//use App\Module\Products\Product\UseCase\Admin\Rename\RenameProductDTO;
//use App\Module\Products\Product\UseCase\Admin\Rename\RenameProductForm;
//use App\Module\Products\Product\UseCase\Admin\Rename\RenameProductHandler;
//use App\Module\Products\Product\UseCase\ProductAggregate;
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
use Symfony\Component\Routing\Annotation\Route;


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
        $form = $this->createForm(RenameProductForm::class, $RenameProductDTO,
            ['action' => $this->generateUrl('products-product:admin.rename', ['id' => $RenameProductDTO->getEvent()])]
        );
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid() && $form->has('rename_product'))
        {
            $this->refreshTokenForm($form);

            $handle = $renameProductHandler->handle($RenameProductDTO);

            $this->addFlash
            (
                'admin.page.edit',
                $handle instanceof Product ? 'admin.success.rename' : 'admin.danger.rename',
                'admin.products.product',
                $handle
            );

            return $this->redirectToRoute('products-product:admin.index');

        }

        return $this->render(['form' => $form->createView()]);
    }
}