<?php

namespace App\Presenters;

use Nette\Application\UI\Presenter;
use Language_support;
use App\Model;

/**
 * Zakladny presenter pre vsetky presentery vo FRONT module
 * 
 * Posledna zmena(last change): 12.01.2021
 *
 *	Modul: FRONT
 *
 * @author Ing. Peter VOJTECH ml. <petak23@gmail.com>
 * @copyright Copyright (c) 2021 - 2021 Ing. Peter VOJTECH ml.
 * @license
 * @link      http://petak23.echo-msz.eu
 * @version 1.0.0
 */
abstract class BasePresenter extends Presenter {

  /** @var string Skratka aktualneho jazyka 
   * @persistent */
  public $language = 'sk';
  /** @persistent */
  public $backlink = '';
  /** @var int Uroven registracie uzivatela  */
  public $id_reg;
  /** @var array nastavenie z config-u */
  public $nastavenie;
  
  /** @var Language_support\LanguageMain @inject */
  public $texty_presentera;

  // -- DB
  /** @var Model\Lang @inject*/
	public $lang;
  /** @var Model\User_roles @inject */
	public $user_roles;
  /** @var Model\User_main @inject */
	public $user_main;
  /** @var Model\Verzie @inject */
  public $verzie;
  
  /** @var array */
  protected $main_menu = [
//    ["nazov"=>"Odhlás sa", "odkaz"=>"Homepage:signOut"],
  ];

  // -- Komponenty
  /** @var \App\Components\User\UserLangMenu\IUserLangMenuControl @inject */
  public $userLangMenuControlFactory;

  // -- Forms
  /** @var \App\Forms\User\SignInFormFactory @inject*/
  public $signInForm;

  public function __construct(array $parameters = []) {
    // Nastavenie z config-u
    $this->nastavenie = $parameters;
  }
  
  protected function startup() {
    parent::startup();
    // Kontrola prihlasenia a nacitania urovne registracie
    $this->id_reg = ($this->user->isLoggedIn()) ? ($this->user->getIdentity()->id_user_roles === NULL ? 0 : $this->user->getIdentity()->id_user_roles) : 0;
    
    // Nastav jazyk
    if($this->lang->getLangS($this->params['language']) == $this->params['language']) {
      $this->language = $this->params['language'];
    }

    //Nastavenie textov podla jazyka 
    $this->texty_presentera->setLanguage($this->language);

    // Kontrola ACL
    if (!$this->user->isAllowed($this->name, $this->action)) {
      $this->flashRedirect('Homepage:', sprintf($this->texty_presentera->translate('base_nie_je_opravnenie'), $this->action), 'danger');
    }
  }
  
  /** 
   * Naplnenie spolocnych udajov pre sablony */
  public function beforeRender() {
    $this->template->nastavenie = $this->nastavenie;
    $this->template->setTranslator($this->texty_presentera);
    $this->template->fa = [
      'success' => 'far fa-check-circle',
      'warning' => 'fas fa-exclamation-triangle',
      'info'    => 'fas fa-info-circle',
      'danger'  => 'fas fa-exclamation-circle',
    ];
    //$this->main_menu = array_map($this->main_menu, ["nazov"=>"Adminer", "odkaz"=>])
        
    $this->template->main_menu = $this->main_menu;
  }

  /** 
   * Akcia pre odhlasenie - spolocna pre vsetky presentery */
	public function actionSignOut(): void {
		$this->getUser()->logout(TRUE);
    $this->id_reg = 0;    
		$this->flashRedirect('Homepage:', $this->texty_presentera->translate('base_log_out_mess'), 'success');
	}
  
  /**
   * Vytvorenie komponenty pre menu uzivatela a zaroven panel jazykov
   * @return \App\FrontModule\Components\User\UserLangMenu */
  public function createComponentUserLangMenu() {
    $ulm = $this->userLangMenuControlFactory->create();
    $ulm->setLanguage($this->language)->setStoreRequest($this->storeRequest());
    return $ulm;
  }

  /** 
   * Formular pre prihlasenie uzivatela.
   * @return Nette\Application\UI\Form */
  protected function createComponentSignInForm() {
    $form = $this->signInForm->create($this->language);
    $servise = $this;
    $form['login']->onClick[] = function ($form) use ($servise) {
      $er_txt = $servise->texty_presentera->translate('base_login_error');
      $servise->restoreRequest($servise->backlink);
      $servise->flashOut(!count($form->errors), 'Homepage:', 
                         $servise->texty_presentera->translate('base_login_ok'), 
                         sprintf($er_txt, isset($form->errors[0]) ? $servise->texty_presentera->translate('base_Log_In_Error_'.$form->errors[0]) : 'Ch'));
    };
    $form['forgottenPassword']->onClick[] = function () {
      $this->redirect('User:forgottenPassword');
    };
    $renderer = $form->getRenderer();
    $renderer->wrappers['controls']['container'] = 'div class=sign-in-form';
    $renderer->wrappers['pair']['container'] = 'div class="form-group row justify-content-center"';
    $renderer->wrappers['pair']['.error'] = 'has-danger';
    $renderer->wrappers['control']['container'] = 'div class="col-12"'; // col-sm-6
    $renderer->wrappers['label']['container'] = 'div class="d-none"';//'div class="col-sm-3 col-form-label"';
    $renderer->wrappers['control']['description'] = 'span class=form-text';
    $renderer->wrappers['control']['errorcontainer'] = 'span class=form-control-feedback';
    $renderer->wrappers['control']['.error'] = 'is-invalid';

    foreach ($form->getControls() as $control) {
      $type = $control->getOption('type');
      if (in_array($type, ['text', 'textarea', 'select'], true)) {
        $control->getControlPrototype()->addClass('form-control');

      } elseif (in_array($type, ['checkbox', 'radio'], true)) {
        if ($control instanceof \Nette\Forms\Controls\Checkbox) {
          $control->getLabelPrototype()->addClass('form-check-label');
        } else {
          $control->getItemLabelPrototype()->addClass('form-check-label');
        }
        $control->getControlPrototype()->addClass('form-check-input');
        $control->getSeparatorPrototype()->setName('div')->addClass('form-check');
      }
    }
    return $form;
  }
  
  /** Funkcia pre zjednodusenie vypisu flash spravy a presmerovania
   * @param array|string $redirect Adresa presmerovania
   * @param string $text Text pre vypis hlasenia
   * @param string $druh Druh hlasenia */
  public function flashRedirect($redirect, string $text = "", string $druh = "info") {
		$this->flashMessage($text, $druh);
    if (is_array($redirect)) {
      if (count($redirect) > 1) {
        if (!$this->isAjax()) {
          $this->redirect($redirect[0], $redirect[1]);
        } else {
          $this->redrawControl();
        }
      } elseif (count($redirect) == 1) { $this->redirect($redirect[0]);}
    } else { 
      if (!$this->isAjax()) { 
        $this->redirect($redirect); 
      } else {
        $this->redrawControl();
      }
    }
	}
  /**
   * Funkcia pre zjednodusenie vypisu flash spravy a presmerovania aj pre chybovy stav
   * @param bool $ok Podmienka
   * @param array|string $redirect Adresa presmerovania
   * @param string $textOk Text pre vypis hlasenia ak je podmienka splnena
   * @param string $textEr Text pre vypis hlasenia ak NIE je podmienka splnena  */
  public function flashOut(bool $ok, $redirect, string $textOk = "", string $textEr = "") {
    if ($ok) {
      $this->flashRedirect($redirect, $textOk, "success");
    } else {
      $this->flashMessage($textEr, 'danger');
    }
  }
}