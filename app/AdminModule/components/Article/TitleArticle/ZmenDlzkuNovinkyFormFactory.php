<?php
declare(strict_types=1);

namespace App\AdminModule\Components\Article\TitleArticle;

use App\Model;
use Nette\Application\UI\Form;

/**
 * Formular a jeho spracovanie pre zmenu dlzky sledovania ako novinky polozky.
 * Posledna zmena 14.05.2020
 * 
 * @author     Ing. Peter VOJTECH ml. <petak23@gmail.com>
 * @copyright  Copyright (c) 2012 - 2020 Ing. Peter VOJTECH ml.
 * @license
 * @link       http://petak23.echo-msz.eu
 * @version    1.0.1
 */
class ZmenDlzkuNovinkyFormFactory {
  /** @var Model\Hlavne_menu */
	private $hlavne_menu;
  
  /** @var array Hodnoty id=>nazov pre formulare z tabulky dlzka_novinky*/
	private $dlzka_novinky;
  
  /**
   * @param Model\Hlavne_menu $hlavne_menu
   * @param Model\Dlzka_novinky $dlzka_novinky */
  public function __construct(Model\Hlavne_menu $hlavne_menu, Model\Dlzka_novinky $dlzka_novinky) {
		$this->hlavne_menu = $hlavne_menu;
    $this->dlzka_novinky = $dlzka_novinky->dlzkaNovinkyForm();
	}
  
  /**
   * Formular pre zmenu dlzky sledovania ako novinky.
   * @param int $id Id polozky v hlavnom menu
   * @param int $id_dlzka_novinky Sucasna dlzka sledovania ako novinky
   * @return Nette\Application\UI\Form */  
  public function create(int $id = 0, int $id_dlzka_novinky = 0): Form {
		$form = new Form();
		$form->addProtection();
    $form->addHidden("id", (string)$id);
    $form->addRadioList('id_dlzka_novinky', 'Nová dĺžka sledovania ako novinky:', $this->dlzka_novinky)
         ->setDefaultValue($id_dlzka_novinky)
         ->setOption('description', 'Dĺžka času (v dňoch), za ktorý je článok považovaný za novinku od jeho poslednej zmeny.');
    $form->addSubmit('uloz', 'Zmeň')
         ->setAttribute('class', 'btn btn-success')
         ->onClick[] = [$this, 'zmenUrovenRegistracieFormSubmitted'];
    $form->addSubmit('cancel', 'Cancel')
         ->setAttribute('class', 'btn btn-default')
         ->setAttribute('data-dismiss', 'modal')
         ->setAttribute('aria-label', 'Close')
         ->setValidationScope([]);
		return $form;
	}
  
  /** 
   * Spracovanie formulara pre dlzky sledovania ako novinky.
   * @param \Nette\Forms\Controls\SubmitButton $button Data formulara */
  public function zmenUrovenRegistracieFormSubmitted(\Nette\Forms\Controls\SubmitButton $button) {
		$values = $button->getForm()->getValues(); 	//Nacitanie hodnot formulara
    try {
			$this->hlavne_menu->zmenDlzkuNovinky($values);
		} catch (Database\DriverException $e) {
			$button->addError($e->getMessage());
		}
  }
}