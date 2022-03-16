/**
 * @file This function make the request on the server to to store the information in the database.
 * 
 * Convert this datas in a json, and send to server by post request on the body.
 * 
 * ps.: it wait to a response also in json
 * 
 * This function displays the dialog modal changing the text based on the response 
 * 
 * @copyright Oficialfarma 2020
 * 
 * @returns {boolean}
 *
 * @author Luan Thomaz Abrantes Martinez
 * @version 1.0
 * 
 * 
 */


const productRequest = (sku, name, price, url, urlImg, paymentI) => {
    const url = 'http://localhost:80/validation/validation.php';
    const postObject = json.stringfy({
        sku,
        name,
        price,
        url,
        urlImg,
        paymentI
    });

    postObject(sku, name, price, url, urlImg, paymentI);
    console.log(postObject);



    // carrega as informações da url
    fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: postObject
        }).then(resp => resp.json())
        .then(response => {
            document.querySelector('.catalog').innerHTML = `
        <div class="card">
                    <a href="${response.url}">
                        class="productPhoto"><img src="${response.urlImg}" alt="${response.name}"></a>
                         class="photo" /></a>
                    <a class="cardInformation" href="${response.url}">
                        <h2 class="productName">${response.name}</h2>
                        <p class="productPrice">${response.price}</p>
                        <a href="${response.url}"
                         class="buttonProduct">Comprar</a>
                    </a>
                    <p class="descont">${response.paymentI}</p>
                </div>
        `;
        })
}