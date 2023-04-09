<?php
/*
 *  Copyright 2023.  Baks.dev <admin@baks.dev>
 *
 *  Permission is hereby granted, free of charge, to any person obtaining a copy
 *  of this software and associated documentation files (the "Software"), to deal
 *  in the Software without restriction, including without limitation the rights
 *  to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 *  copies of the Software, and to permit persons to whom the Software is furnished
 *  to do so, subject to the following conditions:
 *
 *  The above copyright notice and this permission notice shall be included in all
 *  copies or substantial portions of the Software.
 *
 *  THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 *  IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 *  FITNESS FOR A PARTICULAR PURPOSE AND NON INFRINGEMENT. IN NO EVENT SHALL THE
 *  AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 *  LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 *  OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 *  THE SOFTWARE.
 */

namespace BaksDev\Products\Product\UseCase\Admin\NewEdit\Property;

use BaksDev\Core\Services\Fields\FieldsChoice;
use BaksDev\Core\Services\Fields\FieldsChoiceInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

/* Форма свойств продукта */


final class PropertyCollectionForm extends AbstractType
{
	
	private FieldsChoice $fieldsChoice;
	
	public function __construct(FieldsChoice $fieldsChoice) {
		
		$this->fieldsChoice = $fieldsChoice;
	}
	
	
	public function buildForm(FormBuilderInterface $builder, array $options) : void
	{
		
		//        $builder->add(
		//          'section',
		//          TextType::class,
		//          ['mapped' => false]
		//        );
		
		
		//dump($builder);
		
		/*$builder->add(
			'value',
			TextType::class,
		
		);*/
		
		$builder->addEventListener(
			FormEvents::PRE_SET_DATA,
			function(FormEvent $event) use ($options)
			{
				$data = $event->getData();
				$form = $event->getForm();
				
				if($data)
				{
					$propertyCategory = $options['properties'];
					$id = (string) $data->getField();
					
					if(empty($id) || !isset($propertyCategory[$id]))
					{
						return;
					}
					
					$propCat = $propertyCategory[$id];
					
					/* СЕКЦИЯ */
					$form->add(
						'section',
						HiddenType::class,
						[
							//'mapped' => false,
							//'data' => (string) $propCat->sectionUid,
							'label' => $propCat->sectionTrans,
						]
					);
					
					$fieldType = $this->fieldsChoice->getChoice($propCat->fieldType);
					
					$form->add
					(
						'value',
						$fieldType->form(),
						[
							'label' => $propCat->fieldTrans,
							'required' => $propCat->fieldRequired,
						]
					);
					
					
					//dump($form);
					//dump($fieldType->form());
					//dd($propCat);
					
					
//					match ($propCat->fieldType)
//					{
//						/* INTEGER */
//						'integer' => $form->add
//						(
//							'value',
//							IntegerType::class,
//							[
//								'label' => $propCat->fieldTrans,
//								'required' => $propCat->fieldRequired,
//							]
//						)
//					,
//
//						/* MAIL */
//						'mail' => $form->add
//						(
//							'value',
//							EmailType::class,
//							[
//								'label' => $propCat->fieldTrans,
//								'required' => $propCat->fieldRequired,
//								'help' => $propCat->fieldDesc,
//							]
//						),
//
//						/* PHONE */
//						'phone' => $form->add
//						(
//							'value',
//							TextType::class,
//							[
//								'label' => $propCat->fieldTrans,
//								'required' => $propCat->fieldRequired,
//								'attr' =>
//									[
//										'placeholder' => $propCat->fieldDesc,
//									],
//							]
//						),
//
//						/* SELECT */
//						'select' => $form->add
//						(
//							'value',
//							ChoiceType::class,
//							[
//								'label' => $propCat->fieldTrans,
//								'required' => $propCat->fieldRequired,
//								'placeholder' => $propCat->fieldDesc,
//							]
//						),
//
//						/* TEXTAREA */
//						'textarea' => $form->add(
//							'value',
//							TextareaType::class,
//							[
//								'label' => $propCat->fieldTrans,
//								'required' => $propCat->fieldRequired,
//								'help' => $propCat->fieldDesc,
//							]
//						),
//
//						default => $form->add
//						(
//							'value',
//							TextType::class,
//							[
//								'label' => $propCat->fieldTrans,
//								'required' => $propCat->fieldRequired,
//							]
//						)
//
//					};
					
				}
			}
		);
		
	}
	
	
	public function configureOptions(OptionsResolver $resolver) : void
	{
		$resolver->setDefaults
		(
			[
				'data_class' => PropertyCollectionDTO::class,
				'properties' => null,
			]
		);
	}
	
}
