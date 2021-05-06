<?php
declare(strict_types=1);

namespace App\Model;

use Nette;
use Nette\Utils\Random;
use Nette\Utils\Image;

/**
 * Model, ktory sa stara o tabulku hlavne_menu a hlavne_menu_lang
 * 
 * Posledna zmena 14.04.2021
 * 
 * @author     Ing. Peter VOJTECH ml. <petak23@gmail.com>
 * @copyright  Copyright (c) 2012 - 2021 Ing. Peter VOJTECH ml.
 * @license
 * @link       http://petak23.echo-msz.eu
 * @version    1.2.1
 */
class Hlavne_menu extends Table {
  /** @var string */
  protected $tableName = 'hlavne_menu';
  
  /** @var Nette\Database\Table\Selection */
	protected $hlavne_menu_lang;  
  /** @var Nette\Database\Table\Selection */
	protected $hlavne_menu_cast;

  /**
   * @param Nette\Database\Context $db
   * @param Nette\Security\User $user
   * @param Hlavne_menu_lang $hlavne_menu_lang */
  public function __construct(Nette\Database\Context $db, Nette\Security\User $user, Hlavne_menu_lang $hlavne_menu_lang)  {
    parent::__construct($db);
    $this->hlavne_menu_lang = $hlavne_menu_lang;
    $this->hlavne_menu_cast = $this->connection->table("hlavne_menu_cast");
    $this->user = $user;
	}
  
  /** 
   * Funkcia hlada ci polozka s Id ma podradene polozky
   * @param int $id Id polozky, pre ktoru hladam podradenu
   * @return bool */
  public function maPodradenu(int $id): bool {
    return count($this->findBy(["id_nadradenej"=>$id])) ? TRUE : FALSE;
  }
	
  /** 
   * Vypis menu pre Front modul
   * @param string $language Skratka zobrazovaneho jazyka
   * @return array */
	public function getMenuFront(string $language = 'sk'): array {	
    $id_reg = isset($this->user->getIdentity()->id_user_roles) ? $this->user->getIdentity()->id_user_roles : 0;
    $h = clone $this->hlavne_menu_lang;
		$s = $h->findBy(["hlavne_menu.id_user_roles <= " . $id_reg, "lang.skratka" => $language])
            ->where("hlavne_menu.druh.modul IS NULL OR hlavne_menu.druh.modul = ?", "Front");
    $polozky = $s->order('hlavne_menu.id_hlavne_menu_cast, hlavne_menu.uroven, hlavne_menu.poradie ASC');
    return count($polozky) ? $this->_getMenuFront($polozky) : [];
  }
  
  /** 
   * Vytvorenie menu pre front
   * @param Nette\Database\Table\Selection $polozky Vyber poloziek hl. menu
   * @param string $abs_link Absolutna cast odkazu
   * @return array */
  private function _getMenuFront(Nette\Database\Table\Selection $polozky, string $abs_link = ""): array {
    $out = [];
		$cislo_casti = 0;
    foreach ($polozky as $ja) {
      $v = $ja->hlavne_menu;
      //Mam taku istu cast ako pred tym? Ak nie nastav cislo casti, ale len ak je to dovolene cez $casti
      if ($cislo_casti !== $v->id_hlavne_menu_cast) { //Len jeden prechod cez toto a to na začiatku
        $cislo_casti = $v->id_hlavne_menu_cast;
        $temp_pol = new \App\FrontModule\Components\Menu\MenuNode;
        $temp_pol->name = $v->hlavne_menu_cast->view_name;
        $temp_pol->link = $abs_link."Homepage:";
        $temp_pol->id = -1*$v->hlavne_menu_cast->id;
        $out[] = ["node"=>$temp_pol, "nadradena"=>FALSE];
        unset($temp_pol);
      }
      $for_link = $abs_link.($v->druh->presenter == "Menu" ? "Clanky" : $v->druh->presenter).":";
      $temp_pol = new \App\FrontModule\Components\Menu\MenuNode;
      $temp_pol->name = $ja->menu_name;
      $temp_pol->tooltip = $ja->h1part2;
      $temp_pol->view_name = $ja->view_name;
      $temp_pol->avatar = $v->avatar;
      $temp_pol->anotacia = ($v->druh->presenter == "Clanky" && isset($ja->clanok_lang->anotacia)) ? $ja->clanok_lang->anotacia : FALSE;
      $temp_pol->node_class = ($v->ikonka !== NULL && strlen($v->ikonka)>2) ? $v->ikonka : NULL;
      $temp_pol->link = $v->druh->je_spec_naz ? [$for_link] : $for_link;
      $temp_pol->absolutna = $v->absolutna;
      $temp_pol->novinka = $v->id_dlzka_novinky > 1 ? $v->modified->add(new \DateInterval('P'.$v->dlzka_novinky->dlzka.'D')) : NULL;
      $temp_pol->id = $v->id;
      $temp_pol->poradie_podclankov = $v->poradie_podclankov;
      $out[] = ["node"=>$temp_pol, "nadradena"=>isset($v->id_nadradenej) ? $v->id_nadradenej : -1*$v->hlavne_menu_cast->id];
      unset($temp_pol);
    }
    return $out;
  }
  
