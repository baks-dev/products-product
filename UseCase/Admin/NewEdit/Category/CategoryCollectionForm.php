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

use BaksDev\Products\Category\Repository\CategoryChoice\CategoryChoiceInterface;
use BaksDev\Products\Category\Type\Id\ProductCategoryUid;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ButtonType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class CategoryCollectionForm extends AbstractType
{
	
	private CategoryChoiceInterface $category;
	
	
	public function __construct(CategoryChoiceInterface $category)
	{
		$this->category = $category;
	}
	
	
	public function buildForm(FormBuilderInterface $builder, array $options) : void
	{
		
		$builder
			->add('category', ChoiceType::class, [
				'choices' => $this->category->get(),
				'choice_value' => function(?ProductCategoryUid $type) {
					return $type?->getValue();
				},
				'choice_label' => function(?ProductCategoryUid $type) {
					return $type?->getOptions();
				},
				
				'label' => false,
				'expanded' => false,
				'multiple' => false,
				'required' => true,
			])
		;
		
		$builder->add
		(
			'DeleteCategory',
			ButtonType::class,
			[
				'label_html' => true,
			]
		);
		
	}
	
	
	public function configureOptions(OptionsResolver $resolver) : void
	{
		$resolver->setDefaults
		(
			[
				'data_class' => CategoryCollectionDTO::class,
			]
		);
	}
	
}
