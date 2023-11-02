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

namespace BaksDev\Products\Product\Controller;

use BaksDev\Core\Controller\AbstractController;
use BaksDev\Core\Listeners\Event\Security\RoleSecurity;
use BaksDev\Core\Type\UidType\ParamConverter;
use BaksDev\Products\Category\Entity\ProductCategory;
use BaksDev\Products\Category\Repository\AllCategoryByMenu\AllCategoryByMenuInterface;
use BaksDev\Products\Category\Type\Id\ProductCategoryUid;
use BaksDev\Products\Product\Repository\AllProductsByCategory\AllProductsByCategoryInterface;
use BaksDev\Settings\Main\Repository\SettingsMain\SettingsMainInterface;
use BaksDev\Users\Profile\UserProfile\Type\Id\UserProfileUid;
use ReflectionAttribute;
use ReflectionClass;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;


#[AsController]
final class ExportController extends AbstractController
{

    #[Route('/export.xml', name: 'export.xml', methods: ['GET'])]
    public function yml(
        Request $request,
        SettingsMainInterface $settingsMain,
        AllProductsByCategoryInterface $productsByCategory
    ): Response
    {

//        dd($settingsMain->getSettingsMainAssociative($request->getHost(), $request->getLocale()));

        $response = $this->render([
            'settings' => $settingsMain->getSettingsMainAssociative($request->getHost(), $request->getLocale()),
            'products' => $productsByCategory->fetchAllProductByCategory()]
        );
        $response->headers->set('Content-Type', 'text/xml');

        return $response;
    }


    #[Route('/export.json', name: 'export.json', methods: ['GET'])]
    public function export(
        #[ParamConverter(ProductCategoryUid::class)] $category,
        AllProductsByCategoryInterface $productsByCategory
    ): Response
    {
        //dd($productsByCategory->fetchAllProductByCategory($category));

        $response = $this->render(['urls' => $productsByCategory->fetchAllProductByCategory($category)]);
        $response->headers->set('Content-Type', 'text/xml');

        return $response;
    }

}