  /** 
   * Vypis menu pre mapu webu
   * @param int $lang_id Id zobrazovaneho jazyka
   * @return array */
  public function getMenuMapa(int $lang_id): array {	
		$polozky = $this->hlavne_menu_lang->findBy(["hlavne_menu.id_user_roles" => 0, "id_lang" => $lang_id])
                    ->order('hlavne_menu.id_hlavne_menu_cast, hlavne_menu.uroven, hlavne_menu.poradie ASC');
    return count($polozky) ? $this->_getMenuMapa($polozky) : [];
  }

  /** 
   * Vytvorenie menu pre mapu
   * @param Nette\Database\Table\Selection $polozky Vyber poloziek hl. menu
   * @return array */
  private function _getMenuMapa(Nette\Database\Table\Selection $polozky): array {
    $out = [];
		$cislo_casti = 0;
    foreach ($polozky as $ja) {
      $v = $ja->hlavne_menu;
      //Mam taku istu cast ako pred tym? Ak nie nastav cislo casti, ale len ak je to dovolene cez $casti
      if ($cislo_casti !== $v->id_hlavne_menu_cast) { //Len jeden prechod cez toto a to na začiatku
				$cislo_casti = $v->id_hlavne_menu_cast;
        $temp_pol = new \App\FrontModule\Components\Menu\MenuNode;
        $temp_pol->link = "//:Front:Homepage:";
        $temp_pol->id = -1*$v->hlavne_menu_cast->id;
        $out[] = ["node"=>$temp_pol, "nadradena"=>FALSE];
        unset($temp_pol);
      }
      $for_link = "//:Front:".($v->druh->presenter == "Menu" ? "Clanky" : $v->druh->presenter).":";
      $temp_pol = new \App\FrontModule\Components\Menu\MenuNode;
      $temp_pol->link = $v->druh->je_spec_naz ? [$for_link] : $for_link;
      $temp_pol->absolutna = $v->absolutna;
      $temp_pol->id = $v->id;
      $out[] = ["node"=>$temp_pol, "nadradena"=>isset($v->id_nadradenej) ? $v->id_nadradenej : -1*$v->hlavne_menu_cast->id];
      unset($temp_pol);
    }
    return $out;
  }

  /** Vypis menu pre Admin modul
   * @param int $lang_id Id jazyka
   * @return array */
  public function getMenuAdmin(int $lang_id = 1): array {	
    $id_reg = isset($this->user->getIdentity()->id_user_roles) ? $this->user->getIdentity()->id_user_roles : 0;
    $polozky = $this->hlavne_menu_lang
               ->findBy(["hlavne_menu.id_user_roles <= " . $id_reg, "id_lang" => $lang_id, "hlavne_menu.druh.modul IS NULL OR hlavne_menu.druh.modul = ?" => "Admin"])
               ->order('hlavne_menu.id_hlavne_menu_cast, hlavne_menu.uroven, hlavne_menu.poradie ASC');
    return count($polozky) ? $this->_getMenuAdmin($polozky) : [];
  }
  
