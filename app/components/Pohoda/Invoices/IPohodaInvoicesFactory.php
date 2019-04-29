<?php

namespace App\Component;

interface IPohodaInvoicesFactory
{
    /**
     * @param array $filters
     * @return PohodaInvoices
     */
    public function create(array $filters = []);
}