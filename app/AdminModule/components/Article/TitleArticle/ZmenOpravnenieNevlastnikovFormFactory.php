<?php
declare(strict_types=1);

namespace App\AdminModule\Components\Article\TitleArticle;

use App\Model;
use Nette\Application\UI\Form;

/**
 * Formular a jeho spracovanie pre zmenu opravnenia nevlastnikov polozky.
 * Posledna zmena 14.04.2021
 * 
 * @author     Ing. Peter VOJTECH ml. <petak23@gmail.com>
 * @copyright  Copyright (c) 2012 - 2021 Ing. Peter VOJTECH ml.
 * @license
 * @link       http://petak23.echo-msz.eu
 * @version    1.0.3
 */
class ZmenOpravnenieNevlastnikovFormFactory {
  /** @var Model\Hlavne_menu */
	private $hlavne_menu;
  
  /** @var array Hodnoty id=>nazov pre formulare z tabulky hlavne_menu_opravnenie */
	private $hlavne_menu_opravnenie;
  
  /**
   * @param Model\Hlavne_menu $hlavne_menu
   * @param Model\Hlavne_menu_opravnenie $hlavne_menu_opravnenie */
  public function __construct(Model\Hlavne_menu $hlavne_menu, Model\Hlavne_menu_opravnenie $hlavne_menu_opravnenie) {
		$this->hlavne_menu = $hlavne_menu;
    $this->hlavne_menu_opravnenie = $hlavne_menu_opravnenie->opravnenieForm();
	}
  
  /**
   * Formular.
   * @param int $id Id polozky v hlavnom menu
   * @param int $id_hlavne_menu_opravnenie Sucasnopravnenie nevlastnikov
   * @return Form */  
  public function create(int $id,int $id_hlavne_menu_opravnenie): Form  {
		$form = new Form();
		$form->addProtection();
    $form->addHidden("id", (string)$id);
    $form->addRadioList('id_hlavne_menu_opravnenie', 'Nové oprávnenie pre nevlastníkov:', $this->hlavne_menu_opravnenie)
         ->setDefaultValue($id_hlavne_menu_opravnenie)
         ->setOption('description', 'Výberom sa pridajú oprávnenia aj pre užívateľov, ktorý nie sú vlastníci článku.');
    $form->addSubmit('uloz', 'Zmeň')
         ->setAttribute('class', 'btn btn-success')
         ->onClick[] = [$this, 'formSubmitted'];
    $form->addSubmit('cancel', 'Cancel')
         ->setAttribute('class', 'btn btn-default')
         ->setAttribute('data-dismiss', 'modal')
         ->setAttribute('aria-label', 'Close')
         ->setValidationScope([]);
		return $form;
	}
  
  /** 
   * Spracovanie formulara.
   * @param \Nette\Forms\Controls\SubmitButton $button Data formulara */
  public function formSubmitted(\Nette\Forms\Controls\SubmitButton $button): void {
		$values = $button->getForm()->getValues(); 	//Nacitanie hodnot formulara
    try {
			$this->hlavne_menu->zmenOpravnenieNevlastnikov($values);
		} catch (Database\DriverException $e) {
			$button->addError($e->getMessage());
		}
  }
}