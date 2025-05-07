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
 *
 */

declare(strict_types=1);

namespace BaksDev\Products\Product\Twig;

use BaksDev\Core\Twig\TemplateExtension;
use BaksDev\Products\Product\Repository\BestSellerProducts\BestSellerProductsInterface;
use Twig\Environment;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class ProductBestSellerExtension extends AbstractExtension
{
    public function __construct(
        private readonly TemplateExtension $template,
        private readonly BestSellerProductsInterface $bestSellerProducts,
    ) {}

    public function getFunctions(): array
    {
        return [
            new TwigFunction('render_all_best_seller',
                [$this, 'renderAllBestSeller'],
                ['needs_environment' => true, 'is_safe' => ['html']]),
            new TwigFunction('render_one_best_seller',
                [$this, 'renderOneBestSeller'],
                ['needs_environment' => true, 'is_safe' => ['html']]),
        ];
    }

    /** Отображает блок с несколькими продуктами, сортируя их по убыванию количества резервов на данную продукцию */
    public function renderAllBestSeller(Environment $twig, ?string $category = null, ?string $invariable = null): string
    {

        /** Если передан invariable id - исключает продукт из результата */
        if(false === is_null($invariable))
        {
            $this->bestSellerProducts->byInvariable($invariable);
        }

        $bestSellers = $this->bestSellerProducts
            ->forCategory($category)
            ->maxResult(20)
            ->toArray();

        if(true === empty($bestSellers))
        {
            return '';
        }

        $path = $this->template->extends('@products-product:render/all_best_seller/template.html.twig');

        return $twig->render
        (
            name: $path,
            context: [
                'bestSellers' => $bestSellers,
            ]
        );
    }

    /** Отображает блок с одним продуктом, с самым большим количеством резервов на данную продукцию */
    public function renderOneBestSeller(Environment $twig, ?string $category = null, ?string $invariable = null): string
    {

        /** Если передан invariable id - исключает продукт из результата */
        if(false === is_null($invariable))
        {
            $this->bestSellerProducts->byInvariable($invariable);
        }

        $bestSellers = $this->bestSellerProducts
            ->forCategory($category)
            ->maxResult(2)
            ->toArray();

        if(true === empty($bestSellers))
        {
            return '';
        }

        $path = $this->template->extends('@products-product:render/one_best_seller/template.html.twig');

        return $twig->render
        (
            name: $path,
            context: [
                'bestSellers' => $bestSellers,
                'invariable' => $invariable,
            ]
        );
    }
}
