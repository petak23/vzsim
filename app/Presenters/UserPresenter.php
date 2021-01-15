<?php
namespace App\Presenters;

use App\Forms\User;
use Language_support;
use Latte;
use Nette\Mail\Message;
use Nette\Mail\SendmailMailer;
use Nette\Security\Passwords;
use Nette\Utils\Random;

/**
 * Prezenter pre prihlasenie, registraciu a aktiváciu uzivatela, obnovenie zabudnutého hesla a zresetovanie hesla.
 * Posledna zmena(last change): 06.10.2020
 *
 *	Modul: FRONT
 *
 * @author Ing. Peter VOJTECH ml. <petak23@gmail.com>
 * @copyright  Copyright (c) 2012 - 2020 Ing. Peter VOJTECH ml.
 * @license
 * @link       http://petak23.echo-msz.eu
 * @version 1.2.3
 */
class UserPresenter extends BasePresenter {
	
  // -- DB
  /** @var \App\Model\User_main @inject */
	public $user_main;
  /** @var \App\Model\User_profiles @inject */
	public $user_profiles;                       
  
  // -- Forms
  /** @var User\RegisterFormFactory @inject*/
	public $registerForm;
  /** @var User\ResetPasswordFormFactory @inject*/
	public $resetPasswordForm;
  /** @var User\ForgottenPasswordFormFactory @inject*/
	public $forgottenPasswordForm;
 
  /** @var mix */
  private $clen;

  /** @var array Nastavenie zobrazovania volitelnych poloziek */
  private $user_view_fields;
  
  protected function startup() {
    parent::startup();
    //Test prihlásenia
    if ($this->user->isLoggedIn()) { 
      $this->flashRedirect('Homepage:', $this->texty_presentera->translate('base_loged_in_bad'), 'danger');
    }
    $this->clen = $this->user_main->find(1);  //Odosielatel e-mailu
    $this->user_view_fields = $this->nastavenie['user_view_fields'];
	}

  /** Akcia pre registráciu nového uzivatela */
  public function actionRegistracia(): void {
    if ($this->udaje->findOneBy(['nazov'=>'registracia_enabled'])->text != 1) {
      $this->flashRedirect('Homepage:', 'registracia_not_enabled', 'danger');
    }
  }
  
  /**
   * Akcia pre aktivaciu registrovaneho uzivatela 
   * @param int $id Id uzivatela
   * @param string $new_password_key Kontrolny retazec pre aktivaciu */
  public function actionActivateUser(int $id, string $new_password_key): void {
    $user_main_data = $this->user_main->find($id); // Najdi uzivatela
    if ($new_password_key == $user_main_data->new_password_key){ //Aktivacne data su v poriadku
      try {
        $user_main_data->update(['id_user_roles'=>1, 'activated'=>1, 'new_password_key'=>NULL]); // Aktivacia uzivatela
				$this->user_profiles->uloz(['news'=>'A', 'news_key'=>Passwords::hash($user_main_data->email."news=>A")], $user_main_data->id_user_profiles);	// Zapnutie posielania noviniek pri aktivacii
        $this->flashRedirect('User:', $this->texty_presentera->translate('activate_ok'), 'success');
      } catch (Exception $e) {
        $this->flashMessage($this->texty_presentera->translate('activate_err1').$e->getMessage(), 'danger,n');
      }
    } else { $this->flashMessage($this->texty_presentera->translate('activate_err2'), 'danger'); } //Neuspesna aktivacia
    $this->redirect('Homepage:');
  }

