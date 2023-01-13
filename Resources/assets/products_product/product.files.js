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


/** Коллекция ФАЙЛОВ */

let $addButtonFile = document.getElementById('file_addCollection');

/* Блок для новой коллекции */
let $blockCollectionFile = document.getElementById('file_collection');

if ($addButtonFile) {
    /* добавить событие на удаление ко всем существующим элементам формы в блок с классом .del-item */
    let $delItemFile = $blockCollectionFile.querySelectorAll('.del-item-file');

    /* Удаляем при клике колекцию СЕКЦИЙ */
    $delItemFile.forEach(function (item) {
        item.addEventListener('click', function () {
            item.closest('.item-collection-file').remove();
        });
    });

    /* получаем количество коллекций и присваиваем data-index прототипу */
    //$blockCollectionFile.dataset.index = $blockCollectionFile.getElementsByClassName('item-collection-file').length.toString();

    /* Добавляем новую коллекцию */
    $addButtonFile.addEventListener('click', function () {

        let $addButtonFile = this;
        /* получаем прототип коллекции  */
        let newForm = $addButtonFile.dataset.prototype;
        let index = $addButtonFile.dataset.index * 1;

        /* Замена '__name__' в HTML-коде прототипа
        вместо этого будет число, основанное на том, сколько коллекций */
        newForm = newForm.replace(/__files__/g, index);
        //newForm = newForm.replace(/__FIELD__/g, index);

        /* Вставляем новую коллекцию */
        let div = document.createElement('div');
        div.classList.add('item-collection-file')
        div.innerHTML = newForm;
        $blockCollectionFile.append(div);

        /* Удаляем при клике колекцию СЕКЦИЙ */
        div.querySelector('.del-item-file').addEventListener('click', function () {
            this.closest('.item-collection-file').remove();
            let index = $addButtonFile.dataset.index * 1;
            $addButtonFile.dataset.index = (index - 1).toString();
        });

        $addButtonFile.dataset.index = (index + 1).toString();

        /* Увеличиваем data-index на 1 после вставки новой коллекции */
        //$blockCollectionFile.dataset.index = index.toString();

    });
}
