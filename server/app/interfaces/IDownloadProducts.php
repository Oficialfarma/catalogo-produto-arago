<?php

namespace App\Interfaces;

interface IDownloadProducts
{
    /**
     * Obtém informações sobre paginação
     *
     * @return self
     */
    public function getPagingInfos(): self;

    /**
     * Obtém as informações do produto
     *
     * @return self
     */
    public function getProductInformations(): self;

    /**
     * Invoca a inserção dos produtos no banco de dados
     *
     * @return void
     */
    public function saveProducts(): void;
}