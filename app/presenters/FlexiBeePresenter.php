<?php

namespace App\Presenters;

use App\Component\FlexiBee\PriceList\IPriceListFactory;
use App\Component\FlexiBee\PriceList\IPriceListItemFormFactory;
use Nette\Application\UI\Presenter;

class FlexiBeePresenter extends Presenter
{
    /** @var array $filters */
    private $filters;
    /** @var int $id */
    private $id;

    /**
     * @var IPriceListFactory
     */
    private $priceListFactory;
    /**
     * @var IPriceListItemFormFactory
     */
    private $priceListItemFormFactory;

    public function __construct(
        IPriceListFactory $priceListFactory,
        IPriceListItemFormFactory $priceListItemFormFactory
    )
    {
        parent::__construct();
        $this->priceListFactory = $priceListFactory;
        $this->priceListItemFormFactory = $priceListItemFormFactory;
    }

    public function actionPriceList($filters = [])
    {
        $this->filters = $filters;
    }

    public function actionPriceListItem($id = NULL)
    {
        $this->id = $id;
    }

    /**
     * @return \App\Component\FlexiBee\PriceList\PriceList
     */
    public function createComponentPriceList()
    {
        return $this->priceListFactory->create($this->filters);
    }

    /**
     * @return \App\Component\FlexiBee\PriceList\PriceListItemForm
     */
    public function createComponentPriceListItemForm()
    {
        return $this->priceListItemFormFactory->create($this->id);
    }
}