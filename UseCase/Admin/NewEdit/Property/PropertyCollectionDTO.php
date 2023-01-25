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

namespace BaksDev\Products\Product\UseCase\Admin\NewEdit\Property;

use App\Module\Products\Category\Type\Section\Field\Id\FieldUid;
use BaksDev\Products\Product\Entity\Property\PropertyInterface;
use Symfony\Component\Validator\Constraints as Assert;

final class PropertyCollectionDTO implements PropertyInterface
{
    /** Связь на поле из категории */
    #[Assert\Uuid]
    private ?FieldUid $field = null;
    
    /** Заполненное значение */
    private ?string $value = null;
    
    
    /* Вспомогательные свойства */
    private ?string $section = null;
    
    /**
     * @return string|null
     */
    public function getValue() : ?string
    {
        return $this->value;
    }
    
    /**
     * @param string|null $value
     */
    public function setValue(?string $value) : void
    {
        $this->value = $value;
    }
    
    /**
     * @return FieldUid|null
     */
    public function getField() : ?FieldUid
    {
        return $this->field;
    }
    

    public function setField(FieldUid $field) : void
    {
        $this->field = $field;
    }
    

    public function setSection(string $section) : void
    {
        $this->section = $section;
    }
    
    /**
     * @return string|null
     */
    public function getSection() : ?string
    {
        return $this->section;
    }
    
    

}