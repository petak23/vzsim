<?php
declare(strict_types=1);

namespace App\AdminModule\Components\Article\TitleArticle;

use App\Model;
use Nette\Application\UI\Form;
/**
 * Formular a jeho spracovanie pre zmenu sablony polozky.
 * Posledna zmena 14.04.2021
 * 
 * @author     Ing. Peter VOJTECH ml. <petak23@gmail.com>
 * @copyright  Copyright (c) 2012 - 2021 Ing. Peter VOJTECH ml.
 * @license
 * @link       http://petak23.echo-msz.eu
 * @version    1.0.2
 */
class ZmenSablonuFormFactory {
  /** @var Model\Hlavne_menu */
	private $hlavne_menu;
  
  /** @var Model\Hlavne_menu_template */
	private $hlavne_menu_template;
  
  /**
   * @param Model\Hlavne_menu $hlavne_menu
   * @param Model\Hlavne_menu_template $hlavne_menu_template */
  public function __construct(Model\Hlavne_menu $hlavne_menu, Model\Hlavne_menu_template $hlavne_menu_template) {
		$this->hlavne_menu = $hlavne_menu;
    $this->hlavne_menu_template = $hlavne_menu_template;
	}
  
  /**
   * Formular.
   * @param int $id Id polozky v hlavnom menu
   * @param int $id_hlavne_menu_template Id sucasnej sablony
   * @return Form */  
  public function create(int $id = 0, int $id_hlavne_menu_template = 0): Form  {
		$form = new Form();
		$form->addProtection();
    $form->addHidden("id", (string)$id);
    $form->addRadioList('id_hlavne_menu_template', 'Nová šablóna:', $this->hlavne_menu_template->formPairs())
         ->setDefaultValue($id_hlavne_menu_template);
    $form->addSubmit('uloz', 'Zmeň')
         ->setAttribute('class', 'btn btn-success')
         ->onClick[] = [$this, 'changeFormSubmitted'];
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
  public function changeFormSubmitted(\Nette\Forms\Controls\SubmitButton $button) {
		$values = $button->getForm()->getValues();
    try {
			$this->hlavne_menu->changeTemplate($values);
		} catch (Database\DriverException $e) {
			$button->addError($e->getMessage());
		}
  }
}