<?php

namespace App\Component\FlexiBee\PriceList;

use AccSync\FlexiBee\Exception\FlexiBeeConnectionException;
use App\Model\AccSyncFacade;
use App\Model\Enum\EFlashMessage;
use Nette\Application\UI\Control;
use Nette\Application\UI\Form;

class PriceListItemForm extends Control
{
    /**
     * @var int $id
     */
    private $id;
    /**
     * @var \stdClass $item
     */
    private $item;
    /**
     * @var AccSyncFacade
     */
    private $accSyncFacade;

    public function __construct(
        $id,
        AccSyncFacade $accSyncFacade
    )
    {
        parent::__construct();

        $this->id = $id;
        $this->accSyncFacade = $accSyncFacade;

        if (!empty($this->id))
        {
            $this->item = ($this->accSyncFacade->getPriceListItemFlexiBee($this->id))[0];
        }
    }

    public function render()
    {


        $this->template->setFile(__DIR__ . '/PriceListItemForm.latte');
        $this->template->render();
    }

    public function createComponentForm()
    {
        $form = new Form();

        $code = $form->addText('code', 'Code');

        $name = $form->addText('name', 'Name');

        $basePrice = $form->addText('basePrice', 'Default price');

        $vatRate = $form->addText('vatRate', 'Vat rate');

        if (!empty($this->item))
        {
            $code->setDefaultValue($this->item->kod);
            $name->setDefaultValue($this->item->nazev);
            $basePrice->setDefaultValue($this->item->cenaZakl);
            $vatRate->setDefaultValue($this->item->szbDph);
        }

        $form->addSubmit('submit', 'Create');

        $form->onSuccess[] = [$this, 'send'];

        return $form;
    }

    public function send(Form $form)
    {
        $values = $form->getValues(TRUE);

        try
        {
            $this->accSyncFacade->sendFlexiBeePriceListItem($values, $this->id);
        }
        catch (FlexiBeeConnectionException $e)
        {
            \Tracy\Debugger::log($e->getMessage(), 'error');
            $this->getPresenter()->flashMessage('Check connection to FlexiBee', EFlashMessage::ERROR);
            $this->getPresenter()->redirect('this');
        }
        catch (\ErrorException $e)
        {
            \Tracy\Debugger::log($e->getMessage(), 'error');
            $this->getPresenter()->flashMessage($e->getMessage(), EFlashMessage::ERROR);
            $this->getPresenter()->redirect('this');
        }

        $this->getPresenter()->flashMessage('Record successfully added', EFlashMessage::SUCCESS);
        $this->getPresenter()->redirect('FlexiBee:priceList');
    }
}