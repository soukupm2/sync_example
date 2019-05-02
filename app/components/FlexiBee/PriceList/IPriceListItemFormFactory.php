<?php

namespace App\Component\FlexiBee\PriceList;

interface IPriceListItemFormFactory
{
    /**
     * @param null $id
     * @return PriceListItemForm
     */
    public function create($id = NULL);
}