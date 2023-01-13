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


/** Коллекция КАТЕГОРИЙ */

/* кнопка Добавить КАТЕГОРИЮ */
let $addButtonCategory = document.getElementById('category_addCollection');

/* Блок для новой коллекции КАТЕГОРИИ */
let $blockCollectionCategory = document.getElementById('category_collection');

if ($addButtonCategory) {
    /* добавить событие на удаление ко всем существующим элементам формы в блок с классом .del-item */
    let $delItemCategory = $blockCollectionCategory.querySelectorAll('.del-item-category');

    /* Удаляем при клике колекцию СЕКЦИЙ */
    $delItemCategory.forEach(function (item) {
        item.addEventListener('click', function () {

            let $counter = $blockCollectionCategory.getElementsByClassName('item-collection-category').length;
            if ($counter > 1) {
                item.closest('.item-collection-category').remove();
            }
        });
    });

    /* получаем количество коллекций и присваиваем data-index прототипу */
    $blockCollectionCategory.dataset.index = $blockCollectionCategory.getElementsByClassName('item-collection-category').length.toString();

    /* Добавляем новую коллекцию */
    $addButtonCategory.addEventListener('click', function () {
        /* получаем прототип коллекции  */
        let $addButtonCategory = this;

        let newForm = $addButtonCategory.dataset.prototype;
        let index = $addButtonCategory.dataset.index * 1;

        /* Замена '__name__' в HTML-коде прототипа
        вместо этого будет число, основанное на том, сколько коллекций */
        newForm = newForm.replace(/__categories__/g, index);
        //newForm = newForm.replace(/__FIELD__/g, index);

        /* Вставляем новую коллекцию */
        let div = document.createElement('div');
        div.classList.add('item-collection-category')
        div.innerHTML = newForm;
        $blockCollectionCategory.append(div);

        /* Удаляем при клике колекцию СЕКЦИЙ */
        div.querySelector('.del-item-category').addEventListener('click', function () {
            let $counter = $blockCollectionCategory.getElementsByClassName('item-collection-category').length;
            if ($counter > 1) {
                this.closest('.item-collection-category').remove();
                index = $addButtonCategory.dataset.index * 1;
                $addButtonCategory.dataset.index = (index - 1).toString();
            }
        });

        /* Увеличиваем data-index на 1 после вставки новой коллекции */
        $addButtonCategory.dataset.index = (index + 1).toString();

        /* применяем select2 */
        new NiceSelect(div.querySelector('[data-select="select2"]'), {searchable: true});

    });
}

$select2Category = document.getElementById('product_form_category_0_category');
//new NiceSelect($select2Category, {searchable: true});


/* ИЗМЕНЕНИЕ СВОЙСТВ В ЗАВИСИМОСТИ ОТ КАТЕГОРИИ */
//let attr = document.querySelector('[data-parent="true"]');

$select2Category.addEventListener('change', function () {

    if (this.value) {
        window.location.href = '?category=' + this.value;
        return;
    }

    window.location.href = '?';
});

