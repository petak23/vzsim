<?php

namespace App\Forms\User;

use Language_support;
use Nette\Application\UI\Form;
use Nette\Security;

/**
 * Formular pre reset hesla
 * Posledna zmena 06.10.2020
 * 
 * @author     Ing. Peter VOJTECH ml. <petak23@gmail.com>
 * @copyright  Copyright (c) 2012 - 2020 Ing. Peter VOJTECH ml.
 * @license
 * @link       http://petak23.echo-msz.eu
 * @version    1.0.8
 */
class ResetPasswordFormFactory {
  /** @var User */
  private $user;
  /** @var Language_support\LanguageMain */
  private $texts;
  /** @var \App\Model\User_main */
  private $user_main;

  /** @param Security\User $user   */
  public function __construct(Security\User $user, Language_support\LanguageMain $language_main, \App\Model\User_main $user_main) {
    $this->user = $user;
    $this->texts = $language_main;
    $this->user_main = $user_main;
	}
  
  /**
   * Prihlasovaci formular
   * @return Nette\Application\UI\Form */
  public function create(string $language)  {
    $this->texts->setLanguage($language);
    $form = new Form();
		$form->addProtection();
    $form->setTranslator($this->texts);
//    $form->addProtection();
    $form->addHidden('id');
    $form->addPassword('new_heslo', 'ResetPasswordForm_new_heslo')
         ->setAttribute('autofocus', 'autofocus')
         ->setHtmlAttribute('size', 0)->setHtmlAttribute('maxlength', 100)
				 ->setRequired('ResetPasswordForm_new_heslo_sr');
		$form->addPassword('new_heslo2', 'ResetPasswordForm_new_heslo2')
         ->setHtmlAttribute('size', 0)->setHtmlAttribute('maxlength', 100)
         ->addRule(Form::EQUAL, 'ResetPasswordForm_new_heslo2_ar', $form['new_heslo'])
				 ->setRequired('ResetPasswordForm_new_heslo2_sr');
		$form->addSubmit('uloz', 'base_save');
    $form->onValidate[] = [$this, 'validateResetForm'];
    $form->onSuccess[] = [$this, 'userPasswordResetFormSubmitted'];
		return $form;
	}
  
  /** Vlastná validácia
   * @param Nette\Application\UI\Form $button */
  public function validateResetForm($button) {
    $values = $button->getForm()->getValues();
    if ($button->isSubmitted()->name == 'uloz') {
      if ($values->new_heslo != $values->new_heslo2) {
        $button->addError($this->texts->translate('reset_pass_hesla_err'));
      }
    } 
  }
  
  /** 
   * Overenie po odoslani
   * @param Nette\Forms\Controls\SubmitButton $button Data formulara */
  public function userPasswordResetFormSubmitted($button) {
    $values = $button->getForm()->getValues(); //Nacitanie hodnot formulara
		if ($values->new_heslo != $values->new_heslo2) {
      $button->addError($this->texts->translate('reset_pass_hesla_err'));
		} else {
      //Vygeneruj kluc pre zmenu hesla
      $new_password = Security\Passwords::hash($values->new_heslo);
      unset($values->new_heslo, $values->new_heslo2); //Len pre istotu
      try {
        $this->user_main->find($values->id)->update(['password'=>$new_password, 'new_password_key'=>NULL, 'new_password_requested'=>NULL]);
      } catch (Exception $e) {
        $button->addError($e->getMessage());
      }
    }
	}
}
