<?php

namespace App\Model;

use Nette;

/**
 * Model starajuci sa o tabulku hlavne_menu_lang
 * 
 * Posledna zmena 14.04.2021
 * 
 * @author     Ing. Peter VOJTECH ml. <petak23@gmail.com>
 * @copyright  Copyright (c) 2012 - 2021 Ing. Peter VOJTECH ml.
 * @license
 * @link       http://petak23.echo-msz.eu
 * @version    1.1.0
 */
class Hlavne_menu_lang extends Table {
  const 
    NOT_EXIST = 1,
    MISSING_PERMISSIONS = 2;
  
  /** @var string */
  protected $tableName = 'hlavne_menu_lang';
    
  /** Funkcia pre ziskanie info o konkretnom clanku na zaklade spec_nazov, language_id 
	  * a min. urovne registracie uzivatela
		* @param string $spec_nazov - specificky nazov clanku v hl. menu
		* @param int $language_id - id jazykovej mutacie clanku. Ak nemam tak 1 - sk
		* @param int $id_user_roles - min. uroven registracie uzivatela. Ak nemam tak sa berie 5
		* @return array|FALSE */
	public function getOneArticleSp($spec_nazov, $language_id = 1, $id_user_roles = 5) {
    $articles = clone $this;
		//Najdi v tabulke hlavne_menu polozku podla spec. nazvu a urovne registracie
    return $articles->getTable()->where("hlavne_menu.spec_nazov", $spec_nazov)
                                ->where("id_lang", $language_id)
                                ->where("hlavne_menu.id_user_roles <= ?", $id_user_roles)
                                ->fetch();
  }
  
  /** 
   * Funkcia pre ziskanie info o konkretnom clanku na zaklade id, language_id 
	 * a min. urovne registracie uzivatela
	 * @param int $id_hlavne_menu Id polozky v tabulke "hlavne_menu"
	 * @param int $id_lang Id jazykovej mutacie clanku v tabulke "lang". Ak nemam tak 1 - sk. 
	 * @param int $id_user_roles Min. uroven registracie uzivatela. Ak nemam tak sa berie 0 - guest
	 * @return Nette\Database\Table\ActiveRow
   * @throws ArticleExteption */
  public function getOneArticleId(int $id_hlavne_menu,int $id_lang = 1,int $id_user_roles = 0) {
    $articles = clone $this;
    //Najdi v tabulke hlavne_menu polozku podla id
    $tmp_article = $articles->getTable()->where(["id_hlavne_menu" => $id_hlavne_menu, "id_lang" => $id_lang]);
    if ($tmp_article->count() == 0) {
      throw new ArticleMainMenuException("Article not exist", self::NOT_EXIST);
    } else { // Article found
      $tmp_article_final = $tmp_article->where("hlavne_menu.id_user_roles <= ?", $id_user_roles)->fetch();
      if ($tmp_article_final === FALSE) {
        throw new ArticleMainMenuException("Missing permissions", self::MISSING_PERMISSIONS);
      } else {
        return $tmp_article_final;
      }
    }
  }
  
  public function ulozTextClanku($values, $action, $id_hlavne_menu) {
    $uloz_txt = [];
    foreach ($values as $k => $v) {
      $a = explode("_", $k, 2);
      $uloz_txt[$a[0]][$a[1]] = $v;
    }
    $ulozenie = 1;
		if (($utc = count($uloz_txt))) {
			foreach($uloz_txt as $ke => $ut){
        $cid = ($action == "edit2") ? (isset($ut["id"]) ? $ut["id"] : 0) : 0;
				$uloz_t = $this->ulozClanokLang($ut, $cid);
        if ($uloz_t !== FALSE && $uloz_t['id']) { //Ulozenie v poriadku
          $this->_prepojHlavneMenuLeng($cid, $id_hlavne_menu, $uloz_t['id'], $ut["id_lang"]);
          $ulozenie++;
        }
			}
			if ($ulozenie != $utc+1) { //Nieco sa neulozilo v poriadku
				//TODO!!! Zmazanie toho co sa uz ulozilo
				$ulozenie = 0; 
			}
    } else { $ulozenie = ($this->action == "add2") ? 0 : 1;} //Ak pri pridani nemam texty je to chyba!
    return $ulozenie;
  }
  
  /** Funkcia pridava alebo aktualizuje v DB tabulke 'clanok_lang' podla toho, ci je zadané ID
   * @param array $data
   * @param int $id
   * @return Nette\Database\Table\ActiveRow|null */
  public function ulozClanokLang(array $data, int $id = 0): ?Nette\Database\Table\ActiveRow {
    $clanok_lang = $this->connection->table('clanok_lang');
    return $id ? ($clanok_lang->where(['id'=>$id])->update($data) !== FALSE ? $clanok_lang->get($id) : FALSE): $clanok_lang->insert($data);
  }
  
  /** 
   * Ulozi texty pre tabulku hlavne_menu_lang
   * @param Nette\Utils\ArrayHash $values
   * @param Nette\Database\Table\Selection $jazyky
   * @param int $id
   * @return bool */
  public function ulozPolozku(Nette\Utils\ArrayHash $values, Nette\Database\Table\Selection $jazyky, int $id = 0): bool {
    $ulozenie = 0;
    foreach($jazyky as $j){
      foreach(["menu_name", "h1part2", "view_name"] as $f){
        $new = $values->{$j->skratka."_".$f};
        $ut[$f] = strlen($new) ? $new : NULL;
      }
      $hlid = $values->{$j->skratka."_id"};
      if ($hlid == 0)  { //pridavam
        $ut["id_lang"] = $j->id;
        $ut["id_hlavne_menu"] = $id;
      }
      if ($this->uloz($ut, $hlid) !== FALSE) { //Ulozenie v poriadku
        $ulozenie++;
      }
    }
    return ($ulozenie == count($jazyky));
  }
  
  /** Ak pridavam tak vytvorim zavislost na hlavne_menu_lang
   * @param int $cid
   * @param int $id_hlavne_menu
   * @param int $id_clanok_lang
   * @param int $id_lang */
  public function _prepojHlavneMenuLeng($cid, $id_hlavne_menu, $id_clanok_lang, $id_lang) {
    if ($cid == 0) { //
      $pol = $this->findOneBy(["id_hlavne_menu"=>$id_hlavne_menu, "id_lang"=>$id_lang]);
      $this->uloz(["id_clanok_lang"=>$id_clanok_lang], $pol->id);
    } 
  }
  
  /**
   * Pre danu polozku vrati len platne podclanky
   * @param int $id_lang Id jazyka
   * @param int $id_nadradenej
   * @return Nette\Database\Table\Selection */
  public function subArticleToView(int $id_lang, int $id_nadradenej) {
    return $this->findBy(["id_lang"=>$id_lang, "hlavne_menu.id_nadradenej"=>$id_nadradenej])
                ->where("datum_platnosti ? OR datum_platnosti >= ? ", NULL, StrFTime("%Y-%m-%d",strtotime("0 day")));
  }

}

/**
 * Exception for a unique constraint violation.
 */
class ArticleMainMenuException extends Nette\Database\ConstraintViolationException {}