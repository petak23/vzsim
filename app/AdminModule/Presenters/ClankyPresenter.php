<?php
namespace App\AdminModule\Presenters;

use App\AdminModule\Components;
use Nette\Application\UI\Form;
use PeterVojtech;

/**
 * Prezenter pre spravu clankov.
 * 
 * Posledna zmena(last change): 14.04.2021
 *
 *	Modul: ADMIN
 *
 * @author Ing. Peter VOJTECH ml <petak23@gmail.com>
 * @copyright Copyright (c) 2012 - 2021 Ing. Peter VOJTECH ml.
 * @license
 * @link http://petak23.echo-msz.eu
 * @version 1.3.6
 */

class ClankyPresenter extends ArticlePresenter {

  // -- Komponenty
  /** @var Components\Clanky\IZobrazClanokAControl @inject */
  public $zobrazClanokControlFactory;
  /** @var Components\Clanky\PrilohyClanok\IPrilohyClanokAControl @inject */
  public $prilohyClanokControlFactory;
  /** @var Components\Clanky\Komponenty\IKomponentyControl @inject */
  public $komponentyControlFactory;

	/** @var string */
  protected $nadpis_h2 = "";
	
  /** @var mixed */
	public $priloha;
  
  /** @persistent */
  public $tabs_clanky = 'prilohy-tab';

  /** Vychodzie nastavenia */
  protected function startup() {
    parent::startup();
    $this->template->jazyky = $this->lang->findAll();
  }
  
  /** Render pre defaultnu akciu */
	public function renderDefault() {
    parent::renderDefault();
		$this->template->prilohy = $this->dokumenty->getPrilohy($this->zobraz_clanok->id_hlavne_menu);
    //Zisti, ci su k clanku priradene komponenty
    $this->template->komponenty = $this->clanok_komponenty->getKomponenty($this->zobraz_clanok->id_hlavne_menu, $this->nastavenie["komponenty"]);
    //Kontrola jedinecnych komponent. Ak uz su priradene tak sa vypustia
    $this->template->zoznam_komponent = $this->clanok_komponenty->testJedinecnosti($this->nastavenie["komponenty"], $this->zobraz_clanok->id_hlavne_menu);
    $this->template->tabs = isset($this->params["tab"]) ? $this->params["tab"] : "prilohy-tab";
	}

  /** 
   * Akcia pre 1. krok pridania clanku - udaje pre hl. menu.
   * @param int $id - id nadradenej polozky
   * @param int $uroven - uroven menu */
  public function actionAdd($id, $uroven) {
		$this->menuformuloz = ["text"=>"Ulož základ a pokračuj na texty >>","redirect"=>"Clanky:add2"];
    parent::actionAdd($id, $uroven);
	}
	
  /** 
   * Akcia pre 1. krok editovania clanku - udaje pre hl. menu.
   * @param int $id - id editovanej polozky */
  public function actionEdit($id) {
    $this->menuformuloz = ["text"=>"Ulož","redirect"=>"Clanky:default"];
    parent::actionEdit($id);
	}
  
	/** 
   * Akcia pre 2. krok pridania clanku - udaje pre clanok.
   * @param int $id - id pridavanej polozky v hl. menu */
	public function actionAdd2($id) {
    $this->nadpis_h2 = 'Pridanie textov k článku: ';
    //Najdi pozadovany clanok
    if (($this->zobraz_clanok = $this->hlavne_menu_lang->getOneArticleId($id, $this->language_id, $this->id_reg)) === FALSE) { 
      $this->setView('notFound');
    } else {
      $vychodzie = [];
      foreach ($this->jaz as $j) {
        $la = $j->skratka."_";
        $vychodzie = array_merge($vychodzie, [//Nastav vychodzie hodnoty
            $la.'id_lang'   => $j->id, 
            $la.'text'    	=> NULL,
            $la.'anotacia'	=> NULL,	
          ]);
      }
      $this["clankyEditForm"]->setDefaults($vychodzie);
      $this->setView("krok2");
    }
	}
	
