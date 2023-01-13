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







// if (initDatepick != undefined)
// {
//     /** Инициируем календарь */
//     //initDatepick('product_form_active_activeFrom');
//     //initDatepick('product_form_active_activeTo');
// }






/** Символьный код */
/* Получаем поле, Согласно выбранной локали */
let $name = document.querySelector("input[data-lang='product_form_trans_" + $lang + "']");

if ($name) {
    $name.addEventListener('input', catUrl.debounce(500));

    function catUrl() {
        /* Заполняем транслитом URL */
        document.getElementById('product_form_info_url').value = translitRuEn(this.value).toLowerCase();
    }
}

// document.querySelectorAll('[data-select="select2"]').forEach(function (item) {
//     new NiceSelect(item, {searchable: true});
// });



/** коллекция ТОРГОВЫХ ПРЕДЛОЖЕНИЙ} ***********************************************************/

/* получаем количество коллекций и присваиваем data-index прототипу */

/* Блок для новой коллекции */
let $blockCollectionOffers = document.getElementById('collection-offer');

// if ($blockCollectionOffers) {
//     /* получаем количество коллекций и присваиваем data-index прототипу */
//     $blockCollectionOffers.dataset.index = $blockCollectionOffers.getElementsByClassName('item-collection-offer').length.toString();
//
// }


let $addButtonOffer = document.getElementById('offer_addCollection');

if ($addButtonOffer) {
    /* Добавляем новую коллекцию торгового предложения */
    $addButtonOffer.addEventListener('click', function () {
        $addButtonOffer = this;


        /* получаем прототип коллекции  */
        let newForm = $addButtonOffer.dataset.prototype;
        let index = $addButtonOffer.dataset.index * 1;
        let offer = $addButtonOffer.dataset.offer * 1;



        /* Замена '__name__' в HTML-коде прототипа
        вместо этого будет число, основанное на том, сколько коллекций */
        newForm = newForm.replace(/__offers__/g, index);
        //newForm = newForm.replace(/__variation__/g, offer);
        //newForm = newForm.replace(/__images__/g, index);

        /* Вставляем новую коллекцию */
        let div = document.createElement('div');
        div.classList.add('card');
        div.classList.add('mb-3');
        div.classList.add('item-collection-offer');
        div.dataset.index = offer.toString();
        //div.classList.add('item-collection-offer');
        div.id = 'item-collection-offers-'+index;
        div.innerHTML = newForm;


        // let collOffer = div.getElementsByClassName('item-collection-offer-fields');
        //
        // collOffer.forEach(function (item, count) {
        //     item.innerHTML = item.innerHTML.replace(/__count__/g, count)
        //     //item.innerHTML = item.innerHTML.replace(/__counter__/g, count)
        // });



        $blockCollectionOffers.append(div);
        $addButtonOffer.dataset.index = (index + 1).toString();


        /* Делаем замену INPUT на справочник во всех вариациях */
        div.querySelectorAll('.item-collection-offer-fields').forEach(function (references) {

            let $addButtonMultipleOffer = references.querySelector('[data-reference]');

            if ($addButtonMultipleOffer) {
                replaceReference(references, $addButtonMultipleOffer.dataset.reference);
            }
        });


        replaceHidden(div)

        /* Добавляем индекс блока */
        //div.dataset.offer = $blockCollectionOffers.getElementsByClassName('item-collection-offer').length.toString();

        /* Указываем количество полей в блоке торгового предложения */
        //div.dataset.fields = div.getElementsByClassName('item-collection-offer-fields').length.toString();

        /* Увеличиваем data-index на 1 после вставки новой коллекции */
        //$blockCollectionOffers.dataset.index = (index + 1).toString();

        /* Удаляем при клике полностью Торговое предложение */
        div.querySelector('.del-item-offer').addEventListener('click', function () {
            this.closest('.item-collection-offer').remove();
        });


        /* Добавляем в торговое предложение вариант с множественным выбором при клике */
        div.querySelectorAll('.offer_multiple_addCollection').forEach(function (multipleOffer) {
            addMultipleOffer(multipleOffer);
        });

        /* Добавляем фото торгового предложения */
        let offer_photo_addCollection = div.querySelector('.offer_photo_addCollection');
        if(offer_photo_addCollection)
        {
            offer_photo_addCollection.addEventListener('click', function () {
                addOfferPhoto(this)
            });
        }



        /* Инициируем предзагрузку фото */
        new KTImageInput(div.querySelector("[data-kt-image-input]"));

    });
}




/* Удаляем при клике торговое предложение */
document.querySelectorAll('.del-item-offer').forEach(function (delOffer) {
    delOffer.addEventListener('click', function () {
        this.closest('.item-collection-offer').remove();
    });
});


