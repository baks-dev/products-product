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

namespace BaksDev\Products\Product\UseCase\Admin\Rename\Trans;

use BaksDev\Core\Type\Locale\Locale;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class ProductTransForm extends AbstractType
{
	
	public function buildForm(FormBuilderInterface $builder, array $options) : void
	{
		$builder->add('local', HiddenType::class);
		
		$builder->get('local')->addModelTransformer(
			new CallbackTransformer(
				function($price) {
					return $price instanceof Locale ? $price->getLocalValue() : $price;
				},
				function($price) {
					return new Locale($price);
				}
			)
		);
		
		$builder->add('name', TextType::class);

	}
	
	
	public function configureOptions(OptionsResolver $resolver) : void
	{
		$resolver->setDefaults([
				'data_class' => ProductTransDTO::class,
			]
		);
	}
	
}
