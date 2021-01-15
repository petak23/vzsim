<?php

namespace App\Model;
use Nette;

/**
 * Model, ktory sa stara o tabulku udaje
 * 
 * Posledna zmena 06.10.2020
 * 
 * @author     Ing. Peter VOJTECH ml. <petak23@gmail.com>
 * @copyright  Copyright (c) 2012 - 2020 Ing. Peter VOJTECH ml.
 * @license
 * @link       http://petak23.echo-msz.eu
 * @version    1.0.7
 */
class Udaje extends Table {
  
  const
		COLUMN_ID = 'id',
    COLUMN_ID_USER_ROLES = 'id_user_roles',
    COLUMN_ID_DRUH = 'id_druh',
    COLUMN_ID_UDAJE_TYP = 'id_udaje_typ',
    COLUMN_NAME = 'nazov',
    COLUMN_TEXT = 'text',
    COLUMN_COMMENT = 'comment',
    COLUMN_SEPARATE_SETTINGS = 'separate_settings';
  
  /** @var string */
  protected $tableName = 'udaje';

  /** 
   * Vrati pozadovany zaznam kluca alebo false
   * @param string $name Nazov kluca
   * @return Nette\Database\Table\ActiveRow|null */
  public function getByName(string $name): ?Nette\Database\Table\ActiveRow {
    return strlen($name) ? $this->findOneBy([self::COLUMN_NAME=>$name]) : null;
  }
  
  /** 
   * Vrati pozadovanu hodnotu kluca (pole text) alebo prázdny string
   * @param string $name Nazov kluca
   * @return string */
  public function getValByName(string $name): string {
    return ($t = $this->getByName($name)) !== null ? $t->{self::COLUMN_TEXT} : "";
  }
  
  /**
   * Opravy v tabulke zaznam s danym nazvom
   * @param string $key Nazov kluca zo stlpca nazov
   * @param string $value Nova hodnota kluca
   * @return bool|null Ak bolo co opravit alebo je to bez zmeny tak true inak false. Ak sa kluc nenasiel tak null.*/
  public function editKey(string $key, string $value): ?bool {
    //update - Ak bolo co opravit tak true inak(nema co opravit) false
    return (($tmp = $this->getByName($key)) !== null)
           ?(($tmp->{self::COLUMN_TEXT} != $value) ? $tmp->update([self::COLUMN_TEXT=>$value]) : true)
           : null;
  }
  
  /** 
   * Vrati vsetky dostupne udaje podla urovne registracie
   * @param int $id_user_roles min. uroven registracie
   * @return Nette\Database\Table\Selection */
  public function vypisUdaje(int $id_user_roles = 0): Nette\Database\Table\Selection {
    return $this->getTable()->where(self::COLUMN_ID_USER_ROLES . " <= ?", $id_user_roles);
  }
  
  /** 
   * Funkcia vrati celociselnu hodnotu udaju s nazvom
   * @param string $nazov Nazov udaju
   * @return int */
  public function getUdajInt(string $nazov): int {
    $p = $this->getByName($nazov);
    return $p !== null ? (int)$p->text : 0;
  }
  
  /** 
   * Funkcia pre ulozenie udaju z formulara
   * @param Nette\Utils\ArrayHash $values
   * @param array $ur_reg
   * @return Nette\Database\Table\ActiveRow|FALSE
   * @throws Database\DriverException */
  public function ulozUdaj(Nette\Utils\ArrayHash $values, array $ur_reg) {
    $id = isset($values->id) ? $values->id : 0;
    unset($values->id);
    if (isset($values->spravca)) {
      $values->offsetSet(self::COLUMN_ID_USER_ROLES, $values->spravca ? $ur_reg['manager'] : $ur_reg['admin']);
      unset($values->spravca);
    }
    if (isset($values->druh_null)) {
      $values->offsetSet(self::COLUMN_ID_DRUH, $values->druh_null ? NULL : (isset($values->id_druh) ? $values->id_druh : 1));
      unset($values->druh_null);
    } elseif (isset($values->id_druh)) { unset($values->id_druh); }
    try {
      return $this->uloz($values, $id);
    } catch (Exception $e) {
      throw new Database\DriverException('Chyba ulozenia: '.$e->getMessage());
    }
  }
  
  /** 
   * Vrati pozadovane usporiadanie oznamov alebo false
   * @return boolean */
  public function getOznamUsporiadanie() {
    $tmp = $this->findOneBy([self::COLUMN_NAME => "oznam_usporiadanie"]);
    return $tmp !== null ? (boolean)$tmp->text : FALSE;
  }
  
  /** 
   * Vrati pozadovanu skupinu udajov alebo false podla druhu
   * @param string $kluc Nazov druhu
   * @param int $ur_reg Minimalna uroven registracie
   * @return \Nette\Database\Table\Selection|FALSE */
  public function getDruh(string $kluc = "", int $ur_reg = 5) {
    return strlen($kluc) ? $this->findBy(['druh.presenter'=>$kluc, self::COLUMN_ID_USER_ROLES . " <= ".$ur_reg]) : FALSE;
  }
  
  /**
   * Nacitanie a spracovanie hlavnych udajov webu na základe jazyka
   * @param string $language
   * @return array */
  public function hlavneUdaje(string $language = "sk"): array {
    $out = [];
    //Nacitanie len tych premennych, ktore platia pre danu jazykovu mutaciu
    foreach ($this->findAll()->fetchPairs(self::COLUMN_NAME, self::COLUMN_TEXT) as $key => $value) { 
      $kluc = explode("-", $key);
      if (count($kluc) == 2 && $kluc[1] == $language) { $out[substr($key, 0, strlen($key)-strlen($language)-1)] = $value; } 
      if (count($kluc) == 1) {$out[$key] = $value;}
    }
    return $out;
  }
}