/* Добавляем в торговое предложение поле с множественным выбором при клике */
document.querySelectorAll('.offer_multiple_addCollection').forEach(function (multipleOffer) {
    addMultipleOffer(multipleOffer);
});



/* Меняем позицию ROOT для фото ТП */



document.querySelectorAll('.item-collection-photo').forEach(function (div) {

    div.querySelector('.change-root').addEventListener('change', function () {

        //let photo_collection = document.getElementById('photo_collection');


        this.closest('.item-collection').querySelectorAll('.change-root').forEach(function (rootCheck) {
            rootCheck.checked = false;
        });

        this.checked = true;
    });


});










/* Добавляем ФОТО торгового предложения */
let $offer_photo_addCollection = document.querySelectorAll('.offer_photo_addCollection');
$offer_photo_addCollection
    .forEach(function (addPhoto) {
        addPhoto.addEventListener('click', function () {
            addOfferPhoto(this);
        });
    });

// if ($offer_photo_addCollection) {
//     $offer_photo_addCollection.addEventListener('click', function () {
//         addOfferPhoto(this)
//     });
// }


/* Удаляем ФОТО в торговом предложении при клике */
document.querySelectorAll('.del-item-offer-image').forEach(function (deletePhoto) {
    deletePhoto.addEventListener('click', function () {
        this.closest('.item-collection-photo').remove();
    });
});


/* Удаляем мульти-торговое предложение */
document.querySelectorAll('.del-item-collection').forEach(function (deleteMulti) {
    deleteMulti.addEventListener('click', function () {
        if(this.closest('.multiple-collection').querySelectorAll('.del-item-collection').length > 1)
        {
            this.closest('.item-collection').remove();
        }
    });
});



function addMultipleOffer($addButtonMultipleOffer) {


    /* Индекс торгового предложения */
    //let index = $blockCollectionOffers.dataset.index * 1 - 1;

    /* Блок коллекции, для подсчета полей торгового предложения */
    //let coll = $addButtonMultipleOffer.closest('.item-collection-offer');

    /* Обновляем индекс количества торговых предложений */
    //coll.dataset.fields = coll.getElementsByClassName('item-collection-offer-fields').length.toString();

    /* Событие на клик по кномпе Добавить множественное поле */
    $addButtonMultipleOffer.addEventListener('click', function () {


        /* Блок для вставки нового множественого поля */
        let $multipleBlock = document.getElementById('multiple_' + $addButtonMultipleOffer.dataset.collection);


        /* Получаем прототип формы */
        let newForm = $addButtonMultipleOffer.dataset.prototype;

        /* Индес поля в торговом предложении */
        let offers = $addButtonMultipleOffer.dataset.offers * 1;


        /* Получаем блок с торговыми предложениями, в котором текущие поля  */
        let $blockCollectionOffers = document.getElementById('item-collection-offers-' + offers);
        let index = $blockCollectionOffers.dataset.index * 1;

        newForm = newForm.replace(/__offers__/g, offers) /* меняем индекс торгового предложения */
        newForm = newForm.replace(/__variation__/g, index) /* меняем индекс поля в торговом предложения */

        let div = document.createElement('div');
        div.innerHTML = newForm;

        div.removeAttribute('id');
        div.classList.add('pt-3');
        div.classList.add('border-top');
        div.classList.add('item-collection');
        div.id = 'prototype_product_form_offers_'+offers+'_offer_'+index;

        $multipleBlock.append(div);

        /* Увеличиваем data-fields на 1 после вставки новой коллекции */
        $blockCollectionOffers.dataset.index = (index + 1).toString();

        replaceHidden($multipleBlock);

        /* Делаем замену INPUT на справочник */
        replaceReference($multipleBlock, $addButtonMultipleOffer.dataset.replace);


        /* Удаляем мульти-торговое предложение */
        div.querySelectorAll('.del-item-collection').forEach(function (deleteMulti) {
            deleteMulti.addEventListener('click', function () {
                if(this.closest('.multiple-collection').querySelectorAll('.del-item-collection').length > 1)
                {
                    this.closest('.item-collection').remove();
                }
            });
        });


        /* Если в предложении есть фото - добавляем событие на клик */
        let newOffer =  document.getElementById(div.id);
        let $offer_photo_addCollection = newOffer.querySelector('.offer_photo_addCollection');
        if ($offer_photo_addCollection) {
            $offer_photo_addCollection.addEventListener('click', function () {
                addOfferPhoto(this);
            });
        }
    });

}

