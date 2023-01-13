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

namespace App\Module\Products\Product\UseCase\Admin\NewEdit\Offers\Offer\Quantity;

use App\Module\Products\Product\Entity\Offers\Offer\Quantity\ProductQuantityInterface;
use Symfony\Component\Validator\Constraints as Assert;

final class QuantityDTO implements ProductQuantityInterface
{
    /** В наличие */
    private ?int $quantity = null; // 0 - нет в наличие
    
    /** Резерв */
    private ?int $reserve = null;

    public function getQuantity() : ?int
    {
        return $this->quantity;
    }
    

    public function setQuantity(?int $quantity) : void
    {
        $this->quantity = $quantity;
    }
    

    public function getReserve() : ?int
    {
        return $this->reserve;
    }
    

    public function setReserve(?int $reserve) : void
    {
        
        $this->reserve = $reserve;
    }
    
}

