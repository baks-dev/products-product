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

declare(strict_types=1);

namespace BaksDev\Products\Product\Forms\ProductCategoryFilter\User;

use BaksDev\Core\Services\Fields\FieldsChoice;
use BaksDev\Core\Type\Field\InputField;
use BaksDev\Products\Category\Repository\AllFilterFieldsByCategory\AllFilterFieldsByCategoryInterface;
use BaksDev\Products\Category\Repository\ModificationFieldsCategoryChoice\ModificationFieldsCategoryChoiceInterface;
use BaksDev\Products\Category\Repository\OfferFieldsCategoryChoice\OfferFieldsCategoryChoiceInterface;
use BaksDev\Products\Category\Repository\VariationFieldsCategoryChoice\VariationFieldsCategoryChoiceInterface;
use BaksDev\Products\Category\Type\Id\ProductCategoryUid;
use BaksDev\Products\Category\Type\Offers\Id\ProductCategoryOffersUid;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class ProductCategoryFilterForm extends AbstractType
{
	
	private AllFilterFieldsByCategoryInterface $fields;
	
	private FieldsChoice $choice;
	
	private RequestStack $request;
	
	private OfferFieldsCategoryChoiceInterface $offerChoice;
	
	private VariationFieldsCategoryChoiceInterface $variationChoice;
	
	private ModificationFieldsCategoryChoiceInterface $modificationChoice;
	
	
	public function __construct(
		AllFilterFieldsByCategoryInterface $fields,
		
		OfferFieldsCategoryChoiceInterface $offerChoice,
		VariationFieldsCategoryChoiceInterface $variationChoice,
		ModificationFieldsCategoryChoiceInterface $modificationChoice,
		FieldsChoice $choice,
		RequestStack $request,
	)
	{
		$this->fields = $fields;
		$this->choice = $choice;
		$this->request = $request;
		$this->offerChoice = $offerChoice;
		$this->variationChoice = $variationChoice;
		$this->modificationChoice = $modificationChoice;
	}
	
	
	/** Форма фильтрации товаров в разделе */
	
	public function buildForm(FormBuilderInterface $builder, array $options) : void
	{
		$data = $builder->getData();
		
		if($data->getCategory())
		{
			
			/** Торговое предложение раздела */
			
			$offerField = $this->offerChoice->getOfferFieldType($data->getCategory());
			
			
			
			if($offerField)
			{
				$inputOffer = $this->choice->getChoice($offerField->getField());
				
				if($inputOffer)
				{
					$builder->add('offer',
						
						$inputOffer->form(),
						[
							'label' => $offerField->getOption(),
							//'mapped' => false,
							'priority' => 200,
							'required' => false,
							
							//'block_name' => $field['type'],
							//'data' => isset($session[$field['type']]) ? $session[$field['type']] : null,
						]
					);
					
					
					/** Множественные варианты торгового предложения */
					
					$variationField = $this->variationChoice->getVariationFieldType($offerField);
					
					if($variationField)
					{
						
						$inputVariation = $this->choice->getChoice($variationField->getField());
						
						if($inputVariation)
						{
							$builder->add('variation',
								$inputVariation->form(),
								[
									'label' => $variationField->getOption(),
									//'mapped' => false,
									'priority' => 199,
									'required' => false,
									
									//'block_name' => $field['type'],
									//'data' => isset($session[$field['type']]) ? $session[$field['type']] : null,
								]
							);
							
							/** Модификации множественных вариантов торгового предложения */
							
							$modificationField = $this->modificationChoice->getModificationFieldType($variationField);
							
							
							if($modificationField)
							{
								$inputModification = $this->choice->getChoice($modificationField->getField());
								
								if($inputModification)
								{
									$builder->add('modification',
										$inputModification->form(),
										[
											'label' => $modificationField->getOption(),
											//'mapped' => false,
											'priority' => 198,
											'required' => false,
											
											//'block_name' => $field['type'],
											//'data' => isset($session[$field['type']]) ? $session[$field['type']] : null,
										]
									);
								}
							}
						}
					}
				}
			}
			
			
			
			
			
			/** Свойства, учавствующие в фильтре */
			
			$fields = $this->fields->fetchAllFilterCategoryFieldsAssociative($data->getCategory());
			
			
			
			if($fields)
			{
				
				//dd($fields);
				
				$session = $this->request->getSession()->get('catalog_filter');
				
				//dump($session);
				
				$i = 100;
				foreach($fields as $field)
				{
					$input = $this->choice->getChoice(new  InputField($field['type']));
					
					if($input)
					{
						$builder->add($field['id'],
							$input->form(),
							[
								'label' => $field['name'],
								'mapped' => false,
								'priority' => $i,
								'required' => false,
								'block_name' => $field['type'],
								'data' => isset($session[$field['type']]) ? $session[$field['type']] : null,
							]
						);
					}
					
					$i--;
				}
				
				$builder->addEventListener(
					FormEvents::POST_SUBMIT,
					function(FormEvent $event) {
						
						$data = $event->getForm()->all();
						
						$session = [];
						
						foreach($data as $datum)
						{
							if(!empty($datum->getViewData()))
							{
								if($datum->getNormData() === true)
								{
									$item = 'true';
								}
								else
								{
									$item = (string) $datum->getNormData();
								}
								
								$session[$datum->getConfig()->getOption('block_name')] = $item;
							}
						}
						
						$this->request->getSession()->set('catalog_filter', $session);
						
					}
				);
				
				/* Сохранить ******************************************************/
				$builder->add(
					'filter',
					SubmitType::class,
					['label' => 'Save', 'label_html' => true, 'attr' => ['class' => 'btn-primary'], 'priority' => $i]
				);
			}
			
		}
		
	}
	
	
	public function configureOptions(OptionsResolver $resolver) : void
	{
		$resolver->setDefaults([
			'data_class' => ProductCategoryFilterDTO::class,
		]);
	}
	
}