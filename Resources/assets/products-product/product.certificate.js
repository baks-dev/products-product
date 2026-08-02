/*
 *  Copyright 2025.  Baks.dev <admin@baks.dev>
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


/** Коллекция ФАЙЛОВ */

let $addButtonCertificate = document.getElementById("cert_addCollection");

/* Блок для новой коллекции */
let $blockCollectionCertificate = document.getElementById("cert_collection");

if($addButtonCertificate)
{
    /* добавить событие на удаление ко всем существующим элементам формы в блок с классом .del-item */
    let $delItemCertificate = $blockCollectionCertificate.querySelectorAll(".del-item-cert");

    /* Удаляем при клике колекцию СЕКЦИЙ */
    $delItemCertificate.forEach(function(item)
    {
        item.addEventListener("click", function()
        {
            item.closest(".item-collection-cert").remove();
        });
    });

    /* получаем количество коллекций и присваиваем data-index прототипу */
    //$blockCollectionCertificate.dataset.index = $blockCollectionCertificate.getElementsByClassName('item-collection-file').length.toString();

    /* Добавляем новую коллекцию */
    $addButtonCertificate.addEventListener("click", function()
    {

        let $addButtonCertificate = this;

        /* получаем прототип коллекции  */
        let newForm = document.getElementById($addButtonCertificate.dataset.prototype).dataset.prototype;
        let index = $addButtonCertificate.dataset.index * 1;

        /* Замена '__name__' в HTML-коде прототипа
         вместо этого будет число, основанное на том, сколько коллекций */
        newForm = newForm.replace(/__certificate__/g, index);
        //newForm = newForm.replace(/__FIELD__/g, index);

        /* Вставляем новую коллекцию */
        let div = document.createElement("div");
        div.classList.add("d-flex");
        div.classList.add("justify-content-between");
        div.classList.add("align-items-center");
        div.classList.add("gap-3");
        div.classList.add("item-collection-cert");
        div.classList.add("mb-3");

        div.innerHTML = newForm;
        $blockCollectionCertificate.append(div);

        /* Удаляем при клике колекцию СЕКЦИЙ */
        div.querySelector(".del-item-cert").addEventListener("click", function()
        {
            this.closest(".item-collection-cert").remove();
            let index = $addButtonCertificate.dataset.index * 1;
            $addButtonCertificate.dataset.index = (index - 1).toString();
        });


        const $elementsSelect2 = div.querySelectorAll("[data-select=\"select2\"]");

        $elementsSelect2.forEach((element) =>
        {
            new NiceSelect(element, {searchable : true});
        });


        let $elementsDate = div.querySelectorAll("input.js-datepicker");

        $elementsDate.forEach((element) =>
        {
            MCDatepicker.create({
                el : "#" + element.id,
                bodyType : "modal", // ‘modal’, ‘inline’, or ‘permanent’.
                autoClose : false,
                closeOndblclick : true,
                closeOnBlur : false,
                customOkBTN : "OK",
                customClearBTN : datapickerLang[$locale].customClearBTN,
                customCancelBTN : datapickerLang[$locale].customCancelBTN,
                firstWeekday : datapickerLang[$locale].firstWeekday,
                dateFormat : "DD.MM.YYYY",
                customWeekDays : datapickerLang[$locale].customWeekDays,
                customMonths : datapickerLang[$locale].customMonths,
                selectedDate : new Date(),
            });

        });


        $addButtonCertificate.dataset.index = (index + 1).toString();

        /* Увеличиваем data-index на 1 после вставки новой коллекции */
        //$blockCollectionCertificate.dataset.index = index.toString();

    });
}
