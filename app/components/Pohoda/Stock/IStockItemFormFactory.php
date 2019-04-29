<?php


namespace App\Component;


interface IStockItemFormFactory
{
    /**
     * @return StockItemForm
     */
    public function create();
}