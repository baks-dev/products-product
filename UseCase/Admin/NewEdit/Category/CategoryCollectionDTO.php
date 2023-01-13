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

namespace App\Module\Products\Product\UseCase\Admin\NewEdit\Category;

use App\Module\Products\Category\Type\Id\CategoryUid;
use App\Module\Products\Product\Entity\Category\CategoryInterface;
use Symfony\Component\Validator\Constraints as Assert;

final class CategoryCollectionDTO implements CategoryInterface
{
    
    /** ID категории продукта */
    #[Assert\Uuid]
    private ?CategoryUid $category = null;
    
    private bool $root = false;
    
    /**
     * @return ?CategoryUid
     */
    public function getCategory() : ?CategoryUid
    {
        return $this->category;
    }
    
    /**
     * @param CategoryUid $category
     */
    public function setCategory(CategoryUid $category) : void
    {
        $this->category = $category;
    }
    
    /**
     * @return bool
     */
    public function isRoot() : bool
    {
        return $this->root;
    }
    
    /**
     * @param bool $root
     */
    public function setRoot(bool $root) : void
    {
        $this->root = $root;
    }
    
    public function rootCategory() : void
    {
        $this->root = true;
    }

}