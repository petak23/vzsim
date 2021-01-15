<?php

namespace App\Presenters;

use App\Model;
use Nette\Utils\Json;

/**
 * Prezenter pre editáciu oblasti.
 * Posledna zmena(last change): 12.01.2021
 *
 *	Modul: FRONT
 *
 * @author Ing. Peter VOJTECH ml. <petak23@gmail.com>
 * @copyright  Copyright (c) 2021 - 2021 Ing. Peter VOJTECH ml.
 * @license
 * @link       http://petak23.echo-msz.eu
 * @version 1.0.0
 */
class EditPresenter extends BasePresenter {
  
  /** @var Model\Oblast @inject */
	public $oblast;
  /** @var Model\Oblast_prvky @inject */
  public $oblast_prvky;
  /** @var Model\Prvky_kluc @inject */
  public $prvky_kluc;

  private $aktualna_oblast;

  private $prvky;
  
  public function actionDefault(int $id = 0) {
    if (($this->aktualna_oblast = $this->oblast->find($id)) === null) {
      $this->flashRedirect("Homepage:", "Požadovanú oblasť som nenašiel!", "warning");
    }
    $this->main_menu = array_merge([["nazov"=>"Ukonč oblasť: ".$this->aktualna_oblast->name, "odkaz"=>"Homepage:"]], $this->main_menu);
    $this->prvky = $this->oblast_prvky->findBy(['id_oblast'=>$id]);
  }

  public function renderDefault(int $id = 0) {
    $this->template->oblast = $this->aktualna_oblast;
    $this->template->prvky = $this->prvky;
    $this->template->jprvky = Json::encode($this->rtoArray($this->prvky));
  }

  public function rtoArray($prvky) {
    $out = [];
    foreach($prvky as $p) {
      $out[] = $p->toArray();
    }
    return $out;
  }
  
  public $tmp_zaznam = [
    'kluc' => 'Kľúč', //A
    'xs' => 'xs', //B
    'y' => 'y', //C
    'c0' => 'c0', //D
    'c1' => 'c1', //E
    'c2' => 'c2', //F
    'c3' => 'c3', //G
    'dl' => 'dl', //H
    'n0' => 'n0', //I
    'n1' => 'n1', //J
    'sm' => 'sm', //K
    'rezim' => 'rezim', //L
    'odk' => 'odk', //M
  ];
  
  public function actionImport(int $id_oblast = 0) {
     $out=[];$id=1;
    if (($handle = fopen("data/mala.csv", "r")) !== FALSE) {
      while (($data = fgetcsv($handle, 1500, ";")) !== FALSE) {
        $temp = ['id'=>$id];$i = 0;
        foreach ($this->tmp_zaznam as $k => $v) {
          $temp[$k] = $data[$i];
          $i++;
        }
        $out[] = $temp;
      }
      $id++;
      fclose($handle);
    }
    //dumpe($out);
    foreach ($out as $v) {
      $kluc = $this->prvky_kluc->findOneBy(['key' => $v['kluc']]);
      $prvok = $this->oblast_prvky->pridaj([
        'id_oblast' => $id_oblast,
        'id_prvky_kluc' => $kluc->id, //A
        'xs' => $v['xs'], //B
        'y' => $v['y'], //C
        'c0' => $v['c0'], //D
        'c1' => $v['c1'], //E
        'c2' => $v['c2'], //F
        'c3' => $v['c3'], //G
        'dl' => $v['dl'], //H
        'n0' => $v['n0'], //I
        'n1' => $v['n1'], //J
        'sm' => $v['sm'], //K
        'rezim' => $v['rezim'], //L
        'odk' => $v['odk'], //M
      ]);
    }
    //dumpe($out);

    $this->flashRedirect('default', 'OK', 'alert-success');
  } 
}