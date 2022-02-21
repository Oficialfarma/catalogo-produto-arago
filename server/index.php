<?php
require 'vendor/autoload.php';

use App\Controller\ProductController;
use App\Utils\LoadEnv;

LoadEnv::load(__DIR__);

if($_SERVER['REQUEST_METHOD'] === 'POST')
{
    header("HTTP/1.1 405 Method Not Allowed");
    exit();
}

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

$hasProductPath = array_search('products', explode('/', $uri));

if(!$hasProductPath)
{
    header("HTTP/1.1 404 Not Found");
    exit();
}

$urlPath = explode('/', $uri);
array_shift($urlPath);
$productCampaign = $urlPath[1] ?? null;

$productController = new ProductController($productCampaign);

if(isset($_SERVER['QUERY_STRING']))
{
    $queryString = $_SERVER['QUERY_STRING'];
    $productController->filterProducts($queryString);
}
else
{
    $productController->getAllProducts($productCampaign);
}