  /** 
   * Akcia pre 2. krok editovania clanku - udaje pre clanok.
   * @param int $id - id editovaneho clanku v hl. menu */
	public function actionEdit2($id) {
    $this->nadpis_h2 = 'Editácia textov k článku: ';
    //Najdi pozadovany clanok
    if (($this->zobraz_clanok = $this->hlavne_menu_lang->getOneArticleId($id, $this->language_id, $this->id_reg)) === FALSE) { 
      $this->setView('notFound');
    } else {
      $vychodzie = [];
      foreach ($this->jaz as $j) {
        $pom = $this->hlavne_menu_lang->findOneBy(["id_lang"=>$j->id, "id_hlavne_menu"=>$id]);
        $la = $j->skratka."_";
        if ($pom === FALSE OR $pom->id_clanok_lang == NULL) { //Polozku som nenasiel a tak ju vytvorim
          $vychodzie = array_merge($vychodzie, [//Nastav vychodzie hodnoty
            $la.'id_lang'   => $j->id, 
            $la.'text'    	=> NULL,
            $la.'anotacia'	=> NULL,	
          ]);
        } else {
          $vychodzie = array_merge($vychodzie, [ //Nastav vychodzie hodnoty
            $la.'id'        => $pom->clanok_lang->id,
            $la.'id_lang'   => $pom->clanok_lang->id_lang, 
            $la.'text'    	=> $pom->clanok_lang->text,
            $la.'anotacia'	=> $pom->clanok_lang->anotacia,	
          ]);
        }  
      }
      $this["clankyEditForm"]->setDefaults($vychodzie);
      $this->setView("krok2");
    }
	}
  
  /** Render pre 2. krok editacie clanku. */
  public function renderKrok2() {
		$this->template->h2 = $this->nadpis_h2.$this->zobraz_clanok->view_name;
	}
	
	/** 
   * Formular pre editaciu clanku.
	 * @return Nette\Application\UI\Form */
	protected function createComponentClankyEditForm() {
		$form = new Form();
		$form->addProtection();
    $form->addGroup();
    if ($this->nastavenie['send_e_mail_news']) {
      $form->addCheckbox('posli_news', ' Posielatie NEWS')
            ->setOption('description', 'Zaškrtnutím sa pri uložení pošle informačný e-mail všetkým užívateľom, ktorý majú oprávnenie na čítanie článku(min. úroveň registrácie).');
    }
		$form->addGroup();
		foreach ($this->jaz as $j) {
      $form->addHidden($j->skratka.'_id');
			$form->addHidden($j->skratka.'_id_lang');
      if ($this->nastavenie['clanky']['zobraz_anotaciu']) {
        $form->addText($j->skratka.'_anotacia', 'Anotácia článku pre jazyk '.$j->nazov.':', 0, 255);
      }
			$form->addTextArea($j->skratka.'_text', 'Text článku pre jazyk '.$j->nazov.':')
            ->setAttribute('cols', 0)
            ->setAttribute('rows', 20)
            ->getControlPrototype()->class("texyla");
    }
		$form->addGroup();
		$form->addSubmit('uloz', 'Ulož článok')->setAttribute('class', 'btn btn-success');
		$form->onSuccess[] = [$this,'clankyEditFormSubmitted'];
		$form = $this->_vzhladForm($form);
    $renderer = $form->getRenderer();
    $renderer->wrappers['pair']['.odd'] = 'r1';
    $renderer->wrappers['control']['container'] = 'div class="col-sm-12 control-field"';
    $renderer->wrappers['label']['container'] = 'div class="col-sm-12 control-label control-label-clanky"';
    return $form;
	}

