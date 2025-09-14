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
use BaksDev\Products\Product\Repository\Search\AllProductsToIndex\AllProductsToIndexRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
final class IndexController extends AbstractController
{
    #[Route('/api/admin/products', name: 'api.admin.index', methods: ['GET', 'POST'])]
    public function index(
        Request $request,
        AllProductsToIndexRepository $AllProductsToIndexRepository,
        int $page = 0,
    ): Response
    {
        $result = $AllProductsToIndexRepository->findAll();

        $data = null;

        foreach($result as $item)
        {
            $arrName = explode(' ', $item->getproductName());
            $arrArticle = explode('-', $item->getProductArticle());

            /** Поиск по полному названию */

            $combined = [];

            foreach($arrName as $value)
            {
                $combined[$value] = true;
            }

            foreach($arrArticle as $value)
            {
                $combined[$value] = true;
            }

            $combined = array_keys($combined);

            $search = implode(' ', $combined);

            /** Здесь можно применить фильтр для поиска */

            $search = str_replace([' WL', ' SA', ' Z507', ' S AECO'], '', $search);

            $search = preg_replace_callback('/\b(1[3-9]|2[0-4])\b/', function($matches) {
                return 'R'.$matches[1];
            }, $search);

            $data[] = [
                'search' => $search,
                'article' => $item->getProductArticle(),
            ];
        }

        return new JsonResponse($data);
    }
}