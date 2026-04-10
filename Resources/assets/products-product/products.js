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

/** массив с продуктами для вставки/рендеринга */
const products_vFeCOgNNWC = [];

/** покажет в логах:
 *  - время каждого запроса для получения продуктов по категории
 *  - время до начала вставки контента
 *  */
const debug_PfZTxrOp = true;
let debug_start_PfZTxrOp = 0;

executeFunc(function yOgZhXVkej()
{
    const category_products = document.querySelectorAll("[data-group=\"category_products\"]");

    if(typeof category_products === "undefined" || category_products === null)
    {
        return false;
    }

    //if(typeof bootstrap !== 'object')
    //{
    //    return false;
    //}
    //
    //
    //const modalElement = document.getElementById('modal');
    //
    //if(modalElement)
    //{
    //    //let modal_instance = bootstrap.Modal.getOrCreateInstance(basket);
    //
    //    console.log('modalElement ->', modalElement)
    //    bootstrap.Modal.getOrCreateInstance(modalElement);
    //
    //}


    const form = document.forms["product_category_filter_form"];

    /** если в шаблоне есть форма - отправляем запросы на получение продуктов с учетом данных из формы */
    if(form)
    {

        /** отправка формы по клику */

        form.addEventListener("submit", function(event)
        {
            /** блокируем отправку формы */
            event.preventDefault();

            /** устанавливаем индекс для отслеживания равным общему количеству категорий */
            let categories = document.querySelector("[data-group=\"categories\"]");
            const categories_count = categories.dataset.count;

            categories.setAttribute("data-index", categories_count);

            /** показываем спиннеры загрузки */
            let spinners = document.getElementById("catalog_products_spinners");
            spinners.classList.remove('d-none')

            /** скрываем пустой шаблон */
            let empty = document.getElementById("catalog_products_empty");
            empty.classList.add('d-none')

            category_products.forEach(item =>
            {
                PfZTxrOp(item, form)
            })

        });

        /** форма была отправлена с другой страницы */

        category_products.forEach(item =>
        {
            PfZTxrOp(item, form)
        })

        return true;
    }

    category_products.forEach(item =>
    {
        PfZTxrOp(item)
    })

    return true;
}, 100);


async function PfZTxrOp(item, form = null)
{
    /** body и method определяются в зависимости от того, передана форма или нет */
    const body = null !== form ? new FormData(form) : null;
    const method = null !== form ? form.method : 'GET';

    if(true === debug_PfZTxrOp)
    {
        debug_start_PfZTxrOp = performance.now();
    }

    let link = item.dataset.link;
    let target = document.getElementById(item.dataset.target);

    if(typeof link === undefined || link === null)
    {
        console.log(`Определите путь для получения контента`);
        return
    }

    await fetch(link, {
        method: method,
        body: body,
        cache: 'no-cache',
        credentials: 'same-origin',
        headers: {'X-Requested-With': 'XMLHttpRequest'},
        redirect: 'follow',
        referrerPolicy: 'no-referrer',
    }).then((response) =>
    {
        if(response.status !== 200)
        {
            return false;
        }

        if(true === debug_PfZTxrOp)
        {
            const debug_end = performance.now();
            console.log(`Время загрузки контента для категории ${item.dataset.url} : ${(debug_end - debug_start_PfZTxrOp).toFixed(2)} ms`);
        }

        return response.text();

    }).then((html) =>
    {

        if(html)
        {
            let categories = document.querySelector("[data-group=\"categories\"]");
            const categories_count = categories.dataset.index;

            categories.setAttribute("data-index", parseInt(categories_count) - 1);

            /** Если продуктов нет - не вставляем ответ от сервера */
            if(html.length <= 1)
            {
                if(true === debug_PfZTxrOp)
                {
                    console.log(`Продукты НЕ НАЙДЕНЫ для категории ${item.dataset.url}`);
                }

                return;
            }

            const range = document.createRange();
            range.selectNode(document.body);

            const fragment = range.createContextualFragment(html);

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
                modal_links.forEach(modalLink)
            }

            /** обрабатываем все формы с фильтрами */
            const product_filter_form = fragment.querySelector(".product_filter_form");

            if(product_filter_form)
            {
                productFilter_F4Haf8H(product_filter_form)
            }

            /** сохраняем результат */
            products_vFeCOgNNWC.push([item.dataset.sort, fragment])
        }
    })
        .finally(() =>
        {

            let categories = document.querySelector("[data-group=\"categories\"]");

            /** проверяем — всё ли загрузилось */
            if(parseInt(categories.dataset.index) === 0)
            {
                if(true === debug_PfZTxrOp)
                {
                    const end = performance.now();
                    console.log(`Получена информация о всех продуктах из всех категорий`);
                    console.log(`Время до начала вставки: ${(end - debug_start_PfZTxrOp).toFixed(2)} ms`);
                }

                /** Очищаем контейнер с пререндерингом продуктов */
                target.innerHTML = ''
                pasteAllProducts(target);
            }
        });
}


function pasteAllProducts(target)
{

    /** Убираем спиннеры загрузки */
    let spinners = document.getElementById("catalog_products_spinners");
    spinners.classList.add('d-none')

    /** Если коллекция продуктов пустая - показываем шаблон "продуктов не найдено" */
    if(products_vFeCOgNNWC.length === 0)
    {
        let empty = document.getElementById("catalog_products_empty");
        empty.classList.remove('d-none')
        return
    }

    /** Сортируем по индексу сортировки у категории */
    const sorted = products_vFeCOgNNWC.sort((a, b) =>
    {
        return Number(a[0]) - Number(b[0]);
    });

    /** Вставляем каждый элемент с продуктами */
    sorted.forEach(function(element)
    {
        const fragment = element[1]
        const elements = Array.from(fragment.children);

        target.appendChild(fragment);

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
    });

    products_vFeCOgNNWC.length = 0
}