  /** 
   * Akcia pre reset hesla pri zabudnutom hesle 
   * @param int $id Id uzivatela pre reset hesla
   * @param string $new_password_key Kontrolny retazec pre reset hesla */
  public function actionResetPassword(int $id, string $new_password_key): void {
    if (!isset($id) OR !isset($new_password_key)) {
      $this->flashRedirect('Homepage:', $this->texty_presentera->translate('reset_pass_err1'), 'danger');
    } else {
      $user_main_data = $this->user_main->find($id);
      if ($new_password_key == $user_main_data->new_password_key){ 
        $this->template->email = sprintf($this->texty_presentera->translate('reset_pass_email'), $user_main_data->email);
        $this["resetPasswordForm"]->setDefaults(["id"=>$id]); //Nastav vychodzie hodnoty
      } else { 
        $this->flashRedirect('Homepage:', $this->texty_presentera->translate('reset_pass_err'.($user_main_data->new_password_key == NULL ? '2' : '3')), 'danger');
      }
    }
  }
  
  /** 
   * Formular pre registraciu uzivatela.
	 * @return Nette\Application\UI\Form */
	protected function createComponentClenRegistraciaForm() {
    $form = $this->registerForm->create($this->user_view_fields, $this->link('User:forgotPassword'), $this->language);
    $form['uloz']->onClick[] = [$this, 'userRegisterFormSubmitted'];
    $form->getElementPrototype()->class = 'noajax';
		return $this->_vzhladForm($form);
	}

  /** 
   * Spracovanie reistracneho formulara
   * @param Nette\Application\UI\Form $button Data formulara */
  public function userRegisterFormSubmitted($button) {
		// Inicializacia
    $values = $button->getForm()->getValues(); 	//Nacitanie hodnot formulara
    $new_password_key = Random::generate(25);
    if (($uloz_user_profiles = $this->user_profiles->uloz(['pohl' => isset($values->pohl) ? $values->pohl : 'Z'])) !== FALSE) { //Ulozenie v poriadku
      $uloz_user_main = $this->user_main->uloz([ 
        'id_user_profiles' => $uloz_user_profiles['id'],
        'meno'      => $values->meno,
        'priezvisko'=> $values->priezvisko,
        'password'  => Passwords::hash($values->heslo),
        'email'     => $values->email,
        'activated' => 0,
        'created'   => StrFTime("%Y-%m-%d %H:%M:%S", Time()),
      ]);
   }
   if ($uloz_user_main !== FALSE) { //Ulozenie v poriadku
      $this->flashMessage($this->texty_presentera->translate('base_save_ok'), 'success');
      $templ = new Latte\Engine;
      $params = [
        "site_name" => $this->nazov_stranky,
        "nadpis"    => sprintf($this->texty_presentera->translate('email_activate_nadpis'),$this->nazov_stranky),
        "email_activate_txt" => $this->texty_presentera->translate('email_activate_txt'),
        "email_nefunkcny_odkaz" => $this->texty_presentera->translate('email_nefunkcny_odkaz'),
        "email_pozdrav" => $this->texty_presentera->translate('email_pozdrav'),
        "nazov"     => $this->texty_presentera->translate('register_aktivacia'),
        "odkaz" 		=> 'http://'.$this->nazov_stranky.$this->link("User:activateUser", $uloz_user_main['id'], $new_password_key),
      ];
      $mail = new Message;
      $mail->setFrom($this->nazov_stranky.' <'.$this->clen->email.'>')
           ->addTo($values->email)->setSubject($this->texty_presentera->translate('register_aktivacia'))
           ->setHtmlBody($templ->renderToString(__DIR__ . '/../templates/User/email_activate-html.latte', $params));
      try {
        $sendmail = new SendmailMailer;
        $sendmail->send($mail);
        $this->user_main->find($uloz_user_main['id'])->update(['new_password_key'=>$new_password_key]);
        $this->flashMessage($this->texty_presentera->translate('register_email_ok'), 'success');
      } catch (Exception $e) {
        $this->flashMessage($this->texty_presentera->translate('send_email_err').$e->getMessage(), 'danger,n');
      }
      $this->redirect('Homepage:');
    } else { $this->flashMessage($this->texty_presentera->translate('register_save_err'), 'danger');}	//Ulozenie sa nepodarilo
  }

