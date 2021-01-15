<?php

namespace App\Forms\User;

use Language_support;
use Nette\Application\UI\Form;
use Nette\Security\User;

/**
 * Registracny formular
 * Posledna zmena 06.10.2020
 * 
 * @author     Ing. Peter VOJTECH ml. <petak23@gmail.com>
 * @copyright  Copyright (c) 2012 - 2020 Ing. Peter VOJTECH ml.
 * @license
 * @link       http://petak23.echo-msz.eu
 * @version    1.0.9
 */
class RegisterFormFactory {
  /** @var Security\User */
  protected $user;
  /** @var Language_support\LanguageMain */
  private $texts;
  /** @var \App\Model\User_main */
  private $user_main;
  /** @var string */
  private $link_forgot;  

  /**
   * @param User $user
   * @param Language_support\LanguageMain $language_main
   * @param \App\Model\User_main $user_main */
  public function __construct(User $user, Language_support\LanguageMain $language_main, \App\Model\User_main $user_main) {
    $this->user = $user;
    $this->texts = $language_main;
    $this->user_main = $user_main;
	}
  
  /** Prihlasovaci formular
   * @return Nette\Application\UI\Form */
  public function create($user_view_fields, $link_forgot, string $language)  {
    $this->link_forgot = $link_forgot;
    $this->texts->setLanguage($language);
    $form = new Form();
		$form->addProtection();
    $form->setTranslator($this->texts);
    $form->addText('meno', 'RegistraciaForm_meno')
         ->setHtmlAttribute('size', 0)->setHtmlAttribute('maxlength', 50)
				 ->addRule(Form::MIN_LENGTH, 'RegistraciaForm_meno_ar', 2)
         ->setAttribute('autofocus', 'autofocus')
				 ->setRequired('RegistraciaForm_meno_sr');
    $form->addText('priezvisko', 'RegistraciaForm_priezvisko')
         ->setHtmlAttribute('size', 0)->setHtmlAttribute('maxlength', 50)
				 ->addRule(Form::MIN_LENGTH, 'RegistraciaForm_priezvisko_ar', 3)
				 ->setRequired('RegistraciaForm_priezvisko_sr');
    $form->addText('email', 'Form_email')
         ->setType('email')
         ->setHtmlAttribute('size', 0)->setHtmlAttribute('maxlength', 100)
				 ->addRule(Form::EMAIL, 'Form_email_ar')
				 ->setRequired('Form_email_sr');
    $form->addPassword('heslo', 'RegistraciaForm_heslo')
         ->setHtmlAttribute('size', 0)->setHtmlAttribute('maxlength', 100)
				 ->addRule(Form::MIN_LENGTH, 'RegistraciaForm_heslo_ar', 5)
				 ->setRequired('RegistraciaForm_heslo_sr');
    $form->addPassword('heslo2', 'RegistraciaForm_heslo2')
         ->setHtmlAttribute('size', 0)->setHtmlAttribute('maxlength', 100)
         ->addRule(Form::EQUAL, 'RegistraciaForm_heslo2_ar', $form['heslo'])
				 ->setRequired('RegistraciaForm_heslo2_sr');
    if ($user_view_fields["pohl"]) {
      $form->addSelect('pohl', 'RegistraciaForm_pohl', ['M'=>'RegistraciaForm_m','Z'=>'RegistraciaForm_z']);
    }
		$form->addReCaptcha('recaptcha', $label = 'Captcha')
				 ->setRequired('Táto položka je požadovaná!')
				 ->setMessage('Are you a bot?');
				 
    $form->onValidate[] = [$this, 'validateRegisterForm'];
		$form->addSubmit('uloz', 'RegistraciaForm_uloz')
         ->setAttribute('class', 'btn btn-success');
		return $form;
	}
  
  /** Vlastná validácia pre RegisterForm
   * @param Nette\Application\UI\Form $button */
  public function validateRegisterForm($button) {
    $values = $button->getForm()->getValues();
    if ($button->isSubmitted()->name == 'uloz') {
      // Over, ci dany email uz existuje.
      if ($this->user_main->testEmail($values->email)) {
        $button->addError(sprintf($this->texts->translate('registracia_email_duble2'), $values->email, $this->link_forgot));
      }
    } 
  }
}
