/*
 *  Copyright 2026.  Baks.dev <admin@baks.dev>
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


/** коллекция ВИДЕО */

let $addButtonProfile = document.getElementById("profile_addCollection");

/* Блок для новой коллекции */
let $blockCollectionProfile = document.getElementById("profile_collection");

if($addButtonProfile)
{
    /* добавить событие на удаление ко всем существующим элементам формы в блок с классом .del-item */
    let $delItemProfile = $blockCollectionProfile.querySelectorAll(".del-item-profile");

    /* Удаляем при клике колекцию СЕКЦИЙ */
    $delItemProfile.forEach(function(item)
    {
        item.addEventListener("click", function()
        {

            item.closest(".item-collection-profile").remove();

        });
    });

    /* Добавляем новую коллекцию */
    $addButtonProfile.addEventListener("click", function()
    {

        let $addButtonProfile = this;

        /* получаем прототип коллекции  */
        //let newForm = $addButtonProfile.dataset.prototype;
        let newForm = document.getElementById($addButtonProfile.dataset.prototype).dataset.prototype;
        let index = $addButtonProfile.dataset.index * 1;

        /* Замена '__name__' в HTML-коде прототипа на
         вместо этого будет число, основанное на том, сколько коллекций */
        newForm = newForm.replace(/__profile__/g, index);


        /* Вставляем новую коллекцию */
        let div = document.createElement("div");
        div.classList.add("d-flex");
        div.classList.add("justify-content-between");
        div.classList.add("align-items-center");
        div.classList.add("gap-3");
        div.classList.add("item-collection-profile");


        div.innerHTML = newForm;
        $blockCollectionProfile.append(div);

        /* Удаляем при клике колекцию СЕКЦИЙ */
        div.querySelector(".del-item-profile").addEventListener("click", function()
        {
            this.closest(".item-collection-profile").remove();
            let index = $addButtonProfile.dataset.index * 1;
            $addButtonProfile.dataset.index = (index - 1).toString();
        });

        new NiceSelect(document.getElementById("product_form_profile_" + index + "_value"), {searchable : true});

        $addButtonProfile.dataset.index = (index + 1).toString();

    });
}