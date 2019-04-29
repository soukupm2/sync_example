<?php

namespace App\Presenters;

use App\Component\IPohodaInvoicesFactory;
use App\Component\IStockItemFormFactory;
use App\Component\IStockListFactory;
use Nette\Application\UI\Presenter;

class PohodaPresenter extends Presenter
{
    /**
     * @var array $filters
     */
    private $filter;

    /**
     * @var IPohodaInvoicesFactory
     */
    private $pohodaInvoicesFactory;
    /**
     * @var IStockListFactory
     */
    private $stockListFactory;
    /**
     * @var IStockItemFormFactory
     */
    private $stockItemFormFactory;

    public function __construct(
        IPohodaInvoicesFactory $pohodaInvoicesFactory,
        IStockListFactory $stockListFactory,
        IStockItemFormFactory $stockItemFormFactory
    )
    {
        parent::__construct();

        $this->pohodaInvoicesFactory = $pohodaInvoicesFactory;
        $this->stockListFactory = $stockListFactory;
        $this->stockItemFormFactory = $stockItemFormFactory;
    }

    public function actionDefault($filter = [])
    {
        $this->filter = $filter;
    }

    public function actionStock($filter = [])
    {
        $this->filter = $filter;
    }

    public function actionAddStockItem()
    {

    }

    public function createComponentPohodaInvoices()
    {
        return $this->pohodaInvoicesFactory->create($this->filter);
    }

    public function createComponentStockList()
    {
        return $this->stockListFactory->create($this->filter);
    }

    public function createComponentStockItemForm()
    {
        return $this->stockItemFormFactory->create();
    }
}