  /** 
   * Spracovanie formulara pre editaciu clanku.
   * @param Nette\Application\UI\Form $form Hodnoty formulara */
	public function clankyEditFormSubmitted($form) {
		$values = $form->getValues(TRUE);             //Nacitanie hodnot formulara
		//Inicializacia
		$posli_news = isset($values["posli_news"]) ? $values["posli_news"] : FALSE;
    unset($values["posli_news"]);
		if ($this->hlavne_menu_lang->ulozTextClanku($values, $this->action, $this->zobraz_clanok->id_hlavne_menu)) { //Ulozenie v poriadku
			if ($this->nastavenie['send_e_mail_news'] && $posli_news) { $this->_sendClankyEmail(); }
      $this->flashRedirect(['Clanky:default', $this->zobraz_clanok->id_hlavne_menu], 'Váš článok bol úspešne uložený!', 'success');
		} else {													//Ulozenie sa nepodarilo
			$this->flashMessage('Došlo k chybe a článok sa neuložil. Skúste neskôr znovu...', 'danger');
		}
	}
  
  /** Odoslanie info e-mailu */
	public function _sendClankyEmail() {
    $params = [ "site_name" => $this->nazov_stranky,
                "nazov" 		=> $this->zobraz_clanok->view_name,
                "odkaz" 		=> $this->link(":Front:Clanky:default", $this->zobraz_clanok->id_hlavne_menu),
                "datum_platnosti" => $this->zobraz_clanok->hlavne_menu->datum_platnosti,
              ];
    $send = new PeterVojtech\Email\EmailControl(__DIR__.'/templates/Clanky/email_clanky_html.latte', $this->user_profiles, 1, $this->zobraz_clanok->hlavne_menu->id_user_roles);
    try {
      $this->flashMessage('E-mail bol odoslany v poriadku na emaily: '.$send->send($params, 'Nový článok na stránke '.$this->nazov_stranky), 'success');
    } catch (Exception $e) {
      $this->flashMessage($e->getMessage(), 'danger');
    }
	}
  
  /** 
   * Komponenta pre ukazanie obsahu clanku.
   * @return \App\AdminModule\Components\Clanky\ZobrazClanokControl */
	public function createComponentZobrazClanok() {
    $zobrazClanok = $this->zobrazClanokControlFactory->create();
    $zobrazClanok->setZobraz($this->zobraz_clanok->id_hlavne_menu);
    return $zobrazClanok;
    
  }
  
  /** 
   * Komponenta pre ukazanie priloh clanku.
   * @return \App\AdminModule\Components\Clanky\PrilohyClanok\PrilohyClanokAControl */
	public function createComponentPrilohyClanok() {
    $prilohyClanok = $this->prilohyClanokControlFactory->create(); 
    $prilohyClanok->setTitle($this->zobraz_clanok, $this->nazov_stranky, $this->name);
    return $prilohyClanok;
  }
  
  /**
   * Komponenta pre ukazanie komponent clanku.
   * @return \App\AdminModule\Components\Clanky\Komponenty\KomponentyControl */
	public function createComponentKomponenty() {
    $komponenty = $this->komponentyControlFactory->create();
    $komponenty->setTitle($this->zobraz_clanok, $this->nazov_stranky/*, $this->name*/);
    return $komponenty;
  }
  
  /** 
   * Signal pre pridanie komponenty, ktora nema parametre
   * @param string $komponenta_spec_nazov Specificky nazov komponenty
   * @param int $id_hlavne_menu Id clanku */
  public function handleAddKomponenta($komponenta_spec_nazov, $id_hlavne_menu = 0) {
    $k = $this->nastavenie["komponenty"][$komponenta_spec_nazov];
    $this->clanok_komponenty->pridaj(["id_hlavne_menu"=>(int)$id_hlavne_menu, "spec_nazov"=>$komponenta_spec_nazov]);
    $this->flashRedirect(["Clanky:default", $id_hlavne_menu],'Komponenta "'.$k["nazov"].'" bola pridaná!', "success");
  }
}
