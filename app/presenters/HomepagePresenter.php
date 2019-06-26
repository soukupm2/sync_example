<?php

namespace App\Presenters;

use AccSync\FlexiBee\Data\FlexiBeeHelper;
use AccSync\FlexiBee\FlexiBeeConnectionFactory;
use AccSync\FlexiBee\Requests\GetDataRequest\PriceListRequest;
use AccSync\Pohoda\Collection\Invoice\InvoicesCollection;
use AccSync\Pohoda\Data\InvoiceParser;
use AccSync\Pohoda\Data\StockParser;
use AccSync\Pohoda\Entity\Invoice\Invoice;
use AccSync\Pohoda\Entity\Invoice\InvoiceHeader;
use AccSync\Pohoda\Entity\Invoice\InvoiceSummary;
use AccSync\Pohoda\PohodaConnectionFactory;
use AccSync\Pohoda\Requests\GetDataRequest\ListInvoiceRequest;
use AccSync\Pohoda\Requests\GetDataRequest\ListOrderRequest;
use Nette;

final class HomepagePresenter extends Nette\Application\UI\Presenter
{
    /**
     * @var PohodaConnectionFactory $pohodaConnectionFactory
     */
    private $pohodaConnectionFactory;
    /**
     * @var FlexiBeeConnectionFactory $flexiBeeConnectionFactory
     */
    private $flexiBeeConnectionFactory;

    public function __construct(
        PohodaConnectionFactory $pohodaConnectionFactory,
        FlexiBeeConnectionFactory $flexiBeeConnectionFactory
    )
    {
        parent::__construct();

        $this->pohodaConnectionFactory = $pohodaConnectionFactory;
        $this->flexiBeeConnectionFactory = $flexiBeeConnectionFactory;
    }

    public function actionDefault()
    {
        $this->redirect('Pohoda:stock');

        $connection = $this->pohodaConnectionFactory->create();
        $request = new ListOrderRequest(123456, 12345678, ListOrderRequest::ORDER_TYPE_ISSUED);
        $connection->setCustomRequest($request);
        $result = $connection->sendRequest();
        $this->template->result = $result;
    }

    public function actionFlexi()
    {
        $connection = $this->flexiBeeConnectionFactory->create();

        $request = $connection->sendIssuedInvoiceRequest()
            ->setId(140)
            ->setCode('00000005/19-1')
            ->setDescription('testing')
            ->setLeftToPay(100)
            ->setType('code:FAKTURA')
            ->setDueDate('2019')
            ->setReceivedNumber(1);

        \Tracy\Debugger::barDump($request->getRawData());

        $result = $connection->sendRequest();

        \Tracy\Debugger::barDump($result);
        \Tracy\Debugger::barDump($connection->getError());

        $this->template->result = $result;

    }

    public function actionFlexir()
    {
        $connection = $this->flexiBeeConnectionFactory->create();

        $request = $connection->sendReceivedInvoiceRequest()
            ->setCode('testasldfjasl')
            ->setDescription('testing')
            ->setLeftToPay(100)
            ->setVariableSymbol(12346468661353535)
            ->setDueDate('2019')
            ->setType('code:FAKTURA');

        \Tracy\Debugger::barDump($request->getRawData());

        $result = $connection->sendRequest();

        \Tracy\Debugger::barDump($result);
        \Tracy\Debugger::barDump($connection->getError());

        $this->template->result = $result;

    }

    public function actionFlexio()
    {
        $connection = $this->flexiBeeConnectionFactory->create();

        $request = $connection->sendReceivedOrderRequest()
            ->setCode('testasldfjasl')
            ->setType('code:OBP')
            ->setDescription('testing');

        \Tracy\Debugger::barDump($request->getRawData());

        $result = $connection->sendRequest();

        \Tracy\Debugger::barDump($result);
        \Tracy\Debugger::barDump($connection->getError());

        $this->template->result = $result;
        $this->template->setFile(__DIR__ . '/templates/Homepage/flexi.latte');
    }

    public function actionFlexiov()
    {
        $connection = $this->flexiBeeConnectionFactory->create();

        $request = $connection->sendIssuedOrderRequest()
            ->setCode('testasldfjasl')
            ->setType('code:OBV')
            ->setDescription('testing');

        \Tracy\Debugger::barDump($request->getRawData());

        $result = $connection->sendRequest();

        \Tracy\Debugger::barDump($result);
        \Tracy\Debugger::barDump($connection->getError());

        $this->template->result = $result;
        $this->template->setFile(__DIR__ . '/templates/Homepage/flexi.latte');
    }

