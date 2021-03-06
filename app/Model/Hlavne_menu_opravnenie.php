<?php

namespace App\Model;

/**
 * Model, ktory sa stara o tabulku hlavne_menu_opravnenie
 * 
 * Posledna zmena 14.04.2021
 * 
 * @author     Ing. Peter VOJTECH ml. <petak23@gmail.com>
 * @copyright  Copyright (c) 2012 - 2021 Ing. Peter VOJTECH ml.
 * @license
 * @link       http://petak23.echo-msz.eu
 * @version    1.0.1
 */
class Hlavne_menu_opravnenie extends Table {
  /** @var string */
  protected $tableName = 'hlavne_menu_opravnenie';
    
  /** Hodnoty id=>nazov pre formulare
   * @return array */
  public function opravnenieForm() {
    return $this->findAll()->fetchPairs('id', 'nazov');
  }
}