<?php

namespace App\Model;

use AccSync\FlexiBee\Data\FlexiBeeHelper;
use AccSync\FlexiBee\Enum\EDefinedValues;
use AccSync\FlexiBee\Enum\EOperators;
use AccSync\FlexiBee\FlexiBeeConnectionFactory;
use AccSync\FlexiBee\UrlFilter\FlexiBeeCondition;
use AccSync\Pohoda\Collection\Stock\StockCollection;
use AccSync\Pohoda\Data\InvoiceParser;
use AccSync\Pohoda\Data\StockParser;
use AccSync\Pohoda\Entity\Stock\Stock;
use AccSync\Pohoda\Entity\Stock\StockHeader;
use AccSync\Pohoda\PohodaConnectionFactory;
use AccSync\Pohoda\Requests\GetDataRequest\ListInvoiceRequest;
use Nette\Utils\Validators;

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

    /**
     * @param array $filters
     * @return \stdClass
     * @throws \AccSync\FlexiBee\Exception\FlexiBeeConnectionException
     * @throws \ErrorException
     */
    public function getPriceListFlexiBee(array $filters)
    {
        $connection = $this->flexiBeeConnectionFactory->create();

        $conditions = [];

        if (!empty($filters['id']))
        {
            $condition = new FlexiBeeCondition();
            $condition->setIdentifier('id');
            $condition->setValue($filters['id']);
            $condition->setOperator(EOperators::EQUAL);

            $conditions[] = $condition;
        }

        if (!empty($filters['code']))
        {
            $condition = new FlexiBeeCondition();
            $condition->setIdentifier('kod');
            $condition->setValue('\'' . $filters['code'] . '\'');
            $condition->setOperator(EOperators::LIKE);

            $conditions[] = $condition;
        }

        if (!empty($filters['name']))
        {
            $condition = new FlexiBeeCondition();
            $condition->setIdentifier('nazev');
            $condition->setValue('\'' . $filters['name'] . '\'');
            $condition->setOperator(EOperators::LIKE);

            $conditions[] = $condition;
        }

        $filter = FlexiBeeHelper::joinConditions(EOperators::LOGICAL_AND, ...$conditions);

        $connection->getPriceList()
            ->setUrlFilter($filter)
            ->setOrder('id', EDefinedValues::DESC);


        $result = $connection->sendRequest();

        if ($connection->hasError())
        {
            throw new \ErrorException($connection->getError(), 0, E_WARNING);
        }

        return $result;
    }

    /**
     * @param int $id
     * @return \stdClass|null
     * @throws \AccSync\FlexiBee\Exception\FlexiBeeConnectionException
     */
    public function getPriceListItemFlexiBee($id)
    {
        if (!Validators::isNumericInt($id))
        {
            return NULL;
        }

        $connection = $this->flexiBeeConnectionFactory->create();

        $condition = new FlexiBeeCondition();
        $condition->setIdentifier('id');
        $condition->setValue($id);
        $condition->setOperator(EOperators::EQUAL);

        $connection->getPriceList()
            ->setUrlFilter($condition->getFullCondition());

        $result = $connection->sendRequest();

        return $result;
    }

    /**
     * @param array $values
     * @param null  $id
     * @return \stdClass
     * @throws \AccSync\FlexiBee\Exception\FlexiBeeConnectionException
     * @throws \ErrorException
     */
    public function sendFlexiBeePriceListItem($values, $id = NULL)
    {
        $connection = $this->flexiBeeConnectionFactory->create();

        $request = $connection->sendPriceListItem()
            ->setCode($values['code'])
            ->setName($values['name'])
            ->setBasePrice($values['basePrice'])
            ->setVatRate($values['vatRate']);

        if (!empty($id))
        {
            $request->setId($id);
        }

        $result = $connection->sendRequest();

        if ($connection->hasError())
        {
            throw new \ErrorException($connection->getError(), 0, E_WARNING);
        }

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

    /**
     * @param $values
     * @return bool
     * @throws \AccSync\Pohoda\Exception\PohodaConnectionException
     * @throws \ErrorException
     */
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
            throw new \ErrorException($connection->getError(), 0, E_WARNING);
        }

        return TRUE;
    }
}