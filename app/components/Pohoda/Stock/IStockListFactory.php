<?php


namespace App\Component;


interface IStockListFactory
{
    /**
     * @param array $filters
     * @return StockList
     */
    public function create(array $filters = []);
}