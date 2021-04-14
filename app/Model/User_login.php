<?php

namespace App\Model;

use Nette\Database\Table\Selection;
use Nette\Database\Table\ActiveRow;

/**
 * Model, ktory sa stara o tabulku user_login
 * 
 * Posledna zmena 06.10.2020
 * 
 * @author     Ing. Peter VOJTECH ml. <petak23@gmail.com>
 * @copyright  Copyright (c) 2012 - 2020 Ing. Peter VOJTECH ml.
 * @license
 * @link       http://petak23.echo-msz.eu
 * @version    1.0.2
 */ 
class User_login extends Table {
  const
    COLUMN_LOG_IN_DATETIME = 'log_in_datetime',
    COLUMN_ID_USER_MAIN = 'id_user_main';
  
  /** @var string */
  protected $tableName = 'user_login';

  /** Vrati poslednych x prihlaseni
   * @param int $pocet
   * @return Selection */
  public function getLastPr(int $pocet = 25): Selection {
		return $this->findAll()->order(self::COLUMN_LOG_IN_DATETIME.' DESC')->limit($pocet);
	}
  
  /** Zapise prihlasenie
   * @param int $id_user_main
   * @return ActiveRow|null */
  public function addLogIn(int $id_user_main): ?ActiveRow {
    return $this->pridaj([self::COLUMN_ID_USER_MAIN => $id_user_main, 
                          self::COLUMN_LOG_IN_DATETIME => StrFTime("%Y-%m-%d %H:%M:%S", Time())
                         ]);
  }
  
  /** 
   * Vymaze vstetky data z DB tabulky user_login */
  public function delAll() {
    $this->getTable()->delete();
  }
}