  /** 
   * Vytvorenie menu pre administraciu
   * @param Nette\Database\Table\Selection $polozky Vyber poloziek hl. menu
   * @return array */
  private function _getMenuAdmin(Nette\Database\Table\Selection $polozky): array {
    $cislo_casti = 0; //aktualne cislo casti
    $casti = [];
    $out = [];
    foreach ($polozky as $ja) {
      $v = $ja->hlavne_menu;
      //Mam taku istu cast ako pred tym? Ak nie nastav cislo casti, ale len ak je to dovolene cez $casti
      if ($cislo_casti !== $v->id_hlavne_menu_cast) { //Mam taku istu cast ako pred tym? Ak nie nastav cislo casti
        $cislo_casti = $v->id_hlavne_menu_cast;
        $casti[] = $cislo_casti;
        $temp_pol = new \App\AdminModule\Components\Menu\MenuNode;
        $temp_pol->name = $v->hlavne_menu_cast->view_name;
        $temp_pol->link = ["Homepage:"];
        $temp_pol->id = -1*$v->hlavne_menu_cast->id;
        $out[] = ["node"=>$temp_pol, "nadradena"=>FALSE];
        unset($temp_pol);
      }
      $temp_pol = new \App\AdminModule\Components\Menu\MenuNode;
      $temp_pol->name = $ja->menu_name;
      $temp_pol->tooltip = $ja->h1part2;
      $temp_pol->view_name = $ja->view_name;
      $temp_pol->avatar = $v->avatar;
      $temp_pol->anotacia = ($v->druh->presenter == "Clanky" && isset($ja->clanok_lang->anotacia)) ? $ja->clanok_lang->anotacia : FALSE;
      $temp_pol->node_class = ($v->ikonka !== NULL && strlen($v->ikonka)>2) ? "fa fa-".$v->ikonka : NULL;
      $temp_pol->link = $v->druh->je_spec_naz ? [$v->druh->presenter.":"] : $v->druh->presenter.":";
      $temp_pol->id = $v->id;
      $temp_pol->poradie_podclankov = $v->poradie_podclankov;
      $temp_pol->datum_platnosti = $v->datum_platnosti;
      $out[] = ["node"=>$temp_pol, "nadradena"=>isset($v->id_nadradenej) ? $v->id_nadradenej : -1*$v->hlavne_menu_cast->id];
      unset($temp_pol);
    }
    $c = $this->hlavne_menu_cast->fetchPairs("id");
    if (count($casti) !== count($c)) {
      foreach ($c as $v) {
        if (array_search($v->id, $casti) === FALSE) {
          $temp_pol = new \App\AdminModule\Components\Menu\MenuNode;
          $temp_pol->name = $v->view_name;
          $temp_pol->link = ["Homepage:"];
          $temp_pol->id = -1*$v->id;
          $out[] = ["node"=>$temp_pol, "nadradena"=>FALSE];
          unset($temp_pol);
        }
      } 
    }
    return $out;
  }
  
  /**
   * Funkcia pre zmenu vlastníka
   * @param Nette\Utils\ArrayHash $values udaje
   * @return Nette\Database\Table\ActiveRow|null */
  public function zmenVlastnika(Nette\Utils\ArrayHash $values): ?Nette\Database\Table\ActiveRow {
    return $this->uloz(["id_user_main" => $values->id_user_main], $values->id);
  }
  
  /**
   * Funkcia pre zmenu urovne registracie polozky
   * @param Nette\Utils\ArrayHash $values udaje
   * @return Nette\Database\Table\ActiveRow|null */
  public function zmenUrovenRegistracie(Nette\Utils\ArrayHash $values): ?Nette\Database\Table\ActiveRow {
    return $this->uloz(["id_user_roles" => $values->id_user_roles], $values->id);
  }
  
