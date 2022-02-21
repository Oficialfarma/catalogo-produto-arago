// async function getcontent() {
//     try {
//         const products = await fetch('https://www.aragodermocosmeticos.com.br/web_api/products/+productId');
//         const productsJson = await products.json(products);
//         productslist();
//     } catch (error) {
//         console.log(error)
//     }

// }
// getcontent();

// params = {};

// params["id"] = "3";
// params["attrs"] = "Product.id,Product.name,Product.price,Product.Product.promotional_price,Product.url,Product.ProductImage[]";

// params = {};

// params["category_id"] = "123";
// params["page"] = "2";
// params["limit"] = "20";

//       $.ajax({
//         method: "GET",
//         url: "https://www.aragodermocosmeticos.com.br/web_api/products/",
//         data: params
//       }).done(function( response, textStatus, jqXHR ) {
//         console.log(response);
//       }).fail(function( jqXHR, status, errorThrown ){
//         var response = $.parseJSON( jqXHR.responseText );
//         console.log(response);
//       });