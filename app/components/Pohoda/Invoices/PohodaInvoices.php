<?php

namespace App\Component;

use AccSync\Pohoda\Exception\PohodaConnectionException;
use App\Model\AccSyncFacade;
use Nette\Application\UI\Control;
use Nette\Application\UI\Form;

class PohodaInvoices extends Control
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
        $invoices = NULL;

        try
        {
            $invoices = $this->accSyncFacade->getPohodaInvoices($this->filters);

            if ($invoices === NULL)
            {
                $message = 'No data found';
            }
        }
        catch (PohodaConnectionException $e)
        {
            \Tracy\Debugger::log($e->getMessage(), 'error');
            $message = 'Check pohoda connection';
        }

        $this->template->invoices = $invoices;
        $this->template->filtersEmpty = $this->checkFiltersAreEmpty();

        $this->template->message = $message;

        $this->template->setFile(__DIR__ . '/PohodaInvoices.latte');
        $this->template->render();
    }

    public function createComponentFilterForm()
    {
        $form = new Form();

        $form->addText('id', 'ID');
        $form->addText('date_from', 'Date from');
        $form->addText('date_to', 'Date to');

        $form->addSubmit('submit', 'Filter');

        $form->setDefaults($this->filters);

        $form->onSuccess[] = [$this, 'filter'];

        return $form;
    }

    public function filter(Form $form)
    {
        $values = $form->getValues(TRUE);

        $this->getPresenter()->redirect('Pohoda:default', ['filter' => $values]);
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