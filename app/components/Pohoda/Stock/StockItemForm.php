<?php

namespace App\Component;

use AccSync\Pohoda\Exception\PohodaConnectionException;
use App\Model\AccSyncFacade;
use App\Model\Enum\EFlashMessage;
use Nette\Application\UI\Control;
use Nette\Application\UI\Form;

class StockItemForm extends Control
{
    const REQUIRED_TEXT = 'Field %label is required';

    const STORAGE_IDS = [
        'ZBOŽÍ/Nábytek/Kuchyně' => 'ZBOŽÍ/Nábytek/Kuchyně',
        'ZBOŽÍ/Nábytek/Pro firmy' => 'ZBOŽÍ/Nábytek/Pro firmy',
        'ZBOŽÍ/Nábytek/Ostatní' => 'ZBOŽÍ/Nábytek/Ostatní',
        'ZBOŽÍ/Léčiva' => 'ZBOŽÍ/Léčiva',
        'MATERIÁL' => 'MATERIÁL',
        'PRODEJ/Nábytek/Ostatní' => 'PRODEJ/Nábytek/Ostatní',
    ];

    /**
     * @var AccSyncFacade
     */
    private $accSyncFacade;

    public function __construct(
        AccSyncFacade $accSyncFacade
    )
    {
        parent::__construct();
        $this->accSyncFacade = $accSyncFacade;
    }

    public function render()
    {
        $this->template->setFile(__DIR__ . '/StockItemForm.latte');
        $this->template->render();
    }

    public function createComponentForm()
    {
        $form = new Form();

        $form->addSelect('storage_ids', 'Storage IDS', self::STORAGE_IDS)
            ->setRequired(self::REQUIRED_TEXT);

        $form->addText('name', 'Name')
            ->setRequired(self::REQUIRED_TEXT);

        $form->addText('purchasingPrice', 'Purchasing price')
            ->setRequired(self::REQUIRED_TEXT);

        $form->addText('sellingPrice', 'Selling price')
            ->setRequired(self::REQUIRED_TEXT);

        $form->addTextArea('description', 'Description');

        $form->addSubmit('submit', 'Send');

        $form->onSuccess[] = [$this, 'send'];

        return $form;
    }

    public function send(Form $form)
    {
        $values = $form->getValues();

        try
        {
            $this->accSyncFacade->sendPohodaStock($values);
        }
        catch (PohodaConnectionException $e)
        {
            \Tracy\Debugger::log($e->getMessage(), 'error');
            $this->getPresenter()->flashMessage('Check connection to Pohoda', EFlashMessage::ERROR);
            $this->getPresenter()->redirect('this');
        }
        catch (\ErrorException $e)
        {
            \Tracy\Debugger::log($e->getMessage(), 'error');
            $this->getPresenter()->flashMessage($e->getMessage(), EFlashMessage::ERROR);
            $this->getPresenter()->redirect('this');
        }

        $this->getPresenter()->flashMessage('Stock item saved', EFlashMessage::SUCCESS);
        $this->getPresenter()->redirect('Pohoda:stock');
    }
}