/* Заполняем скрытый ID торгового предложения каталога  */
function replaceHidden($multipleBlock) {



    $multipleBlock.querySelectorAll('[data-variant]').forEach(function (item) {
        let $variant = document.getElementById(item.dataset.variant);
        if ($variant)
        {
            item.value = $variant.value;
            item.removeAttribute('data-variant')
        }
    });
}

function replaceReference($searchBlock, $id) {

    /* Получаем поля с ссылкой на справочник */
    //let $referene = $searchBlock.querySelectorAll('[data-reference]:not([data-reference="false"])');
    let dupNode = document.getElementById($id);





    if (dupNode) {

        $searchBlock.querySelectorAll('[data-reference]').forEach(function (item) {

            let cloneNode = dupNode.cloneNode(true);
            cloneNode.id = item.id;
            cloneNode.name = item.name;
            cloneNode.value = "";


            if(cloneNode.options !== undefined)
            {
                cloneNode.options[0].selected = true;
                cloneNode.options[0].selected = 'selected';
                cloneNode.selectedIndex = 0;

                /* Сбрасываем select */
                for (let i = 0, l = cloneNode.options.length; i < l; i++) {
                    cloneNode.options[i].removeAttribute('selected');
                    cloneNode.options[i].selected = false;
                }
            }





            item.replaceWith(cloneNode);

            /* применяем select2 */
            if (cloneNode.dataset.select === "select2") {
                new NiceSelect(cloneNode, { searchable: true });
            }


        });
    }


   // return;


    /* Перебираем список справочников */
    //$referene.forEach(function (item) {
    /* Получаем справичник */
    // let dupNode = document.querySelector('[data-offer="' + item.dataset.reference + '"]');

    // if (dupNode) {
    //     dupNode = dupNode.cloneNode(true);
    //     dupNode.id = $searchBlock.id;
    //     dupNode.name = $searchBlock.name;
    //     $searchBlock.replaceWith(dupNode);
    //
    //     /* применяем select2 */
    //     if (dupNode.dataset.select === "select2") {
    //         new NiceSelect(dupNode, {searchable: true});
    //     }
    // }


    // }
    //});
}


function addOfferPhoto($addBtn) {



    /* получаем индекс вариации торгового предложения */

    //let field = $addBtn.closest('.item-collection-offer-fields').dataset.field;


    /* Блок для вставки нового изображения */
    let $offerImageBlock = document.getElementById($addBtn.dataset.collection);

    /* Получаем количество фото в колеекции */
    //let counter = $offerImageBlock.getElementsByClassName('item-collection-photo').length;



    // if (counter > 5) {
    //     return true;
    // }


    /* Получаем прототип формы */
    let newForm = $addBtn.dataset.prototype;
    let index = $addBtn.dataset.index * 1;
    let offers = $addBtn.dataset.offers * 1;
    let offer = $addBtn.dataset.offer * 1;

    // newForm = newForm.replace(/__name__/g, name) /* меняем индекс торгового предложения */

    newForm = newForm.replace(/__offers__/g, offers) /* меняем индекс поля в торговом предложения */
    newForm = newForm.replace(/__variation__/g, offer) /* меняем индекс поля в торговом предложения */
    newForm = newForm.replace(/__images__/g, index) /* меняем индекс поля в торговом предложения */

    let div = document.createElement('div');
    div.innerHTML = newForm;

    div.classList.add('image-input');
    div.classList.add('mb-3');
    div.classList.add('item-collection-photo');

    $offerImageBlock.append(div);

    $addBtn.dataset.index = $addBtn.dataset.index = (index + 1).toString();


    /* Инициируем предзагрузку фото */
    new KTImageInput(div);

    /* Удаляем ФОТО в торговом предложении при клике */
    div.querySelector('.del-item-offer-image').addEventListener('click', function () {

        /* Получаем количество фото в колеекции */
        //counter = $offerImageBlock.getElementsByClassName('item-collection-photo').length;

        //if (counter > 1) {
        this.closest('.item-collection-photo').remove();

        let index = $addBtn.dataset.index;
        $addBtn.dataset.index = (index - 1).toString();


        //}

    });



    div.querySelector('.change-root').addEventListener('change', function () {

        //let photo_collection = document.getElementById('photo_collection');

        $offerImageBlock.querySelectorAll('.change-root').forEach(function (rootCheck) {
            rootCheck.checked = false;
        });


        this.checked = true;
    });



}

//
// var productForm = document.forms.product_form;
//
// /* Прелоадер при отправке формы */
//
//     /* событие отправки формы */
// productForm.addEventListener('submit', function (event) {
//
//     console.log(this);
//
//         return false;
//     });
