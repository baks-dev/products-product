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

namespace BaksDev\Products\Product\UseCase\Admin\NewEdit\Active;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class ActiveForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /* TextType */
        $builder->add('active', CheckboxType::class, ['label' => false, 'required' => false]);

        /** Начало активности */
        $builder->add('activeFrom', DateType::class, [
            'widget' => 'single_text',
            'html5' => false,
            'label' => false,
            'required' => true,
            'format' => 'dd.MM.yyyy',
            'input' => 'datetime_immutable',
            'attr' => ['class' => 'js-datepicker'],
        ]);

        $builder->add(
            'activeFromTime',
            TimeType::class,
            [
                'widget' => 'single_text',
                'required' => false,
                'label' => false,
                'input' => 'datetime_immutable',
            ]
        );

        /** Окончание активности */
        $builder->add('activeTo', DateType::class, [
            'widget' => 'single_text',
            'html5' => false,
            'label' => false,
            'required' => false,
            'format' => 'dd.MM.yyyy',
            'input' => 'datetime_immutable',
            'attr' => ['class' => 'js-datepicker'],
        ]);

        $builder->add(
            'activeToTime',
            TimeType::class,
            [
                'widget' => 'single_text',
                'required' => false,
                'label' => false,
                'input' => 'datetime_immutable',
            ]
        );

    }


    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ActiveDTO::class,
        ]);
    }

}
