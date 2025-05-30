/*
 * Copyright 2025.  Baks.dev <admin@baks.dev>
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

executeFunc(function productQuantityForm()
{
    const form = document.forms.products_quantity_form;

    if(typeof form === 'undefined')
    {
        return false;
    }

    var object_category = document.getElementById(form.name + '_category');

    if(object_category === null)
    {
        return false;
    }

    object_category.addEventListener('change', function()
    {
        changeObjectCategory(form);
    }, false);

    let $addButtonStock = document.getElementById(form.name + '_submit');
    $addButtonStock.addEventListener('click',  function(e)
    {
        let $errorFormHandler = null;
        let header = 'Изменить количество продукции';

        if(object_category.value === '')
        {
            $errorFormHandler = JSON.stringify({
                type: 'danger',
                header: header,
                message: 'Категория не выбрана'
            })
        }

        /* Выводим сообщение об ошибке заполнения */

        if($errorFormHandler)
        {
            createToast(JSON.parse($errorFormHandler));
            return false;
        }

        const data = new FormData(form);
        if(true === form.checkValidity())
        {
            submitModalForm(form);

        }
    });

    return true;
});

async function changeObjectCategory(forms)
{
    disabledElementsForm(forms);

    const data = new FormData(forms);
    data.delete(forms.name + '[_token]');
    data.delete(forms.name + '[offer]');
    data.delete(forms.name + '[variation]');
    data.delete(forms.name + '[modification]');

    await fetch(forms.action, {
        method: forms.method, // *GET, POST, PUT, DELETE, etc.
        cache: 'no-cache', // *default, no-cache, reload, force-cache, only-if-cached
        credentials: 'same-origin', // include, *same-origin, omit
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        },

        redirect: 'follow', // manual, *follow, error
        referrerPolicy: 'no-referrer', // no-referrer, *no-referrer-when-downgrade, origin, origin-when-cross-origin, same-origin, strict-origin, strict-origin-when-cross-origin, unsafe-url
        body: data // body data type must match "Content-Type" header
    })
        .then((response) =>
        {
            if(response.status !== 200)
            {
                return false;
            }

            return response.text();

        })
        .then((data) =>
        {
            var parser = new DOMParser();
            var result = parser.parseFromString(data, 'text/html');

            offer = result.getElementById('products_quantity_form_offer');

            if(offer)
            {
                document.getElementById('products_quantity_form_offer').replaceWith(offer);
                new NiceSelect(document.getElementById('products_quantity_form_offer'), {searchable: true});
            }

            variation = result.getElementById('products_quantity_form_variation');

            if(variation)
            {
                document.getElementById('products_quantity_form_variation').replaceWith(variation);
                new NiceSelect(document.getElementById('products_quantity_form_variation'), {searchable: true});
            }

            modification = result.getElementById('products_quantity_form_modification');

            if(modification)
            {
                document.getElementById('products_quantity_form_modification').replaceWith(modification);
                new NiceSelect(document.getElementById('products_quantity_form_modification'), {searchable: true});
            }

            enableElementsForm(forms);
        });
}