    public function actionFlexit()
    {
        $connection = $this->flexiBeeConnectionFactory->create();

        $connection->getIssuedOrders();

        $result = $connection->sendRequest();

        \Tracy\Debugger::barDump($result);

        $this->template->result = $result;

    }

    public function actionFlexitt()
    {
        $connection = $this->flexiBeeConnectionFactory->create();

        $request = new PriceListRequest();

        $expression1 = new Condition();
        $expression1->setIdentifier('id');
        $expression1->setOperator('=');
        $expression1->setValue(4);

        $expression2 = new Condition();
        $expression2->setIdentifier('id');
        $expression2->setOperator('=');
        $expression2->setValue(2);

        $expression3 = new Condition();
        $expression3->setIdentifier('id');
        $expression3->setOperator('!=');
        $expression3->setValue(1);

        $filter = FlexiBeeHelper::joinConditions('or', $expression1, $expression2);

        $filter = FlexiBeeHelper::joinConditions('and', $filter, $expression3);

        $request->setUrlFilter($filter);

        $connection->setCustomRequest($request);

        $result = $connection->sendRequest();


        $result = json_decode($result);

        \Tracy\Debugger::barDump($result);

        $this->template->result = $result;

    }

    public function actionTest()
    {
        $connection = $this->pohodaConnectionFactory->create();

        $connection->setListStockRequest();
            // ->addFilterStoreIds([1]);

        $connection->sendRequest();

        $dom = $connection->getDOMResponse();

        $dom->save(__DIR__ . '/../../temp/zasoby_05_v2.0_response.xml');

        $this->template->result = StockParser::parse($connection->getStdClassResponse());
    }

    public function actionTestr()
    {

        // $dateFrom = new \DateTime('2018-01-21');
        // $dateTo= new \DateTime('2018-01-23');

        $connection = $this->pohodaConnectionFactory->create();

        $connection->setListOrderRequest(ListOrderRequest::ORDER_TYPE_ISSUED);
            // ->addFilterIns([85236972]);
            // ->addFilterDateRange($dateFrom, $dateTo);

        $connection->sendRequest();

        $dom = $connection->getDOMResponse();

        $dom->save(__DIR__ . '/../../temp/objednavky_02_v2.0_issued_response.xml');

        // $parsed = OrderParser::parse($connection->getParsedResponse());

        $this->template->result = $connection->getStdClassResponse();
        // $this->template->result = $parsed;
    }

    public function actionTestt()
    {
        $connection = $this->pohodaConnectionFactory->create();
        $connection->setListInvoiceRequest(ListInvoiceRequest::INVOICE_TYPE_ISSUED);
            // ->addFilterIns(85236972)
            // ->addFilterCompanyName('AK - Media a. s.');

        $connection->sendRequest();

        $dom = $connection->getDOMResponse();

        $dom->save(__DIR__ . '/../../temp/faktury_04_v2.0_response.xml');

        $result = InvoiceParser::parse($connection->getStdClassResponse());
        // $result = $connection->getStdClassResponse();

        // \Tracy\Debugger::barDump($result);

        $this->template->result = $result;
    }

    public function actionTesttt()
    {
        $connection = $this->pohodaConnectionFactory->create();

        $invoiceHeader = new InvoiceHeader();
        $invoiceHeader->setInvoiceType('issuedInvoice');
        $invoiceHeader->setDate(new \DateTime('2018-12-10'));
        $invoiceHeader->setAccountingIds('3Fv');
        $invoiceHeader->setText('testovaci zaznam');
        $invoiceHeader->setClassificationVatType('inland');
        $invoiceHeader->setPaymentType('draft');
        $invoiceHeader->setAccountIds('KB');
        $invoiceHeader->setNote('import test');

        $invoiceSummary = new InvoiceSummary();
        $invoiceSummary->setRoundingDocument('math2one');
        $invoiceSummary->setPriceNone(3018);
        $invoiceSummary->setPriceLow(60000);
        $invoiceSummary->setPriceHighSum(557);
        $invoiceSummary->setPriceRound(0);

        $invoice = new Invoice($invoiceHeader);
        $invoice->setInvoiceSummary($invoiceSummary);

        $invoicesCollection = new InvoicesCollection();
        $invoicesCollection->add($invoice);

        $request = $connection->setSendInvoiceRequest($invoicesCollection);

        $connection->sendRequest();

        $dom = $connection->getDOMResponse();
        $dom->save(__DIR__ . '/../../temp/test5.xml');

        $this->template->result = $connection->getStdClassResponse();
    }
}
