<?php

namespace App\Model;

/**
 * Model, ktory sa stara o tabulku lang
 * 
 * Posledna zmena 06.10.2020
 * 
 * @author     Ing. Peter VOJTECH ml. <petak23@gmail.com>
 * @copyright  Copyright (c) 2012 - 2020 Ing. Peter VOJTECH ml.
 * @license
 * @link       http://petak23.echo-msz.eu
 * @version    1.0.6
 */
class Lang extends Table {
  /** @var string */
  protected $tableName = 'lang';

  /** Vracia vsetky akceptovane jazyky
   * @return \Nette\Database\Table\Selection */
  public function akceptovane(): \Nette\Database\Table\Selection {
    return $this->findBy(["prijaty"=>1]);
  }

  /**
   * Vracia skratku jazyka. Ak nenájde vráti prázdny reťazec
   * @param string hľadaná skratka
   * @return string */
  public function getLangS(string $lang_shortcut = "sk"): string {
    $tmp = $this->findOneBy(['skratka' => $lang_shortcut]);
    return ($tmp != null && isset($tmp->skratka)) ? $tmp->skratka : "";
  }
  
}