  /**
   * Funkcia pre zmenu urovne registracie polozky
   * @param Nette\Utils\ArrayHash $values udaje
   * @return Nette\Database\Table\ActiveRow|null */
  public function zmenDatumPlatnosti(Nette\Utils\ArrayHash $values): ?Nette\Database\Table\ActiveRow {
    return $this->uloz(["datum_platnosti" => $values->platnost == 1 ? $values->datum_platnosti : NULL], $values->id);
  }
  
  /**
   * Funkcia pre zmenu dlzky sledovania ako novinky polozky
   * @param Nette\Utils\ArrayHash $values udaje
   * @return Nette\Database\Table\ActiveRow|null */
  public function zmenDlzkuNovinky(Nette\Utils\ArrayHash $values): ?Nette\Database\Table\ActiveRow {
    return $this->uloz(["id_dlzka_novinky" => $values->id_dlzka_novinky], $values->id);
  }
  
  /**
   * Funkcia pre zmenu opravnenia nevlastnikov polozky
   * @param Nette\Utils\ArrayHash $values udaje
   * @return Nette\Database\Table\ActiveRow|null */
  public function zmenOpravnenieNevlastnikov(Nette\Utils\ArrayHash $values): ?Nette\Database\Table\ActiveRow {
    return $this->uloz(["id_hlavne_menu_opravnenie" => $values->id_hlavne_menu_opravnenie], $values->id);
  }
  
  /**
   * Funkcia pre zmenu opravnenia podla kategorie
   * @param Nette\Utils\ArrayHash $values udaje
   * @return Nette\Database\Table\ActiveRow|null */
  public function zmenOpravnenieKategoria(Nette\Utils\ArrayHash $values): ?Nette\Database\Table\ActiveRow {
    return $this->uloz(["id_user_categories" => $values->id_user_categories], $values->id);
  }
  
  /**
   * Zmena sablony
   * @param Nette\Utils\ArrayHash $values udaje
   * @return Nette\Database\Table\ActiveRow|null */
  public function changeTemplate(Nette\Utils\ArrayHash $values): ?Nette\Database\Table\ActiveRow {
    return $this->uloz(["id_hlavne_menu_template" => $values->id_hlavne_menu_template], $values->id);
  }
  
  /**
   * Zmena okrajov
   * @param Nette\Utils\ArrayHash $values udaje
   * @return Nette\Database\Table\ActiveRow|null */
  public function changeBorders(Nette\Utils\ArrayHash $values): ?Nette\Database\Table\ActiveRow {
    return $this->uloz([
        'border_a'=>$values->border_a_width != 0 ? ($values->border_a_color.'|'.$values->border_a_width) : '#000000|0',
        'border_b'=>$values->border_b_width != 0 ? ($values->border_b_color.'|'.$values->border_b_width) : '#000000|0',
        'border_c'=>$values->border_c_width != 0 ? ($values->border_c_color.'|'.$values->border_c_width) : '#000000|0'
    ], $values->id);
  }
  
  /**
   * Ulozenie titulneho obrazku alebo ikonky
   * @param Nette\Utils\ArrayHash $values
   * @param string $avatar_path
   * @param string $www_dir
   * @throws Database\DriverException */
  public function zmenTitleImage(Nette\Utils\ArrayHash $values, string $avatar_path, string $www_dir) {
    if ($values->avatar->hasFile()) {
      if ($values->avatar->isImage()){
        if (($tmp = $this->find($values->id)->avatar) !== NULL) {
          $this->_delAvatar($tmp, $avatar_path, $www_dir);
        }
        $values->avatar = $this->_uploadTitleImage($values->avatar, $www_dir."/".$avatar_path);
        $this->uloz(["ikonka"=>NULL, "avatar"=>$values->avatar], $values->id);
      } else {
        throw new Database\DriverException('Pre titulný obrázok nebol použitý obrázok a tak nebol uložený!'.$e->getMessage());
      }
    } elseif ($values->ikonka){
      $this->_delAvatar($values->old_avatar, $avatar_path, $www_dir);
      $this->uloz(["ikonka"=>$values->ikonka, "avatar"=>NULL], $values->id);
    } else { 
      throw new Database\DriverException('Pri pokuse o uloženie došlo k chybe! Pravdepodobná príčina je č.'.$values->avatar->error.". ".$e->getMessage());
    }
  }
  
