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

namespace BaksDev\Products\Product\Forms\ProductFilter\Admin;

use BaksDev\Products\Category\Type\Id\ProductCategoryUid;
use BaksDev\Products\Product\Forms\ProductFilter\ProductFilterInterface;
use BaksDev\Users\Profile\UserProfile\Type\Id\UserProfileUid;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;

final class ProductFilterDTO implements ProductFilterInterface
{
    public const profile = 'qwouuufslq';
    public const category = 'abaxvfsuto';
    
    private Request $request;
    
    public function __construct(Request $request) {
        $this->request = $request;
    }
    
    
    /** Категория */
    private ?ProductCategoryUid $category = null;
    
    /** Профиль */
    private ?UserProfileUid $profile = null;
    

    public function setProfile(?UserProfileUid $profile) : void
    {
        if($profile === null) { $this->request->getSession()->remove(self::profile); }
        $this->profile = $profile;
    }
    

    public function setCategory(?ProductCategoryUid $category) : void
    {
        if($category === null) { $this->request->getSession()->remove(self::category); }
        $this->category = $category;
    }
    

    public function getCategory() : ?ProductCategoryUid
    {
        return $this->category ?: $this->request->getSession()->get(self::category);
    }
    

    public function getProfile() : ?UserProfileUid
    {
        return $this->profile ?: $this->request->getSession()->get(self::profile);
    }
    
}

