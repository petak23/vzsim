<?php

namespace App\Model;

/**
 * Model, ktory sa stara o tabulku clanok_komponenty
 * 
 * Posledna zmena(last change): 14.04.2021
 * 
 * @author     Ing. Peter VOJTECH ml. <petak23@gmail.com>
 * @copyright  Copyright (c) 2012 - 2021 Ing. Peter VOJTECH ml.
 * @license
 * @link       http://petak23.echo-msz.eu
 * @version    1.0.8
 */
class Clanok_komponenty extends Table {
  /** @var string */
  protected $tableName = 'clanok_komponenty';

  /** Testuje, jedinecne komponenty ci su uz pridane k clanku. Ak ano vypusti
   * @param array $komponenty Pole dostupnych komponent
   * @param int $id_hlavne_menu Id clanku
   * @return array */
  public function testJedinecnosti($komponenty, $id_hlavne_menu) {
    $out = [];
    foreach ($komponenty as $k=>$v) {//kontrola jedinecnych komponent. Ak uz su priradene tak sa vypustia
      if ($v["jedinecna"]) {
        if ($this->findOneBy(["id_hlavne_menu"=>$id_hlavne_menu, "spec_nazov" => $k]) === FALSE) {
          $out[$k] = $v;
        }
      } else {
        $out[$k] = $v;
      }
    }
    return $out;
  }
  
  /** Vracia vsetky komponenty priradene k polozke
   * @param int $id_hlavne_menu Id prislusneho clanku
   * @param array $komponenty Info o komponentach
   * @return array */
  public function getKomponenty($id_hlavne_menu, $komponenty) {
    $out = [];
    $pom = $this->getTable()->where("id_hlavne_menu", $id_hlavne_menu);
    foreach ($pom as $value) {
      $tmp_komp = [];
      $tmp_komp['nazov'] = $value->spec_nazov;
      $tmp_komp['jedinecna'] = $komponenty[$value->spec_nazov]['jedinecna'];
      if (isset($value->parametre)) {
        $tnk = array_keys($komponenty[$value->spec_nazov]['parametre']);
        $ttk = explode(",", $value->parametre);
        $tmp_komp['parametre'] = array_combine($tnk, $ttk);
      }
      if (isset($komponenty[$value->spec_nazov]['route'])) {
        $tmp_komp['route'] = $komponenty[$value->spec_nazov]['route'];
      }
      $out[] = $tmp_komp;
    }
    return $out;
  }
  
  /** Vracia nazvy vsetkych komponent priradenych k polozke
   * @param int $id_hlavne_menu Id prislusneho clanku
   * @return array */
  public function getKomponentyName($id_hlavne_menu) {
    $out = [];
    $pom = $this->getTable()->where("id_hlavne_menu", $id_hlavne_menu);
    foreach ($pom as $value) {
      $out[] = $value->spec_nazov;
    }
    return $out;
  }
}