  /**
   * Zmazanie titulneho obrazku a/alebo ikonky
   * @param string $id
   * @param string $avatar_path
   * @param string $www_dir
   * @return Nette\Database\Table\ActiveRow|null */
  public function zmazTitleImage(string $id, string $avatar_path, string $www_dir): ?Nette\Database\Table\ActiveRow {
    $hl = $this->find($id);
    if (is_string($hl->avatar)) {
      $this->_delAvatar($hl->avatar, $avatar_path, $www_dir);
    }
    return $this->uloz(["ikonka"=>NULL, "avatar"=>NULL], $id);
  }
  
  /**
   * @param Nette\Http\FileUpload $avatar
   * @param string $path
   * @return string */
  private function _uploadTitleImage(Nette\Http\FileUpload $avatar, string $path): string {
    $pi = pathinfo($avatar->getSanitizedName());
    $ext = $pi['extension'];
    $avatar_name = Random::generate(15).".".$ext;
    $avatar->move($path.$avatar_name);
    $image = Image::fromFile($path.$avatar_name);
    $image->save($path.$avatar_name, 75);
    return $avatar_name;
  }
  
  /**
   * @param string $avatar_name
   * @param string $avatar_path
   * @param string $www_dir */
  private function _delAvatar(string $avatar_name, string $avatar_path, string $www_dir) {
    if ($avatar_name !== NULL && is_file($avatar_path.$avatar_name)) { 
      unlink($www_dir."/".$avatar_path.$avatar_name);
    }
  }
  
  /** 
   * Upravy hodnoty a ulozi polozku
   * @param array $values
   * @return Nette\Database\Table\ActiveRow|null */
  public function ulozPolozku(array $values): ?Nette\Database\Table\ActiveRow {
    $id = $values['id'];
    $values['spec_nazov'] = $id ? $values['spec_nazov'] : $this->najdiSpecNazov($values['sk_menu_name']);
    $values['id_nadradenej'] = isset($values['id_nadradenej']) && $values['id_nadradenej'] > 0 ? $values['id_nadradenej'] : NULL;
    $values['nazov_ul_sub'] = isset($values['nazov_ul_sub']) && strlen($values['nazov_ul_sub']) > 1 ? $values['nazov_ul_sub'] : NULL;
    $values['id_hlavne_menu_template'] = isset($values['id_hlavne_menu_template']) ? $values['id_hlavne_menu_template'] : 1;
    unset($values['id'], $values['sk_menu_name']);
    return $this->uloz($values, $id);
  }
  
  /**
   * Ulozi clanok
   * @param Nette\Utils\ArrayHash $values
   * @param array $default
   * @param Nette\Database\Table\Selection $lang
   * @return int Id ulozeneho clanku */
  public function saveArticle(Nette\Utils\ArrayHash $values, array $default, Nette\Database\Table\Selection $lang): int { 
    // Vyber hodnoty pre DB tabulku hlavne_menu, uloz a odstran z pola values
    $uloz = $this->ulozPolozku(array_merge($default, 
                                  ['id_hlavicka'  => isset($values->id_hlavicka) ? $values->id_hlavicka : 0, 
                                   'poradie'      => $values->poradie, 
                                   'absolutna'    => isset($values->absolutna) ? $values->absolutna : null,
                                   'sk_menu_name' => $values->sk_menu_name]));
    unset($values->id_hlavicka, $values->poradie, $values->absolutna);
    // Uloz do tabulky hlavne_menu_lang
    if (!empty($uloz['id'])) {
      $this->hlavne_menu_lang->ulozPolozku($values, $lang, $uloz['id']);
    }
    return $uloz['id'];
  }
}