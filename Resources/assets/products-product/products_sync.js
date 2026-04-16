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
 *
 */

/** получает продукты по идентификатору категории и вставляет html с продуктами */

/** покажет в логах:
 *  - время каждого запроса для получения продуктов по категории
 *  */
const debug_TfpBy2NAo1 = false;
let debug_start_TfpBy2NAo1 = 0;

async function loadSync_TfpBy2NAo1(items, form = null)
{
    /** В начале поиска показываем прелоадер */
    beforeLoad_TfpBy2NAo1()

    const results = [];

    for(const item of items)
    {
        const result = await init_TfpBy2NAo1(item, form);
        results.push(result);
    }

    /** В конце убираем прелоадер */
    afterLoad_TfpBy2NAo1()


    /** если все загрузку не успешны - показываем пустой шаблон */

    const all_failed = results.every(result => result === false);

    if(all_failed)
    {
        let empty = document.getElementById("catalog_products_empty");

        if(empty && true === empty.classList.contains('d-none'))
        {
            empty.classList.remove('d-none')
        }
    }

}

async function init_TfpBy2NAo1(item, form = null)
{
    /** body и method определяются в зависимости от того, передана форма или нет */
    const body = null !== form ? new FormData(form) : null;
    const method = null !== form ? form.method : 'GET';

    const path = item.path;

    // отладка
    if(true === debug_TfpBy2NAo1)
    {
        debug_start_TfpBy2NAo1 = performance.now();
    }

    /** Ждем ответ от сервера */
    const response = await fetch(path, {
        method: method,
        body: body,
        cache: 'no-cache',
        credentials: 'same-origin',
        headers: {'X-Requested-With': 'XMLHttpRequest'},
        redirect: 'follow',
        referrerPolicy: 'no-referrer',
    })

    if(response.status !== 200)
    {
        return false;
    }

    // отладка
    if(true === debug_TfpBy2NAo1)
    {
        const debug_end = performance.now();
        console.log(`Время получения ответа для категории ${item.url} : ${(debug_end - debug_start_TfpBy2NAo1).toFixed(2)} ms`);
    }

    const html = await response.text();

    if(html)
    {
        /** Если продуктов нет - не вставляем ответ от сервера */
        if(html.length <= 1)
        {
            if(true === debug_TfpBy2NAo1)
            {
                console.log(`Продукты НЕ НАЙДЕНЫ для категории ${item.url}`);
            }

            return false
        }

        const range = document.createRange();
        range.selectNode(document.body);

        const fragment = range.createContextualFragment(html);
        const elements = Array.from(fragment.children);

        /** обрабатываем все кнопки "Добавить в избранное" */
        const favorites = fragment.querySelectorAll('.favorite, .favorite-delete');

        if(favorites)
        {
            favorites.forEach(favorite =>
            {
                favorite.addEventListener('click', function(e)
                {
                    e.preventDefault();
                    addToFavorite(e);
                }, true);
            });
        }

        /** обрабатываем все ссылки для вызова модального окна */
        const modal_links = fragment.querySelectorAll("[data-bs-toggle=\"modal\"]");

        if(modal_links)
        {
            modal_links.forEach(function(item, i, arr)
            {
                modalLink(item);

                item.disabled = false;
                item.removeAttribute("disabled");
                item.classList.remove("disabled");

            });

        }

        /** обрабатываем все формы с фильтрами */
        const product_filter_form = fragment.querySelector(".product_filter_form");

        if(product_filter_form)
        {
            productFilter_F4Haf8H(product_filter_form)
        }

        let target = document.getElementById('catalog_products');

        /** вставляем элемент */
        target.append(fragment);

        /** анимируем вставку */
        elements.forEach(el =>
        {
            el.animate(
                [
                    {opacity: 0, transform: "translateY(10px)"},
                    {opacity: 1, transform: "translateY(0)"}
                ],
                {
                    duration: 500,
                    easing: "ease",
                }
            );
        });

        return true;
    }

    return false;
}

/** Визуализация перед загрузкой */
function beforeLoad_TfpBy2NAo1()
{
    if(document.forms["product_category_filter_form"])
    {
        let btn = document.getElementById("product_category_filter_form_filter");
        btn.setAttribute('disabled', 'disabled');
    }

    let spinners = document.getElementById("catalog_products_spinners");

    if(spinners && true === spinners.classList.contains('d-none'))
    {
        spinners.classList.remove('d-none')
    }

    let preload = document.getElementById("catalog_products_preload");

    if(preload && true === preload.classList.contains('d-none'))
    {
        preload.classList.remove('d-none')
    }

    let target = document.getElementById('catalog_products');
    target.innerHTML = ''
}

/** Визуализация после загрузкой */
function afterLoad_TfpBy2NAo1()
{
    if(document.forms["product_category_filter_form"])
    {
        let btn = document.getElementById("product_category_filter_form_filter");
        btn.removeAttribute('disabled');
    }

    let empty = document.getElementById("catalog_products_empty");

    if(empty && false === empty.classList.contains('d-none'))
    {
        empty.classList.add('d-none')
    }

    let spinners = document.getElementById("catalog_products_spinners");

    if(spinners && false === spinners.classList.contains('d-none'))
    {
        spinners.classList.add('d-none')
    }

    let preload = document.getElementById("catalog_products_preload");

    if(preload && false === preload.classList.contains('d-none'))
    {
        preload.classList.add('d-none')
    }
}