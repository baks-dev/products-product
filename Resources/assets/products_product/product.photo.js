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


/** Коллекция ФОТО */

/* кнопка Добавить ФОТО */
let $addButtonPhoto = document.getElementById('photo_addCollection');

/* Блок для новой коллекции */
let $blockCollectionPhoto = document.getElementById('photo_collection');


if ($addButtonPhoto) {
    /* добавить событие на удаление ко всем существующим элементам формы в блок с классом .del-item */
    let $delItemPhoto = $blockCollectionPhoto.querySelectorAll('.del-item-photo');

    /* Удаляем при клике колекцию СЕКЦИЙ */
    $delItemPhoto.forEach(function (item) {
        item.addEventListener('click', function () {
            let $counter = $blockCollectionPhoto.getElementsByClassName('item-collection-photo').length;
            if ($counter > 1) {
                item.closest('.item-collection-photo').remove();
            }
        });
    });


    /* добавить событие на удаление ко всем существующим элементам формы в блок с классом .del-item */
    let $changeItemPhoto = $blockCollectionPhoto.querySelectorAll('.change-root');

    /* Удаляем при клике колекцию СЕКЦИЙ */
    $changeItemPhoto.forEach(function (item) {

        if ($changeItemPhoto.length === 1 && item.checked === false) {
            item.checked = true;
        }

        item.addEventListener('change', function () {

            let photo_collection = document.getElementById('photo_collection');

            photo_collection.querySelectorAll('.change-root').forEach(function (rootCheck) {
                rootCheck.checked = false;
            });


            this.checked = true;
        });
    });


    /*    $blockCollectionPhoto.querySelector('.change-root').addEventListener('change', function (selector) {
    
            let photo_collection = document.getElementById('photo_collection');
    
            photo_collection.querySelectorAll('.change-root').forEach(function (rootChack, i, arr) {
                rootChack.checked = false;
            });
    
    
            this.checked = true;
        });*/


    /* получаем количество коллекций и присваиваем data-index прототипу */
    //$blockCollectionPhoto.dataset.index = $blockCollectionPhoto.getElementsByClassName('item-collection-photo').length.toString();


    /* Добавляем новую коллекцию */
    $addButtonPhoto.addEventListener('click', function () {

        let $addButtonPhoto = this;


        /* получаем прототип коллекции  */
        let newForm = $addButtonPhoto.dataset.prototype;
        let index = $addButtonPhoto.dataset.index * 1;

        if (index === 6) {
            return;
        }

        /* Замена '__name__' в HTML-коде прототипа на
        вместо этого будет число, основанное на том, сколько коллекций */
        newForm = newForm.replace(/__photos__/g, index);
        //newForm = newForm.replace(/__FIELD__/g, index);

        /* Вставляем новую коллекцию */
        let div = document.createElement('div');
        div.classList.add('item-collection-photo')
        div.innerHTML = newForm;
        $blockCollectionPhoto.append(div);

        /* Удаляем при клике колекцию СЕКЦИЙ */
        div.querySelector('.del-item-photo').addEventListener('click', function () {
            let $counter = $blockCollectionPhoto.getElementsByClassName('item-collection-photo').length;
            //if ($counter > 1) {
            this.closest('.item-collection-photo').remove();
            let index = $addButtonPhoto.dataset.index * 1;
            $addButtonPhoto.dataset.index = (index - 1).toString()

            //}
        });


        div.querySelector('.change-root').addEventListener('change', function (selector) {

            let photo_collection = document.getElementById('photo_collection');

            photo_collection.querySelectorAll('.change-root').forEach(function (rootChack, i, arr) {
                rootChack.checked = false;
            });


            this.checked = true;
        });


        /* Увеличиваем data-index на 1 после вставки новой коллекции */
        $addButtonPhoto.dataset.index = (index + 1).toString();


        let inputElement = div.querySelector('input[type="file"]');

        inputElement.addEventListener('change', function (e) {

            var file = inputElement.files[0];
            var reader = new FileReader();
            let image = div.querySelector('.image-input');

            reader.onloadend = function () {

                image.style.setProperty("background-image", "url(" + reader.result + ")", "important")
            }

            if (file) {
                reader.readAsDataURL(file);
            } else {
                image.style.setProperty("background-image", "url(/img/blank.svg)", "important")

            }
        });
    });
}





