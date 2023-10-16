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

namespace BaksDev\Products\Product\UseCase\Admin\NewEdit\Seo;

use BaksDev\Core\Type\Locale\Locale;
use BaksDev\Products\Product\Entity\Seo\ProductSeoInterface;
use ReflectionProperty;
use Symfony\Component\Validator\Constraints as Assert;

final class SeoCollectionDTO implements ProductSeoInterface
{
	/** Локаль */
	#[Assert\NotBlank]
	private Locale $local;
	
	/** Шаблон META TITLE (строка с точкой, запятой, нижнее подчеркивание тире процент скобки) */
	#[Assert\Regex(pattern: '/^[\w \.\,\_\-\(\)\%]+$/iu')]
	private ?string $title = null;
	
	/** Шаблон META DESCRIPTION (строка с точкой, запятой, нижнее подчеркивание тире процент скобки) */
	#[Assert\Regex(pattern: '/^[\w \.\,\_\-\(\)\%]+$/iu')]
	private ?string $description = null;
	
	/** Шаблон META KEYWORDS (строка с точкой, запятой, нижнее подчеркивание тире процент скобки) */
	#[Assert\Regex(pattern: '/^[\w \.\,\_\-\(\)\%]+$/iu')]
	private ?string $keywords = null;
	
	
	/**
	 * @return Locale
	 */
	public function getLocal() : Locale
	{
		return $this->local;
	}
	
	
	/** Локаль */

    public function setLocal(Locale|string $local) : void
    {
        if(!(new ReflectionProperty(self::class, 'local'))->isInitialized($this))
        {
            $this->local = $local instanceof Locale ? $local : new Locale($local);
        }
    }
	
	
	/**
	 * @return string|null
	 */
	public function getTitle() : ?string
	{
		return $this->title;
	}
	
	
	/**
	 * @param string|null $title
	 */
	public function setTitle(?string $title) : void
	{
		$this->title = $title;
	}
	
	
	/**
	 * @return string|null
	 */
	public function getKeywords() : ?string
	{
		return $this->keywords;
	}
	
	
	/**
	 * @param string|null $keywords
	 */
	public function setKeywords(?string $keywords) : void
	{
		$this->keywords = $keywords;
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
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	//
	//    /**
	//     * @return Locale
	//     */
	//    public function getLocal() : Locale
	//    {
	//        return $this->local;
	//    }
	//
	//    /**
	//     * @return string
	//     */
	//    public function getTitle(): string
	//    {
	//        return $this->title;
	//    }
	//
	//    /**
	//     * @return string
	//     */
	//    public function getKeywords(): string
	//    {
	//        return $this->keywords;
	//    }
	//
	//    /**
	//     * @return string
	//     */
	//    public function getDescription(): string
	//    {
	//        return $this->description;
	//    }
	//
	
}