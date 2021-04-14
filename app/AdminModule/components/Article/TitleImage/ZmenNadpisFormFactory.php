<?php
declare(strict_types=1);

namespace App\AdminModule\Components\Article\TitleImage;

use App\Model;
use Nette\Application\UI\Form;

/**
 * Formular a jeho spracovanie pre zmenu vlastnika polozky.
 * Posledna zmena 14.04.2021
 * 
 * @author     Ing. Peter VOJTECH ml. <petak23@gmail.com>
 * @copyright  Copyright (c) 2012 - 2021 Ing. Peter VOJTECH ml.
 * @license
 * @link       http://petak23.echo-msz.eu
 * @version    1.0.5
 */
class ZmenNadpisFormFactory {
  /** @var Model\Hlavne_menu_lang */
	private $hlavne_menu_lang;
  /** @var Model\Hlavne_menu */
	private $hlavne_menu;
  /** @var Model\Lang */
  public $lang;
 
  
  /**
   * @param Model\Hlavne_menu $hlavne_menu
   * @param Model\Hlavne_menu_lang $hlavne_menu_lang
   * @param Model\Lang $lang */
  public function __construct(Model\Hlavne_menu $hlavne_menu, Model\Hlavne_menu_lang $hlavne_menu_lang, Model\Lang $lang) {
		$this->hlavne_menu = $hlavne_menu;
    $this->hlavne_menu_lang = $hlavne_menu_lang;
    $this->lang = $lang;
	}
  
  /**
   * Formular pre zmenu vlastnika polozky.
   * @param int $id Id polozky v hlavnom menu
   * @return Form */  
  public function create(int $id): Form  {
    $vychodzie_pre_form = array_merge($this->hlavne_menu_lang, ['langtxt' => ""]);
		foreach ($this->jaz as $j) { //Pridanie vychodzich hodnot pre jazyky
      $vychodzie_pre_form = array_merge($vychodzie_pre_form, [
        $j->skratka.'_menu_name'=>"",
        $j->skratka.'_h1part2'=>"",
        $j->skratka.'_view_name'=>"",
      ]);
      $vychodzie_pre_form["langtxt"] .= " ".$j->skratka;
    }
    $vychodzie_pre_form["langtxt"] = trim($vychodzie_pre_form["langtxt"]);
		$form = new Form();
		$form->addProtection();
    $form->addHidden("id", (string)$id);
    foreach ($this->lang->findAll() as $j) {
      $form->addText($j->skratka.'_menu_name', 'Názov položky pre jazyk'.$j->nazov.":", 30, 100)
           ->addRule(Form::MIN_LENGTH, 'Názov musí mať spoň %d znaky!', 2)
           ->setRequired('Názov  pre jazyk "'.$j->nazov.'" musí byť zadaný!');
      $form->addText($j->skratka.'_h1part2', 'Druhá časť nadpisu pre jazyk'.$j->nazov.":", 90, 100);
      $form->addText($j->skratka.'_view_name', 'Podrobnejší popis položky pre jazyk'.$j->nazov.":", 90, 255)
           ->addRule(Form::MIN_LENGTH, 'Popis musí mať spoň %d znaky!', 2)
           ->setOption('description', 'Podrobnejší popis položky slúži pre vyhľadávače a zároveň ako pomôcka pre užívateľa, keď príde ukazovateľom myši nad odkaz(bublinová nápoveda).')
           ->setRequired('Popis pre jazyk "'.$j->nazov.'" musí byť zadaný!');
		}
    $form->addSubmit('uloz', 'Zmeň')
         ->setAttribute('class', 'btn btn-success')
         ->onClick[] = [$this, 'zmenVlastnikaFormSubmitted'];
    $form->addSubmit('cancel', 'Cancel')
         ->setAttribute('class', 'btn btn-default')
         ->setAttribute('data-dismiss', 'modal')
         ->setAttribute('aria-label', 'Close')
         ->setValidationScope([]);
		return $form;
	}
  
  /** 
   * Spracovanie formulara pre zmenu vlastnika clanku.
   * @param \Nette\Forms\Controls\SubmitButton $button Data formulara */
  public function zmenVlastnikaFormSubmitted(\Nette\Forms\Controls\SubmitButton $button) {
		$values = $button->getForm()->getValues(); 	//Nacitanie hodnot formulara
    try {
			$this->hlavne_menu->zmenVlastnika($values);
		} catch (Database\DriverException $e) {
			$button->addError($e->getMessage());
		}
  }
}