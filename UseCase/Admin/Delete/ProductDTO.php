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

namespace BaksDev\Products\Product\UseCase\Admin\Delete;

use BaksDev\Products\Product\Entity\Event\ProductEventInterface;
use BaksDev\Products\Product\Type\Event\ProductEventUid;

use Symfony\Component\Validator\Constraints as Assert;

final class ProductDTO implements ProductEventInterface
{
	/**
	 * Идентификатор события
	 *
	 * @var ProductEventUid|null
	 */
	#[Assert\Uuid]
	private ?ProductEventUid $id = null;
	
	//    private ?ParentCategoryUid $parent;
	
	#[Assert\Valid]
	private Modify\ModifyDTO $modify;
	
	#[Assert\Valid]
	private Info\InfoDTO $info;
	
	
	public function __construct()
	{
		$this->modify = new Modify\ModifyDTO();
		$this->info = new Info\InfoDTO();
	}
	
	
	/**
	 * @return ProductEventUid|null
	 */
	public function getEvent() : ?ProductEventUid
	{
		return $this->id;
	}
	
	
	/**
	 * @param ProductEventUid $id
	 */
	public function setId(ProductEventUid $id) : void
	{
		$this->id = $id;
	}
	
	
	/* Modify  */
	
	/**
	 * @return Modify\ModifyDTO
	 */
	public function getModify() : Modify\ModifyDTO
	{
		return $this->modify;
	}
	
	
	/** Метод для инициализации и маппинга сущности на DTO в коллекции  */
	public function getModifyClass() : Modify\ModifyDTO
	{
		return new Modify\ModifyDTO();
	}
	
	
	/**
	 * @return Info\InfoDTO
	 */
	public function getInfo() : Info\InfoDTO
	{
		return $this->info;
	}
	
	
	public function setInfo(Info\InfoDTO $info) : void
	{
		$this->info = $info;
	}
	
	//    /**
	//     * @param ModifyDTO $Modify
	//     */
	//    public function setModify(ModifyDTO $Modify) : void
	//    {
	//        $this->modify = $Modify;
	//    }
	
}

