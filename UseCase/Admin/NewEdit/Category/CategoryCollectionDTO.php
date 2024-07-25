<?php
/*
 *  Copyright 2022.  Baks.dev <admin@baks.dev>
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

namespace BaksDev\Products\Product\UseCase\Admin\NewEdit\Category;

use BaksDev\Products\Category\Type\Id\CategoryProductUid;
use BaksDev\Products\Product\Entity\Category\ProductCategoryInterface;
use Symfony\Component\Validator\Constraints as Assert;

/** @see ProductCategory */
final class CategoryCollectionDTO implements ProductCategoryInterface
{
    /** ID категории продукта */
    #[Assert\Uuid]
    private ?CategoryProductUid $category = null;

    private bool $root = false;


    public function getCategory(): ?CategoryProductUid
    {
        return $this->category;
    }


    public function setCategory(CategoryProductUid|string $category): void
    {
        if(is_string($category))
        {
            $category = new CategoryProductUid($category);
        }

        $this->category = $category;
    }


    /**
     * @return bool
     */
    public function getRoot(): bool
    {
        return $this->root;
    }


    /**
     * @param bool $root
     */
    public function setRoot(bool $root): void
    {
        $this->root = $root;
    }


    public function rootCategory(): void
    {
        $this->root = true;
    }

}
