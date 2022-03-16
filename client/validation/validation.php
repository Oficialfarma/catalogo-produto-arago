<?php
    header('Content-Type: application/json; charset=UTF-8');
    header("Access-Control-Allow-Origin: 127.0.0.1:5500");
    header("Access-Contel-Allow-Methods: GET, POST,");
    header("Access-Control-Allow-Headers: Content-Type, Authorization");

    $data = json_decode(file_get_contents("php://input"));
    $sku = $data->sku;
    $name = $data->name;
    $price = $data->price;
    $url = $data->url;
    $rlImg = $data->rlImg;
    $paymentI = $data->paymentI;

    $username = 'root';
    $password = 'admin'; 
    $queryInstruction = 'INSERT INTO products (sku, name, price, url, rlImg, paymentI) VALUES (:sku, :name, :price, :url, :rlImg, :paymentI)';
    $_result = '';

    try {
        $connection = new PDO('mysql:host=127.0.0.1:3306; dbname=products', $username, $password);
        $connection -> setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $result = $connection -> prepare('SELECT * FROM products WHERE nome=:nome');
        $result -> execute(array(
            ':sku' => $sku
        ));
        if($result ->rowCount() == 0)
        {
            $result = $connection -> prepare($queryInstruction);
            $result -> execute(array(
                ':sku' => $sku,
                ':name' => $name,
                ':price' => $price,
                ':url' => $url,
                ':rlImg' => $rlImg,
                ':paymentI' => $paymentI
            ));
            $_result = 'Product inserted';
        }
        else
        {
            $_result = 'Product already exists';
        }
    } catch (PDOException $e) {
        echo json_encode('Error: '.$e->getMessage());
    }

?>