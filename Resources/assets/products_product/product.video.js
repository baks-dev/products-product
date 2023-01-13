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


/** коллекция ВИДЕО */

let $addButtonVideo = document.getElementById('video_addCollection');

/* Блок для новой коллекции */
let $blockCollectionVideo = document.getElementById('video_collection');

if ($addButtonVideo) {
    /* добавить событие на удаление ко всем существующим элементам формы в блок с классом .del-item */
    let $delItemVideo = $blockCollectionVideo.querySelectorAll('.del-item-video');

    /* Удаляем при клике колекцию СЕКЦИЙ */
    $delItemVideo.forEach(function (item) {
        item.addEventListener('click', function () {

            item.closest('.item-collection-video').remove();

        });
    });

    /* получаем количество коллекций и присваиваем data-index прототипу */
    //$blockCollectionVideo.dataset.index = $blockCollectionVideo.getElementsByClassName('item-collection-video').length.toString();


    /* Добавляем новую коллекцию */
    $addButtonVideo.addEventListener('click', function () {

        let $addButtonVideo = this;

        /* получаем прототип коллекции  */
        let newForm = $addButtonVideo.dataset.prototype;
        let index = $addButtonVideo.dataset.index * 1;

        /* Замена '__name__' в HTML-коде прототипа на
        вместо этого будет число, основанное на том, сколько коллекций */
        newForm = newForm.replace(/__videos__/g, index);


        /* Вставляем новую коллекцию */
        let div = document.createElement('div');
        div.classList.add('item-collection-video')
        div.innerHTML = newForm;
        $blockCollectionVideo.append(div);

        /* Удаляем при клике колекцию СЕКЦИЙ */
        div.querySelector('.del-item-video').addEventListener('click', function () {
            this.closest('.item-collection-video').remove();
            let index = $addButtonVideo.dataset.index * 1;
            $addButtonVideo.dataset.index = (index - 1).toString();
        });

        $addButtonVideo.dataset.index = (index + 1).toString();

        /* Увеличиваем data-index на 1 после вставки новой коллекции */
        //$blockCollectionVideo.dataset.index = index.toString();

    });
}