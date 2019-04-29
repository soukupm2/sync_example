<?php

namespace App\Model;

use AccSync\FlexiBee\FlexiBeeConnectionFactory;
use AccSync\Pohoda\Collection\Stock\StockCollection;
use AccSync\Pohoda\Data\InvoiceParser;
use AccSync\Pohoda\Data\StockParser;
use AccSync\Pohoda\Entity\Stock\Stock;
use AccSync\Pohoda\Entity\Stock\StockHeader;
use AccSync\Pohoda\PohodaConnectionFactory;
use AccSync\Pohoda\Requests\GetDataRequest\ListInvoiceRequest;

class AccSyncFacade
{
    /**
     * @var FlexiBeeConnectionFactory
     */
    private $flexiBeeConnectionFactory;
    /**
     * @var PohodaConnectionFactory
     */
    private $pohodaConnectionFactory;

    public function __construct(
        FlexiBeeConnectionFactory $flexiBeeConnectionFactory,
        PohodaConnectionFactory $pohodaConnectionFactory
    )
    {
        $this->flexiBeeConnectionFactory = $flexiBeeConnectionFactory;
        $this->pohodaConnectionFactory = $pohodaConnectionFactory;
    }

    public function getPriceListFlexiBee()
    {
        $connection = $this->flexiBeeConnectionFactory->create();

        $connection->getPriceList();

        $result = $connection->sendRequest();

        return $result;
    }

    /**
     * @param array $filters
     * @return \AccSync\Pohoda\Collection\Invoice\InvoicesCollection|NULL
     * @throws \AccSync\Pohoda\Exception\PohodaConnectionException
     */
    public function getPohodaInvoices(array $filters = [])
    {
        $connection = $this->pohodaConnectionFactory->create();

        $request = $connection->setListInvoiceRequest(ListInvoiceRequest::INVOICE_TYPE_ISSUED);

        if (!empty($filters['id']))
        {
            $request->addFilter('id', $filters['id']);
        }

        if (!empty($filters['date_from']) || !empty($filters['date_to']))
        {
            $from = NULL;
            $to = NULL;
            try
            {
                if (!empty($filters['date_from']))
                {
                    $from = new \DateTime($filters['date_from']);
                }
                if (!empty($filters['date_to']))
                {
                    $to = new \DateTime($filters['date_to']);
                }
            }
            catch (\Exception $e)
            {
                null;
            }

            if (!empty($filters['date_from']) && !empty($filters['date_to']))
            {
                $request->addFilterDateRange($from, $to);
            }
            elseif (!empty($filters['date_from']))
            {
                $request->addFilterDateRange($from, $to);
            }
            elseif (!empty($filters['date_to']))
            {
                $request->addFilterDateRange($from, $to);
            }
        }

        $connection->sendRequest();

        $parsed = InvoiceParser::parse($connection->getStdClassResponse());

        return $parsed;
    }

    /**
     * @param array $filters
     * @return \AccSync\Pohoda\Collection\Stock\StockCollection|null
     * @throws \AccSync\Pohoda\Exception\PohodaConnectionException
     * @throws \ErrorException
     */
    public function getPohodaStock(array $filters = [])
    {
        $connection = $this->pohodaConnectionFactory->create();

        $request = $connection->setListStockRequest();

        if (!empty($filters['id']))
        {
            $request->addFilter('id', $filters['id']);
        }
        if (!empty($filters['storage_ids']))
        {
            $request->addFilterStorage($filters['storage_ids']);
        }
        if (!empty($filters['name']))
        {
            $request->addFilter('name', $filters['name']);
        }

        $connection->sendRequest();

        if ($connection->hasError())
        {
            throw new \ErrorException($connection->getError());
        }

        $parsed = StockParser::parse($connection->getStdClassResponse());

        return $parsed;
    }

    public function sendPohodaStock($values)
    {
        $connection = $this->pohodaConnectionFactory->create();

        $stockCollection = new StockCollection();

        $stock = new Stock();

        $stockHeader = new StockHeader();
        $stockHeader->setStorageIds($values->storage_ids);
        $stockHeader->setName($values->name);
        $stockHeader->setPurchasingPrice($values->purchasingPrice);
        $stockHeader->setSellingPrice($values->sellingPrice);
        if (!empty($values->description))
        {
            $stockHeader->setDescription($values->description);
        }
        $stockHeader->setStockType('card');
        $stockHeader->setCode($values->name);
        $stockHeader->setEan('94216416');
        $stockHeader->setPLU('22');
        $stockHeader->setIsSales(true);
        $stockHeader->setIsSerialNumber(true);
        $stockHeader->setIsInternet(true);
        $stockHeader->setIsBatch(true);
        $stockHeader->setPurchasingRateVAT('high');
        $stockHeader->setSellingRateVAT('high');
        $stockHeader->setNameComplement('ISO 9001');
        $stockHeader->setUnit('ks');
        $stockHeader->setTypePriceIds('ostatní');
        $stockHeader->setLimitMax(0);
        $stockHeader->setLimitMin(0);
        $stockHeader->setMass(5.1);
        $stockHeader->setSupplierId(25);
        $stockHeader->setOrderQuantity(0);
        $stockHeader->setShortName('Eizo 1478');
        $stockHeader->setGuaranteeType('year');
        $stockHeader->setGuarantee(2);
        $stockHeader->setProducer('Čermák s.r.o.');
        $stockHeader->setYield(646000);
        $stockHeader->setNote('Načteno z XML.');

        $stock->setStockHeader($stockHeader);

        $stockCollection->add($stock);

        $connection->setSendStockRequest($stockCollection);

        $connection->sendRequest();

        if ($connection->hasError())
        {
            throw new \ErrorException($connection->getError());
        }

        return TRUE;
    }
}