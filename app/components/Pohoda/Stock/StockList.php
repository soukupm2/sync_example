<?php

namespace App\Component;

use AccSync\Pohoda\Exception\PohodaConnectionException;
use App\Model\AccSyncFacade;
use Nette\Application\UI\Control;
use Nette\Application\UI\Form;

class StockList extends Control
{
    /**
     * @var array $params
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

        $this->accSyncFacade = $accSyncFacade;
        $this->filters = $filters;
    }

    public function render()
    {
        $message = NULL;
        $stock = NULL;

        try
        {
            $stock = $this->accSyncFacade->getPohodaStock($this->filters);

            if ($stock === NULL)
            {
                $message = 'No data found';
            }
        }
        catch (PohodaConnectionException $e)
        {
            \Tracy\Debugger::log($e->getMessage(), 'error');
            $message = 'Check pohoda connection';
        }
        catch (\ErrorException $e)
        {
            \Tracy\Debugger::log($e->getMessage(), 'error');
            $message = $e->getMessage();
        }

        $this->template->stock = $stock;
        $this->template->filtersEmpty = $this->checkFiltersAreEmpty();

        $this->template->message = $message;

        $this->template->setFile(__DIR__ . '/StockList.latte');
        $this->template->render();
    }

    public function createComponentFilterForm()
    {
        $form = new Form();

        $form->addText('id', 'ID');
        $form->addText('storage_ids', 'Storage IDS');
        $form->addText('name', 'Name');

        $form->addSubmit('submit', 'Filter');

        $form->setDefaults($this->filters);

        $form->onSuccess[] = [$this, 'filter'];

        return $form;
    }

    public function filter(Form $form)
    {
        $values = $form->getValues(TRUE);

        $this->getPresenter()->redirect('Pohoda:stock', ['filter' => $values]);
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