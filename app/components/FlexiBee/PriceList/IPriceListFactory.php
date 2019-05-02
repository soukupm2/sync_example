<?php


namespace App\Component\FlexiBee\PriceList;


interface IPriceListFactory
{
    /**
     * @param $filters
     * @return PriceList
     */
    public function create(array $filters);
}