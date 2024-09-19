<?php
/*
 *  Copyright 2024.  Baks.dev <admin@baks.dev>
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

namespace BaksDev\Products\Product\Forms\ProductFilter\Admin\Property;

use BaksDev\Core\Services\Fields\FieldsChoice;
use BaksDev\Core\Type\Field\InputField;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class ProductFilterPropertyForm extends AbstractType
{
    public function __construct(private readonly FieldsChoice $choice) {}

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addEventListener(
            FormEvents::PRE_SET_DATA,
            function (FormEvent $event): void {

                $builder = $event->getForm();

                /** @var ProductFilterPropertyDTO $data */
                $data = $event->getData();

                $input = $this->choice->getChoice(new InputField($data->getType()));

                if($input)
                {
                    $data->setDomain($input->domain());

                    $builder->add(
                        'value',
                        $input->form(),
                        [
                            'label' => $data->getLabel(),
                            'required' => false,
                            'block_name' => $data->getType()
                        ]
                    );
                }
            }
        );
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ProductFilterPropertyDTO::class,
            'properties' => null
        ]);
    }
}
