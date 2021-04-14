<?php

declare(strict_types=1);

namespace App\AdminModule\Forms\Verzie;

use App\Model;
use Nette\Application\UI\Form;

/**
 * Tovarnicka pre formular na editaciu verzie
 * Posledna zmena 14.04.2021
 * 
 * @author     Ing. Peter VOJTECH ml. <petak23@gmail.com>
 * @copyright  Copyright (c) 2012 - 2021 Ing. Peter VOJTECH ml.
 * @license
 * @link       http://petak23.echo-msz.eu
 * @version    1.0.7
 */
class EditVerzieFormFactory {
  /** @var Model\Verzie */
	private $verzie;
  /**
   * @param Model\Verzie $verzie */
  public function __construct(Model\Verzie $verzie) {
		$this->verzie = $verzie;
	}
  
  /**
   * Formular pre editaciu udajov verzie.
   * @return Form */  
  public function create(): Form  {
    $form = new Form();
		$form->addProtection();
    $form->addGroup();
    $form->addHidden("id");
    $form->addHidden("id_user_main");
		$form->addText('cislo', 'Číslo verzie:', 0, 80)
         ->setAttribute('autofocus', 'autofocus')
				 ->addRule(Form::FILLED, 'Číslo verzie musí byť zadané!');
		$form->addText('subory', 'Zmenené súbory:', 0, 80);
		$form->addTextArea('text', 'Popis zmien:', 0, 15)->getControlPrototype()->class("texyla");
		$form->addSubmit('uloz', 'Ulož')
         ->setAttribute('class', 'btn btn-success')
         ->onClick[] = [$this, 'editVerzieFormSubmitted'];
    $form->addSubmit('cancel', 'Cancel')->setAttribute('class', 'btn btn-default')
         ->setValidationScope([]);
		return $form;
	}
  
  /** 
   * Spracovanie vstupov z formulara
   * @param \Nette\Forms\Controls\SubmitButton $button Data formulara */
	public function editVerzieFormSubmitted(\Nette\Forms\Controls\SubmitButton $button)	{
    $values = $button->getForm()->getValues();
    try {
			$this->verzie->ulozVerziu($values);
		} catch (Database\DriverException $e) {
			$button->addError($e->getMessage());
		}
	}
}