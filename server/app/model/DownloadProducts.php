<?php
namespace App\Model;

set_time_limit(3600);

use App\Interfaces\IDownloadProducts;
use App\Repositories\ProductsBase;
use App\Utils\RegisterLog;

/**
* Inicia o download dos produtos e salva no banco
*/
class DownloadProducts implements IDownloadProducts
{
    private $curl;
    private $amountProducts;
    private $products = array();
    private $limit;
    private $pages;
    
    function __construct()
    {
        $this->curl = curl_init();
        $this->amountProducts = 0;
        $this->limit = 50;
    }

    /**
     * Obtém informações sobre paginação
     *
     * @return self
     */
    public function getPagingInfos(): self
    {
        $this->prepareRequest("http://aragodermocosmeticos.com.br/web_api/products?page=1&limit=1");

        $pageInfo = curl_exec($this->curl);
        $error = curl_error($this->curl);

        $this->checkError("Error getting pagination infos", $error);

        $page = json_decode($pageInfo, true);

        $this->amountProducts = $page['paging']['total'];
        $this->pages = ceil($this->amountProducts / $this->limit);

        return $this;
    }

    /**
     * Obtém as informações sobre preço (De/por) dos produtos com base no sku
     *
     * @return self
     */
    public function getProductInformations(): self
    {   
        for($i = 1; $i <= $this->pages; $i++)
        {
            $this->prepareRequest("http://aragodermocosmeticos.com.br/web_api/products?page={$i}&limit={$this->limit}");
            
            $products = curl_exec($this->curl);
            $error = curl_error($this->curl);
    
            $this->checkError("Error downloading products", $error);
    
            $products = json_decode($products, true);
            
            array_push(
                $this->products,
                $this->setProductInformations($products['Products'])
            );
        }

        $this->clearVariables($products);

        return $this;
    }

    /**
     * Invoca a inserção dos produtos no banco de dados
     *
     * @return void
     */
    public function saveProducts(): void
    {
        $db = new ProductsBase();
        
        $db->insertProducts(array_unique($this->products, SORT_REGULAR), "product");
    }

    /**
     * Preenche as informações de preço em um array temporário e, também, repassa as informações finais para o array com todas as informações do produto e limpa as chaves do array temporário
     *
     * @param array  $json
     * @return array
     */
    private function setProductInformations($json)
    {
        $keys = array(
            'id',
            'ean',
            'payment_option',
            'name',
            'price',
            'promotional_price'
        );

        $internalLoopKeys = array(
            'url',
            'ProductImage'
        );

        $productInfos = array();

        foreach($json as $key => $value)
        {
            $actualProduct = array();

            array_walk($value['Product'], function($value, $key) use (&$actualProduct, $keys, $internalLoopKeys) {
                if(in_array($key, $keys))
                {
                    if($key === 'price' || $key === 'promotional_price')
                    {
                        $specialKey = ($key === 'price') ? 'price_not_formated' : 'promotional_price_not_formated';
                        $actualProduct[$key] = $value;
                        $actualProduct[$specialKey] = intval(str_replace('.', '', $value));
                        
                    }
                    else
                    {
                        $actualProduct[$key] = $value;
                    }
                }

                if(in_array($key, $internalLoopKeys))
                {
                    foreach($value as $internalKey => $internalValue)
                    {
                        if($key === 'ProductImage')
                        {
                            $actualProduct['product_image'] = $internalValue['https'];
                            continue;
                        }

                        $actualProduct[$key] = $internalValue;
                    }
                }
            });
             
            array_push($productInfos, $actualProduct);
        }
        
        return $productInfos;
    }

    /**
     * Verifica se houve algum erro na requisição e, caso tenha, registra o log e finaliza a execução
     *
     * @param string $message
     * @param Error  $error
     * @return void
     */
    private function checkError($message, $error)
    {
        if($error)
        {
            RegisterLog::RegisterLog($message, $error, "exceptions.log");
            exit();
        }
    }

    /**
     * Prepara a requisição a ser feita
     *
     * @param string $url
     * @return void
     */
    private function prepareRequest($url)
    {
        curl_setopt_array($this->curl, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET"
        ]);
    }

    private function clearVariables($var)
    {
        unset($var);
    }

    function __destruct()
    {
        curl_close($this->curl);
    }
}