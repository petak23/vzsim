<?php
namespace App\FrontModule\Forms\User;

use Language_support;
use Nette\Application\UI\Form;
use Nette\Security;
/**
 * Formular pre vlozenie emailu v pripade zabudnuteho hesla
 * Posledna zmena 14.04.2021
 * 
 * @author     Ing. Peter VOJTECH ml. <petak23@gmail.com>
 * @copyright  Copyright (c) 2012 - 2021 Ing. Peter VOJTECH ml.
 * @license
 * @link       http://petak23.echo-msz.eu
 * @version    1.0.7
 */
class ForgottenPasswordFormFactory {
  /** @var Language_support\LanguageMain */
  private $texts;
  /** @var \App\Model\User_main */
  public $user_main;
  /** @var Nette\Security\User */
  public $user;


  public function __construct(Security\User $user, Language_support\LanguageMain $language_main, \App\Model\User_main $user_main) {
    $this->user = $user;
    $this->texts = $language_main;
    $this->user_main = $user_main;
  }
  
  /** @return Form */
  public function create(string $language)  {
    $this->texts->setLanguage($language);
    $form = new Form();
		$form->addProtection();
    $form->setTranslator($this->texts);
    $form->addText('email', 'Form_email')
         ->setType('email')
         ->setHtmlAttribute('size', 0)->setHtmlAttribute('maxlength', 100)
         ->setAttribute('placeholder', 'Form_email_ph')
				 ->addRule(Form::EMAIL, 'Form_email_ar')
				 ->setRequired('Form_email_sr');
		$form->addSubmit('uloz', 'ForgottenPasswordForm_uloz')
         ->setAttribute('class', 'btn btn-success');
    $form->onValidate[] = [$this, 'validateForm'];
		return $form;
	}
  
  /** Vlastná validácia pre formular
   * @param Nette\Application\UI\Form $button */
  public function validateForm($button) {
    $values = $button->getForm()->getValues();
    if ($button->isSubmitted()->name == 'uloz') {
      // Over, ci dany email existuje.
      if (!($this->user_main->testEmail($values->email))) {
        $button->addError(sprintf($this->texts->translate('forgot_pass_user_err'), $values->email));
      }
    } 
  }
}