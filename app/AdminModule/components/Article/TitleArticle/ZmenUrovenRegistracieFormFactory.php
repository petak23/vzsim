<?php
declare(strict_types=1);

namespace App\AdminModule\Components\Article\TitleArticle;

use App\Model;
use Nette\Application\UI\Form;
use Nette\Security\User;
/**
 * Formular a jeho spracovanie pre zmenu urovne registracie polozky.
 * Posledna zmena 14.04.2021
 * 
 * @author     Ing. Peter VOJTECH ml. <petak23@gmail.com>
 * @copyright  Copyright (c) 2012 - 2021 Ing. Peter VOJTECH ml.
 * @license
 * @link       http://petak23.echo-msz.eu
 * @version    1.0.5
 */
class ZmenUrovenRegistracieFormFactory {
  /** @var Model\Hlavne_menu */
	private $hlavne_menu;
  
  /** @var array */
	private $user_roles;
  
  /**
   * @param Model\Hlavne_menu $hlavne_menu
   * @param Model\User_roles $user_roles
   * @param User $user */
  public function __construct(Model\Hlavne_menu $hlavne_menu, Model\User_roles $user_roles, User $user) {
		$this->hlavne_menu = $hlavne_menu;
    $this->user_roles = $user_roles->urovneReg($user->getIdentity()->id_user_roles);
	}
  
  /**
   * Formular pre zmenu urovne registracie polozky.
   * @param int $id Id polozky v hlavnom menu
   * @param int $id_user_roles Sucasna uroven registracie polozky
   * @return Nette\Application\UI\Form */  
  public function create(int $id, int $id_user_roles): Form  {
		$form = new Form();
		$form->addProtection();
    $form->addHidden("id", (string)$id);
    $form->addRadioList('id_user_roles', 'Nová úroveň:', $this->user_roles)
         ->setDefaultValue($id_user_roles);
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
   * Spracovanie formulara pre zmenu vlastnika clanku.
   * @param Nette\Forms\Controls\SubmitButton $button Data formulara */
  public function zmenUrovenRegistracieFormSubmitted(\Nette\Forms\Controls\SubmitButton $button) {
		$values = $button->getForm()->getValues(); 	//Nacitanie hodnot formulara
    try {
			$this->hlavne_menu->zmenUrovenRegistracie($values);
		} catch (Database\DriverException $e) {
			$button->addError($e->getMessage());
		}
  }
}