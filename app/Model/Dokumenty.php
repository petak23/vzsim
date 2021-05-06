<?php
declare(strict_types=1);

namespace App\Model;
use Nette;
use Nette\Database\Table\Selection;

/**
 * Model, ktory sa stara o tabulku dokumenty
 * 
 * Posledna zmena 14.04.2021
 * 
 * @author     Ing. Peter VOJTECH ml. <petak23@gmail.com>
 * @copyright  Copyright (c) 2012 - 2021 Ing. Peter VOJTECH ml.
 * @license
 * @link       http://petak23.echo-msz.eu
 * @version    1.0.8
 */
class Dokumenty extends Table {
  /** @var string */
  protected $tableName = 'dokumenty';

  /** 
   * Vracia vsetky prilohy polozky
   * @param int $id id_hlavne_menu prislusnej polozky
   * @return Selection */
  public function getPrilohy(int $id): Selection {  
    return $this->findBy(["id_hlavne_menu" => $id])->order("pripona ASC");
  }
  
  /** 
   * Vracia vsetky viditelne prilohy polozky
   * @param int $id id_hlavne_menu prislusnej polozky
   * @param string $order Sposob zoradenia
   * @return Selection */
  public function getViditelnePrilohy(int $id, string $order = "pripona ASC"): Selection {
    return $this->findBy(["id_hlavne_menu" => $id, "zobraz_v_texte" => 1])->order($order);
  }
  
  /** 
   * Vracia vsetky viditelne prilohy - obrazky polozky
   * @param int $id id_hlavne_menu prislusnej polozky
   * @param string $order Sposob zoradenia
   * @return Selection */
  public function getVisibleImages(int $id, string $order = "pripona ASC"): Selection {
    return $this->findBy(["id_hlavne_menu" => $id, "zobraz_v_texte" => 1, "type" => 2])->order($order);
  }
  
  /** 
   * Vracia vsetky viditelne prilohy - video polozky
   * @param int $id id_hlavne_menu prislusnej polozky
   * @param string $order Sposob zoradenia
   * @return Selection */
  public function getVisibleVideos(int $id, string $order = "pripona ASC"): Selection {
    return $this->findBy(["id_hlavne_menu" => $id, "zobraz_v_texte" => 1, "type" => 3])->order($order);
  }
  
  /** 
   * Vracia vsetky viditelne prilohy - audio polozky
   * @param int $id id_hlavne_menu prislusnej polozky
   * @param string $order Sposob zoradenia
   * @return Selection */
  public function getVisibleAudios(int $id, string $order = "pripona ASC"): Selection {
    return $this->findBy(["id_hlavne_menu" => $id, "zobraz_v_texte" => 1, "type" => 4])->order($order);
  }
  
  /** 
   * Vracia vsetky viditelne prilohy - video polozky
   * @param int $id id_hlavne_menu prislusnej polozky
   * @param type $order Sposob zoradenia
   * @return Selection */
  public function getVisibleOther(int $id, string $order = "pripona ASC"): Selection {
    return $this->findBy(["id_hlavne_menu" => $id, "zobraz_v_texte" => 1, "type" => 1])->order($order);
  }
  
  /** 
   * Uloženie jednej prílohy
   * @param array $data 
   * @param mixed $id primary key
   * @return \Nette\Database\Table\ActiveRow|null */
  public function ulozPrilohu(array $data, $id): ?\Nette\Database\Table\ActiveRow {
    $is_image = $data['is_image'];
    unset($data['is_image']);
    $vysledok = $this->uloz($data, $id);
    if ($vysledok !== FALSE && $is_image) { //Ak je to obrazok, tak prida znacku
      $vysledok = $this->oprav($vysledok['id'], ['znacka'=>'#I-'.$vysledok['id'].'#']);
    }
    return $vysledok;
  }
}
