# Catálogo de produtos externo - Árago

## Descrição

Criação de uma página simples de catálogo de produtos para ser utilizada em campanhas no site da Árago.

## Entregáveis

- [ ] Front-end:
    - [ ] Layout
- [x] Back-end:
    - [x] API
    - [x] Banco de dados

## Pré-requisitos

Tenha certeza de ter instalado em sua máquina o Composer, algum servidor como XAMPP ou o servidor embutido do PHP instalado e, também, algum banco de dados - o Banco utilizado pelo projeto foi o mySQL.

## Inicialização

### Server

Com o projeto já na máquina local, acesse a pasta server, pelo terminal e, então, instale as dependências utilizando o comando composer install ou php composer.phar install e, logo após, composer dump-autoload -o para gerar o autoload das classes.

Também será necessário criar as variáveis de ambiente para inicialização do banco de dados. Para criar o arquivo .env basta seguir o exemplo presente no arquivo .env.example.

## Desenvolvimento

### Server

Inicie o servidor PHP seguindo os passos do servidor utilizado ou, caso utilize o servidor embutido do PHP, basta rodar, no terminal, o comando `composer server` para que seja iniciado o servidor.

Com o servidor iniciado já será possível acessar os produtos disponíveis na base através da rota /products/_:?campaign_. Os parâmetros permitidos pela rota são:

- campaing (opcional) - Nome da campanha solicitada, quando omitida retorna todos os produtos registrados na base. Deve ser passada como path e não query string.
- limit (opcional) - Limite de itens retornados em cada página.
- page (opcional) - página de exibição de produtos.
- name (opcional) - nome do produto que está sendo buscado.
- discount (opcional) - Faixa de desconto solicitada (Aceita mais de um valor separado pelo sinal de mais(+)).
- id (opcional) - Id do produto buscado.
- price (opcional) - Faixa de preço do produto (Aceita dois valores separados pelo sinal de adição(+)). Obs.: O valor não deve ser passado como um número inteiro sem possuir vírgulas ou pontos. Ex.: R$ 30,25 deve ser passado como 3025.

## Produtos

Todos os produtos são identificados pelo id. Todo produtos possui as seguintes propriedades:

Campo                           | Tipo          | Descrição
--------------------------------|---------------|----------------------------
id                              | string        | Id do produto
ean                             | string        | Código de barras do produto
name                            | string        | Nome do produto
price                           | string        | Preço formatado
price_not_formated              | integer       | Preço do produto sem formatação
promotional_price               | string        | Preço promocional do produto
promotional_price_not_formated  | integer       | Preço promocional sem formatação
url                             | string        | Url da página do produto
product_image                   | string        | Url da imagem
payment_option                  | string        | Texto de desconto do produto

## Uso

Para listar ou filtrar os produtos, pode-se utilizar a URL /products.

obs: Por padrão o limite de produtos retornados é 10 e o máximo é 100 e a página atual sempre a primeira

Alguns exemplos de filtro e busca de produtos:

- /products : Retorna todos os produtos, retornando a primeira página limitando em 10 produtos
- /products/?:camapnha : Retorna os produtos de determinada campanha
- /products?name=some%20%product : Retorna os produtos que contenham o nome passado
- /products?discount=40000 : Retorna os produtos com faixa de desconto entre os valores solicitados
- /products?productCategories=Emagrecimento+beleza+saude = Retorna os produtos que pertençam à categoria passada
- /products?price=3000+4000 : Faz a busca pela faixa de preço do produto

Os parâmetros limit e page pode ser enviado em conjunto com qualquer um dos outros filtros aceitos e, quando omitidos, será sempre associado a eles o valor pdrão de 10 e 1, respectivamente.

### Exemplo de resposta

Ao solicitar a url /products/teste?products?price=1+15900&limit=2, o retorno será como o exemplo a seguir

```json
{
  "data": [
    {
      "id": "1",
      "ean": "7898624342324",
      "name": "Produto de teste 1",
      "price": "119.00",
      "price_not_formated": 11900,
      "promotional_price": "107.00",
      "promotional_price_not_formated": 10700,
      "url": "https://www.url.com.br/produto-de-teste-500-ml",
      "product_image": "https://static3.tcdn.com.br/img/",
      "payment_option": "R$ 101,65 &agrave; vista com desconto"
    },
    {
      "id": "1",
      "ean": "7898624342324",
      "name": "Produto de teste 1",
      "price": "119.00",
      "price_not_formated": 11900,
      "promotional_price": "107.00",
      "promotional_price_not_formated": 10700,
      "url": "https://www.url.com.br/produto-de-teste-500-ml",
      "product_image": "https://static3.tcdn.com.br/img/",
      "payment_option": "R$ 101,65 &agrave; vista com desconto"
    }
  ],
  "pagination": {
    "totalProducts": 21,
	  "actualPage": 1,
	  "totalPages": 11,
	  "perPage": 2
  },
  "campaign": "teste"
}
```

## Tecnologias e ferramentas

- [PHP](https://www.php.net/)
- [MySQL](https://www.mysql.com/)
- [Composer](https://getcomposer.org/)
- [Insomnia](https://insomnia.rest/download)
- [Simple product API](https://github.com/Alessandro-Miranda/simple-product-API) - Com adaptação do código-fonte original

## Autores

[Alessandro Lima de Miranda](https://github.com/Alessandro-Miranda) - Desenvolvimento Back-end
[Luan Thomaz Abrantes Martinez](https://github.com/Thomazl) - Layout e Desenvolvimento Front-end 