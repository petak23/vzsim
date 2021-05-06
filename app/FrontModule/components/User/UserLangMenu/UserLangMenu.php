<?php
namespace App\FrontModule\Components\User\UserLangMenu;

use App\FrontModule\Forms;
use App\Model;
use Language_support;
use Nette\Application\UI\Control;
use Nette\Security\User;
use Nette\Utils\Html;

/**
 * Plugin pre zobrazenie ponuky o užívateľovi a jazykoch
 * Posledna zmena(last change): 15.04.2021
 *
 * @author Ing. Peter VOJTECH ml. <petak23@gmail.com>
 * @copyright  Copyright (c) 2013 - 2021 Ing. Peter VOJTECH ml.
 * @license
 * @link       http://petak23.echo-msz.eu
 * @version 1.2.2
 */
class UserLangMenuControl extends Control {
  /** @var Language_support\LanguageMain Texty pre dany jazyk */
  public $texty;
	
  /** @var array Lokalne nastavenia */
	private $nastavenie = [];
  /** @var string Adresar k uzivatelom */
  private $dir_to_user = "";
  /** @var boolean Viditelnost avatara */
  private $avatar_view = FALSE;
  /** @var boolean Povolenie registracie */
  private $registracia_enabled = FALSE;

  /** @var Model\Lang */
  public $lang;
  /** @var Model\User_main */
  public $user_main;

  /** @var User */
  protected $user;
  /** @var string */
  protected $storeReq;

  // -- Forms
  /** @var Forms\User\SignInFormFactory */
  public $signInForm;


  /**
   * @param array $nastavenie Nastavenie priamo cez servises.neon
   * @param string $dir_to_user Nastavenie priamo cez servises.neon
   * @param bool $avatar_view Nastavenie priamo cez servises.neon
   * @param Language_support\LanguageMain $language
   * @param Model\Lang $lang
   * @param Model\User_main $user_main
   * @param Model\Udaje $udaje
   * @param User $user */
  public function __construct(array $nastavenie, 
                              string $dir_to_user,
                              bool $avatar_view,
                              Language_support\LanguageMain $language, 
                              Model\Lang $lang,
                              Model\User_main $user_main,
                              Model\Udaje $udaje,
                              User $user,
                              Forms\User\SignInFormFactory $signInForm
                              ) {
    $this->lang = $lang;
    $this->user = $user;
    $this->texty = $language; 
    $this->user_main = $user_main;
    $this->nastavenie = $nastavenie;
    $this->dir_to_user = $dir_to_user;
    $this->avatar_view = (boolean)$avatar_view;
    $this->registracia_enabled = (boolean)$udaje->getValByName('registracia_enabled');
    $this->signInForm = $signInForm;
  }
  
  /** Nastavenie aktualneho jazyka
   * @param string|int $language Skratka jazyka alebo jeho id */
  public function setLanguage($language) {
    // Nacitanie základných textov z neon suboru podla jazyka
    $this->texty->setLanguage($language)->appendLanguageFile(__DIR__ . '/lang_'. $this->texty->jazyk.'.neon');
    return $this;
  }
  
  /** 
   * Panel neprihlaseneho uzivatela
   * @param string $store_request
   * @return \App\Components\User\MenuItem  */
  private function _panelNeprihlaseny(string $store_request) {
    $vlnh = $this->nastavenie['view_log_in_link_in_header']; //Skratenie zapisu
    $menu_user = [];
    $menu_user[] = new MenuItem([
        'odkaz'=>[0=>'User:', 1=>['meno'=>'backlink', 'obsah'=>$store_request]], 
        'class'=>'log-in'.(($vlnh) ? "" : " prazdny fa fa-lock"),
        'title'=> $this->texty->translate('log_in'),
        'nazov'=>($vlnh & 1) ? $this->texty->translate('log_in') : ($vlnh ? NULL : ""),
        'ikonka'=>($vlnh & 2) ? "fas fa-sign-in-alt" : NULL,
        'class'=>'btn-success noajax',
        'data'=>['ajax'=>'false', 'toggle'=>'modal', 'target'=>'#modalSignInForm'],
                        ]);
    if ($this->registracia_enabled) {
      $menu_user[] = new MenuItem([
          'odkaz'=>'User:registracia', 
          'nazov'=> $this->texty->translate('register'),
          'ikonka'=>($vlnh & 2) ? "fas fa-user-plus" : NULL,
          'class'=>'btn-info',
                          ]);
    }
    return $menu_user;
  }