  /**
	 * Forgot password user form component factory.
	 * @return Nette\Application\UI\Form
	 */
	protected function createComponentForgottenPasswordForm() {
    $form = $this->forgottenPasswordForm->create($this->language);
    $form['uloz']->onClick[] = [$this, 'forgotPasswordFormSubmitted'];
		return $this->_vzhladForm($form, "noajax");
	}

  /** 
   * Spracovanie formulara zabudnuteho hesla
   * @param Nette\Application\UI\Form $button Data formulara */
  public function forgotPasswordFormSubmitted($button) {
		//Inicializacia
    $values = $button->getForm()->getValues();                 //Nacitanie hodnot formulara
    $new_password_requested = StrFTime("%Y-%m-%d %H:%M:%S", Time());
    $new_password_key = Random::generate(25);
    if (($user_id = $this->user_main->findIdBy(['email'=>$values->email]))) { //Overenie existencie uzivatela
      $templ = new Latte\Engine;
      $params = [
        "site_name" => $this->nazov_stranky,
        "nadpis"    => sprintf($this->texty_presentera->translate('email_reset_nadpis'),$this->nazov_stranky),
        "email_reset_txt" => $this->texty_presentera->translate('email_reset_txt'),
        "email_nefunkcny_odkaz" => $this->texty_presentera->translate('email_nefunkcny_odkaz'),
        "email_pozdrav" => $this->texty_presentera->translate('email_pozdrav'),
        "nazov"     => $this->texty_presentera->translate('forgot_pass'),
        "odkaz" 		=> 'http://'.$this->nazov_stranky.$this->link("User:resetPassword", $user_id, $new_password_key),
      ];
      $mail = new Message;
      $mail->setFrom($this->nazov_stranky.' <'.$this->clen->email.'>')
           ->addTo($values->email)->setSubject($this->texty_presentera->translate('forgot_pass'))
           ->setHtmlBody($templ->renderToString(__DIR__ . '/../templates/User/forgot_password-html.latte', $params));
      try {
        $sendmail = new SendmailMailer;
        $sendmail->send($mail);
        $this->user_main->find($user_id)->update(['new_password_key'=>$new_password_key, 'new_password_requested'=>$new_password_requested]);
        $this->flashMessage($this->texty_presentera->translate('forgot_pass_email_ok'), 'success');
      } catch (Exception $e) {
        $this->flashMessage($this->texty_presentera->translate('send_email_err').$e->getMessage(), 'danger,n');
      }
      $this->redirect('Homepage:');
    } else {													//Taky uzivatel neexzistuje
      $this->flashMessage(sprintf($this->texty_presentera->translate('forgot_pass_user_err'),$values->email), 'danger');
    }
  }

  /**
	 * Password reset form component factory.
	 * @return Nette\Application\UI\Form */
	protected function createComponentResetPasswordForm() {
    $form = $this->resetPasswordForm->create($this->language);  
    $form['uloz']->onClick[] = function ($form) {
      if (!count($form->errors)){
        $this->flashRedirect('User:', $this->texty_presentera->translate('reset_pass_ok'), 'success');
      } else {
        $this->flashRedirect('Homepage:', $this->texty_presentera->translate('reset_pass_err').$form->errors[0], 'danger,n');
      }
		};
		return $this->_vzhladForm($form);
	}

  /** 
   * Akcia pre vypnutie posialania noviniek
   * @param int $id_user_main
   * @param string $news_key */
  public function actionNewsUnsubscribe($id_user_main, $news_key) {
    $user_for_unsubscribe = $this->user_main->find($id_user_main);
    if ($user_for_unsubscribe !== FALSE && $user_for_unsubscribe->user_profiles->news_key == $news_key) {
      $user_for_unsubscribe->user_profiles->update(['news'=>"N", 'news_key'=>NULL]);
      $this->flashMessage(sprintf($this->texty_presentera->translate('unsubscribe_news_ok'), $user_for_unsubscribe->email), 'success');
    } else {
      $this->flashMessage($this->texty_presentera->translate('unsubscribe_news_err'), 'danger');
    }
    $this->redirect('Homepage:');
  }
}