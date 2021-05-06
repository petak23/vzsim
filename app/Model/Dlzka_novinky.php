<?php

namespace App\Model;

/**
 * Model, ktory sa stara o tabulku dlzka_novinky
 * 
 * Posledna zmena 14.04.2021
 * 
 * @author     Ing. Peter VOJTECH ml. <petak23@gmail.com>
 * @copyright  Copyright (c) 2012 - 2021 Ing. Peter VOJTECH ml.
 * @license
 * @link       http://petak23.echo-msz.eu
 * @version    1.0.2
 */
class Dlzka_novinky extends Table {
  /** @var string */
  protected $tableName = 'dlzka_novinky';
  
  /** Hodnoty id=>nazov pre formulare
   * @return array */
  public function dlzkaNovinkyForm() {
    return $this->findAll()->fetchPairs('id', 'nazov');
  }  
}
