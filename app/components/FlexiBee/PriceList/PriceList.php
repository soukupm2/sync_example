<?php

namespace App\Component\FlexiBee\PriceList;

use AccSync\FlexiBee\Exception\FlexiBeeConnectionException;
use App\Model\AccSyncFacade;
use Nette\Application\UI\Control;
use Nette\Application\UI\Form;

class PriceList extends Control
{
    /**
     * @var array $filters
     */
    private $filters;
    /**
     * @var AccSyncFacade
     */
    private $accSyncFacade;

    public function __construct(
        array $filters,
        AccSyncFacade $accSyncFacade
    )
    {
        parent::__construct();

        $this->filters = $filters;
        $this->accSyncFacade = $accSyncFacade;
    }

    public function render()
    {
        $message = NULL;
        $priceListItems = NULL;

        try
        {
            $priceListItems = $this->accSyncFacade->getPriceListFlexiBee($this->filters);
        }
        catch (FlexiBeeConnectionException $e)
        {
            \Tracy\Debugger::log($e->getMessage(), 'error');
            $message = 'Chyba připojení k FlexiBee';
        }
        catch (\ErrorException $e)
        {
            \Tracy\Debugger::log($e->getMessage(), 'error');
            $message = $e->getMessage();
        }

        if (empty($priceListItems))
        {
            $message = 'No data found';
        }

        $this->template->message = $message;
        $this->template->priceListItems = $priceListItems;
        $this->template->filtersEmpty = $this->checkFiltersAreEmpty();

        $this->template->setFile(__DIR__ . '/PriceList.latte');
        $this->template->render();
    }

    public function createComponentFilterForm()
    {
        $form = new Form();

        $form->addText('id', 'ID');

        $form->addText('code', 'Code');

        $form->addText('name', 'Name');

        $form->addSubmit('submit', 'Filter');

        $form->setDefaults($this->filters);

        $form->onSuccess[] = [$this, 'filter'];

        return $form;
    }

    public function filter(Form $form)
    {
        $values = $form->getValues(TRUE);

        $this->getPresenter()->redirect('FlexiBee:priceList', ['filters' => $values]);
    }

    private function checkFiltersAreEmpty()
    {
        if (empty($this->filters))
        {
            return TRUE;
        }

        foreach ($this->filters as $filter)
        {
            if (!empty($filter))
            {
                return FALSE;
            }
        }

        return TRUE;
    }
}