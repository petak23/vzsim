<?php

namespace App\Presenters;

use App\Model;
use Nette\Utils\Json;

/**
 * Prezenter pre editáciu oblasti.
 * Posledna zmena(last change): 22.01.2021
 *
 *	Modul: FRONT
 *
 * @author Ing. Peter VOJTECH ml. <petak23@gmail.com>
 * @copyright  Copyright (c) 2021 - 2021 Ing. Peter VOJTECH ml.
 * @license
 * @link       http://petak23.echo-msz.eu
 * @version 1.0.1
 */
class EditPresenter extends BasePresenter {
  
  /** @var Model\Oblast @inject */
	public $oblast;
  /** @var Model\Oblast_cesty @inject */
  public $oblast_cesty;
  /** @var Model\Oblast_prvky @inject */
  public $oblast_prvky;
  /** @var Model\Prvky_kluc @inject */
  public $prvky_kluc;
  /** @var Model\Vlaky @inject */
  public $vlaky;

  private $aktualna_oblast;

  private $prvky;
  
  public function actionDefault(int $id = 0) {
    if (($this->aktualna_oblast = $this->oblast->find($id)) === null) {
      $this->flashRedirect("Homepage:", "Požadovanú oblasť som nenašiel!", "warning");
    }
    $this->main_menu = array_merge([["nazov"=>"Ukonč oblasť: ".$this->aktualna_oblast->name, "odkaz"=>"Homepage:"]], $this->main_menu);
    $this->prvky = $this->oblast_prvky->findBy(['id_oblast'=>$id]);
  }

  public function renderDefault() {
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
  
  public $tmp_cesta = [
    'typ' => 'Typ',
    'zc' => 'zc',
    'kc' => 'kc',
    'vyh' => 'vyh' //:array[1..20]of byte;{VK-bit 0,1 cesta 1 al.2 ak 0 nepouzite}
                   //                    {  -bit 2,3 cesta 3 al.4 ak 0 nepouzite}
  ];
  
  private $tmp_vlaky = [
    'cis1'=>'cis1',
    'cis2'=>'cis2',
    'mz'=>'mz',
    'mo'=>'mo',
    'cas_z'=>'cas_z',
    'ry'=>'ry',
    'dl'=>'dl',
    'sm'=>'sm',
    'cesta'=>'cesta',
  ];


  public function actionImport(int $id_oblast = 0) {
    $out=[];$id=1;
    /*if (($handle = fopen("data/mala.csv", "r")) !== FALSE) {
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
      $kluc = $this->prvky_kluc->findOneBy(['key' => $v['kluc'] == "NK" ? "ND" : $v['kluc']]);
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
    }*/

    /*if (($handle = fopen("data/Malavl.csv", "r")) !== FALSE) {
      while (($data = fgetcsv($handle, 1500, " ")) !== FALSE) {
        $temp = ['id'=>$id];$i = 0;
        foreach ($this->tmp_cesta as $k => $v) {
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
      $this->oblast_cesty->pridaj([
        'id_oblast' => $id_oblast,
        'zc' => $v['zc'], //A
        'kc' => $v['kc'], //B
        'typ' => $v['typ'], //C
        'vyhybky' => $v['vyh'] //D
      ]);
    }*/
    if (($handle = fopen("data/malagvd1.csv", "r")) !== FALSE) {
      while (($data = fgetcsv($handle, 1500, ";")) !== FALSE) {
        $temp = ['id'=>$id];$i = 0;
        foreach ($this->tmp_vlaky as $k => $v) {
          $temp[$k] = $data[$i];
          $i++;
        }
        $out[] = $temp;
      }
      $id++;
      fclose($handle);
    }
    foreach ($out as $v) {
      $this->vlaky->pridaj([
        'id_oblast' => $id_oblast,
        'cislo' => $v['cis1'], //A
        'cislo_p' => $v['cis2'], //B
        'mz' => $v['mz'], //C
        'mo' => $v['mo'], //D
        'cas_z'=> $v['cas_z'],
        'ry'=> $v['ry'],
        'dl'=> $v['dl'],
        'sm'=> $v['sm'],
        'cesta'=> trim($v['cesta']),
      ]);
    }
    //dumpe($out);
    $this->flashRedirect('default', 'OK', 'alert-success');
  } 
}