  /** 
   * Panel prihlaseneho uzivatela
   * @param string $baseUrl
   * @return \App\Components\User\MenuItem */
  private function _panelPrihlaseny(string $baseUrl) {
    $menu_user = [];
    $udata = $this->user_main->find($this->user->getIdentity()->getId());
    $avatar = $this->dir_to_user.$udata->id."/".$udata->user_profiles->avatar;
    $menu_user[] = new MenuItem([
          'odkaz'=>'UserLog:', 
          'nazov'=>$udata->meno.' '.$udata->priezvisko,
          'class'=>'btn-info noajax',
          'image'=> $this->nastavenie['view_avatar'] ? (($udata->user_profiles->avatar && is_file($avatar)) ? $avatar : "ikonky/64/figurky_64.png") : NULL,
        ]);
    if ($this->user->isAllowed('Admin:Homepage', 'default')) {
      $menu_user[] = new MenuItem([
        'odkaz'=> ':Admin:Homepage:',
        'title'=> $this->texty->translate('admin_link_name'),
        'ikonka'=> ($this->nastavenie['admin_link'] & 1) ? 'fas fa-pencil-alt' : '',
        'nazov'=> ($this->nastavenie['admin_link'] & 2) ? $this->texty->translate('admin_link_name') : '',
        'class'=> 'btn-info noajax',
        'data'=> ['ajax'=>'false'],
      ]);
    }
    if ($this->user->isInRole('admin')) {
      $hl_m_db_info = $this->lang->getDBInfo();
      $menu_user[] = new MenuItem([
        'abs_link'=>$baseUrl."/www/adminer/?server=".$hl_m_db_info['host']."&db=".$hl_m_db_info['dbname'], 
        'title'=>'Adminer',
        'target'=>'_blank',
        'ikonka'  => 'fas fa-database',
        'class'=>'btn-primary noajax',
        'data'=>['ajax' =>'false'],
                          ]);
    }
    $menu_user[] = new MenuItem([
        'odkaz'=> 'Homepage:signOut',
        'ikonka'=>"fas fa-sign-out-alt",
        'nazov'=> $this->texty->translate('log_out'),
        'class'=>'btn-warning noajax',
        'data'=>['ajax'=>'false'],
                        ]);
    return $menu_user;
  }
  
  /**
   * @param string $sr */
  public function setStoreRequest($sr) {
    $this->storeReq = $sr;
  }

  /** Vykreslenie komponenty */
  public function render() {
		//Inicializacia
		$baseUrl = $this->template->baseUrl;
    $this->nastavenie['view_avatar'] = $this->nastavenie['view_avatar'] && $this->avatar_view;
    
		if ($this->user->isLoggedIn()) { //Panel prihlaseneho uzivatela
      $menu_user = $this->_panelPrihlaseny($baseUrl);
		} elseif ($this->nastavenie['view_log_in_link_in_header'] >= 0) { 
      //Panel neprihlaseneho uzivatela
      $menu_user = $this->_panelNeprihlaseny($this->storeReq);
		}
		$lang_temp = $this->lang->findBy(['prijaty'=>1]);
		if ($lang_temp !== FALSE && count($lang_temp)>1) {
			foreach($lang_temp as $lm) {
				$menu_user[] = new MenuItem([
						'odkaz'=>['setLang!', $lm->skratka],
						'title'=>$lm->nazov.", ".$lm->nazov_en,
						'class'=>($lm->skratka == $this->texty->jazyk) ? "lang actual" : "lang",
            'nazov'=>Html::el('img')->src($baseUrl.'/www/ikonky/flags/'.$lm->skratka.'.png')->alt('Flag of '.$lm->skratka)
				]);
			}
		}
		$this->template->menu_user = isset($menu_user) ? $menu_user : [];
    $this->template->setTranslator($this->texty);
		$this->template->setFile(__DIR__ . '/UserLangMenu.latte');
		$this->template->render();
	}

  /** 
   * Formular pre prihlasenie uzivatela.
   * @return Nette\Application\UI\Form */
  protected function createComponentSignInForm() {
    $form = $this->signInForm->create($this->texty->jazyk);
    $servise = $this;
    $form['login']->onClick[] = function ($form) use ($servise) {
      $sp = $servise->presenter;
      $sp->restoreRequest($sp->backlink);
      if (!count($form->errors)) {
        $sp->flashMessage($servise->texty->translate('base_login_ok'), 'success');
        $sp->redirect('Homepage:');
      } else {
        $sp->flashMessage($servise->texty->translate('base_login_error'), 'danger');
      }
    };
    $form['forgottenPassword']->onClick[] = function () {
      $this->presenter->redirect('User:forgottenPassword');
    };
    return $this->presenter->_vzhladForm($form);
  }
		
}

class MenuItem {
  public $odkaz;
  public $abs_link;
  public $nazov = "";
  public $class = "";
  public $title = "";
  public $target = "";
  public $ikonka;
  public $image;
  public $data = ['name'=>'', 'value'=>''];
  
  function __construct(array $params) {
    foreach ($params as $k => $v) { $this->$k = $v;}
    $this->title = $this->title == "" ? $this->nazov : $this->title;
  }
}

interface IUserLangMenuControl {
  /** @return UserLangMenuControl */
  function create();
}