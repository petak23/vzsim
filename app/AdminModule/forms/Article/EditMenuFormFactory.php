<?php

declare(strict_types=1);

namespace App\AdminModule\Forms\Article;

use App\Model;
use Nette\Application\UI\Form;
use Nette\Security\User;

/**
 * Formular pre editaciu poloziek menu
 * Posledna zmena 14.04.2021
 * 
 * @author     Ing. Peter VOJTECH ml. <petak23@gmail.com>
 * @copyright  Copyright (c) 2012 - 2021 Ing. Peter VOJTECH ml.
 * @license
 * @link       http://petak23.echo-msz.eu
 * @version    1.1.6
 */
class EditMenuFormFactory {
  
  /** @var Nette\Security\User */
  protected $user;
  /** @var Model\Lang */
  public $lang;
  
  /** @var array Hodnoty id=>name pre formulare z tabulky hlavne_menu_template */
  public $hlavne_menu_templateForm;
  /** @var array Hodnoty id=>nazov pre formulare z tabulky hlavicka */
  protected $hlavickaForm;

  /**
   * @param User $user
   * @param Model\Lang $lang
   * @param Model\Hlavne_menu_template $hlavne_menu_template */
  public function __construct(User $user, Model\Lang $lang, 
                              Model\Hlavne_menu_template $hlavne_menu_template) {
    $this->user = $user;
    $this->lang = $lang;
    $this->hlavickaForm = $hlavicka->hlavickaForm();
    $this->hlavne_menu_templateForm = $hlavne_menu_template->formPairs();
  }
  
  /**
   * Edit hlavne menu form component factory.
   * @param int|null $uroven Uroven polozky v menu
   * @param string $uloz Text tlacitka uloz
   * @param string $name
   * @return Form */
  public function form(?int $uroven, string $uloz, string $name): Form  {
		$form = new Form();
		$form->addProtection();
    $form->addGroup();
    $form->addHidden("id");
    $form->addHidden("id_druh");
    $form->addHidden("id_user_main");
    $form->addHidden("spec_nazov");
    $form->addHidden("id_hlavne_menu_cast");
    $form->addHidden("uroven");
    $form->addHidden("id_nadradenej");
    if ($this->user->isInRole("admin")) {
//      $form->addText('nazov_ul_sub', 'Názov alternatívneho vzhľadu:', 20, 20);
      $form->addSelect('id_hlavne_menu_template', 'Alternatívny vzhľad šablóny:', $this->hlavne_menu_templateForm)
           ->setDefaultValue(1)
           ->addRule(Form::FILLED, 'Je nutné vybrať vzhľad.');
    }
    $form->addText('poradie', 'Poradie položky v časti:', 3, 3)
				 ->addRule(Form::RANGE, 'Poradie musí byť v rozsahu od %d do %d!', [1, 9999])
				 ->setRequired('Poradie musí byť zadané!');
    if ($name == "menu") {
      $form->addText('absolutna', 'Absolútna adresa:', 90, 50);
    }
		// Cast textov ----------------- 
		foreach ($this->lang->findAll() as $j) {
      $form->addGroup('Časť pre jazyk '.$j->nazov.':');
      $form->addHidden($j->skratka.'_id');
      $form->addText($j->skratka.'_menu_name', 'Názov v hlavnej ponuke:', 30, 100)
           ->addRule(Form::MIN_LENGTH, 'Názov pre jazyk "'.$j->nazov.'" musí mať spoň %d znaky!', 2)
           ->setRequired('Názov v hlavnej ponuke pre jazyk "'.$j->nazov.'" musí byť zadaný!');
      $form->addText($j->skratka.'_view_name', 'Zobrazený názov:', 90, 255)
           ->addRule(Form::MIN_LENGTH, 'Zobrazený názov pre jazyk "'.$j->nazov.'" musí mať spoň %d znaky!', 2)
           ->setOption('description', 'Tento názov sa zobrazuje ako nadpis článku. Môže byť odlišný od jeho názvu v hlavnej ponuke.')

           ->setRequired('Zobrazený názov pre jazyk "'.$j->nazov.'" musí byť zadaný!');
      $form->addText($j->skratka.'_h1part2', 'Druhá časť nadpisu v hlavnom menu:', 90, 100)
           ->setOption('description', 'Toto je len nepovinná doplnková časť názvu. Podtitul...');
		}
		// Cast textov koniec -----------------
    $form->addSubmit('uloz', $uloz)->setAttribute('class', 'btn btn-success');
    $form->addSubmit('cancel', 'Cancel')->setAttribute('class', 'btn btn-default')->setValidationScope([]);
		return $form;
	}
}

interface IEditMenuFormFactory {
  /** @return EditMenuFormFactory */
  function create();
}
