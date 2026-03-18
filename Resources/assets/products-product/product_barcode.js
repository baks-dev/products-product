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

document.querySelectorAll(".add-barcode").forEach(function(add)
{
    addBarcode(add);
});

document.querySelectorAll(".delete-barcode").forEach(function(del)
{
    deleteBarcode(del);
});

/** Добавление штрихкода */
function addBarcode(element)
{
    element.addEventListener("click", (function(event)
    {
        const collection_id = this.dataset.idCollection;
        const collection_index = this.dataset.indexCollection;
        const collection_current = document.querySelector(`[data-id="${collection_id}"]`);

        const collection_elements = collection_current.querySelectorAll(`[data-collection="${collection_id}"]`);

        if(collection_elements.length >= 5)
        {
            let dangerToast = "{ \"type\":\"danger\" , " +
                "\"header\":\"Ошибка при изменении продукта\"  , " +
                `"message" : "Превышено максимальное количество элементов" }`;

            createToast(JSON.parse(dangerToast));
            return;
        }

        const prototype = document.querySelector(`[data-prototype-collection="${collection_id}"]`);

        let prototype_content = prototype.innerText;
        const prototype_name = prototype.dataset.prototypeName;

        const regex = new RegExp(prototype_name, "g");
        prototype_content = prototype_content.replace(regex, collection_index);

        const template = document.createElement("template");
        prototype_content = prototype_content.replace(/value="[^"]*"/g, "value=\"\"");
        template.innerHTML = prototype_content.trim();

        const fragment = template.content.cloneNode(true);
        collection_current.appendChild(fragment);
        this.dataset.indexCollection = parseInt(collection_index) + 1;

        const last_add = collection_elements[collection_elements.length - 1];
        const last_del = last_add.querySelector("." + "delete-barcode");
        deleteBarcode(last_del);

    }));
}

/** Удаление штрихкода */
function deleteBarcode(element)
{
    element.addEventListener("click", (function(event)
    {
        const collection_id = this.dataset.idCollection;
        const element_id = this.dataset.idBarcode;

        const collection_current = document.querySelector(`[data-id="${collection_id}"]`);
        const collection_elements = collection_current.querySelectorAll(`[data-collection="${collection_id}"]`);

        let collection_element = collection_current.querySelector(`[data-id="${element_id}"]`);

        if(collection_element)
        {
            if(collection_elements.length === 1)
            {
                let dangerToast = "{ \"type\":\"danger\" , " +
                    "\"header\":\"Ошибка при изменении продукта\"  , " +
                    `"message" : "Достигнуто минимальное количество элементов" }`;

                createToast(JSON.parse(dangerToast));

                return;
            }

            collection_element.remove();
        }
    }));
}
