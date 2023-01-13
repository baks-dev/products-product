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

namespace App\Module\Products\Product\UseCase\Admin\NewEdit\Trans;

use App\Module\Products\Product\Entity\Trans\TransInterface;
use App\System\Type\Locale\Locale;
use Symfony\Component\Validator\Constraints as Assert;

final class ProductTransDTO implements TransInterface
{
    /**
     * @var Locale
     */
    private Locale $local;

    /** Название раздела (строка с точкой, нижнее подчеркивание тире процент скобки) */
    #[Assert\NotBlank]
    #[Assert\Regex(pattern: '/^[\w \.\_\-\(\)\%]+$/iu')]
    private ?string $name;
    
    /** Краткое описание */
    //#[Assert\Regex(pattern: '/^[\w \.\_\-\(\)\%]+$/iu')]
    private ?string $preview = null;
    
    /** Детальное описание */
    //#[Assert\Regex(pattern: '/^[\w \.\_\-\(\)\%]+$/iu')]
    private ?string $description = null;
    
    /**
     * @return Locale
     */
    public function getLocal() : Locale
    {
        return $this->local;
    }
    
    /**
     * @param string|Locale $local
     */
    public function setLocal(string|Locale $local) : void
    {
        $this->local = $local instanceof Locale ? $local : new Locale($local) ;
    }
    
    /**
     * @return string|null
     */
    public function getName() : ?string
    {
        return $this->name;
    }
    
    /**
     * @param string|null $name
     */
    public function setName(?string $name) : void
    {
        $this->name = $name;
    }
    
    /**
     * @return string|null
     */
    public function getDescription() : ?string
    {
        return $this->description;
    }
    
    /**
     * @param string|null $description
     */
    public function setDescription(?string $description) : void
    {
        $this->description = $description;
    }
    
    /**
     * @return string|null
     */
    public function getPreview() : ?string
    {
        return $this->preview;
    }
    
    /**
     * @param string|null $preview
     */
    public function setPreview(?string $preview) : void
    {
        $this->preview = $preview;
    }
    

    
    
    
}

