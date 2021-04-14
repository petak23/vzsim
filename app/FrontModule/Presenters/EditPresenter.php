<?php

namespace App\FrontModule\Presenters;

use App\Model;
use Nette\Utils\Json;

/**
 * Prezenter pre editáciu oblasti.
 * Posledná zmena(last change): 14.04.2021
 *
 *	Modul: FRONT
 *
 * @author Ing. Peter VOJTECH ml. <petak23@gmail.com>
 * @copyright  Copyright (c) 2021 - 2021 Ing. Peter VOJTECH ml.
 * @license
 * @link       http://petak23.echo-msz.eu
 * @version 1.0.8
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
  private $cesty;
              //0  1  2  3  4 5 6  7  8  9
  private $dx = [0,-1, 0, 1,-1,0,1,-1, 0, 1]; //posun x
  private $dy = [0, 1, 1, 1, 0,0,0,-1,-1,-1]; //posun y
  private $xmax = 63;
  
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
    $this->template->addFilter('uprav', function ($c){
      $out = "";
      $o = $oc = 0;
      $out .= "C:".floor(($c >> 8) & 15).",".floor(($c >> 4) & 15).",".floor($c & 15);
      return $out;
    });
  }

  public function actionGeneruj(int $id = 0) {
    if (($this->aktualna_oblast = $this->oblast->find($id)) === null) {
      $this->flashRedirect("Homepage:", "Požadovanú oblasť som nenašiel!", "warning");
    }
    $this->prvky = $this->rtoArray($this->oblast_prvky->findBy(['id_oblast'=>$id]));
    $this->cesty = $this->oblast_cesty->findBy(['id_oblast'=>$id]);
    foreach($this->cesty as $cesta) {
      //if ($cesta->prvky_cesty == null)
        $this->test_cesty($cesta);
    }
    $this->flashRedirect(["Edit:", $id], "Vygenerované v poriadku!", "success");
  }

  private function test_cesty($cesta) {
    $cisvyh = 0;                              // Poradové číslo výhybky vrámci cesty
    $pr = $this->prvky[$cesta->zc];           // Aktuálne testovaný prvok cesty
    $sm0 = ($pr['sm'] & 3);                   // Smer cesty
    $prvky_cesty[] = $cesta->zc;              // Pole prvkov cesty
    $prvky_odvrat = [];                       // Pole odvratov
    $prvky_odkaz = [];                        // Pole odkazov pre danú cestu
    $xs = $pr['c'][0];                        // Počiatočné xs z položky csn NH alebo NE
    $cisvch = ($pr['sm'] & 3) == 1 ? 4 : 6;   // Nájdenie čísla "vchodu" do nasledujúcej bunky podľa num. klávesnice
    $rychl = 255;                             // Max. rýchlosť cesty
    $final = 50;                              // Max. počet prvkov cesty
    do {
      $pr = $this->prvky[$xs];                // Do "pr" daj info o aktuálnom prvku
      if (isset($pr)) {
        if ($pr['id_prvky_kluc'] != 4) array_push($prvky_cesty, $pr['xs']); // Ak prvok existuje a nie je UO tak vlož do poľa
        if ($pr['xs'] == $cesta->kc) {        // Je koniec cesty?
          $final = 0;
        } else {                              // Najdi nasledujúci
          switch ($pr['id_prvky_kluc']) {
            case 1: //UB
            case 3: //KB
              $k = ($sm0 == 1) ? ($pr['c'][0] & 15) : ($pr['c'][0] >> 8); // Číslo cesty v smere ku koncu cesty
              $xs += $this->dx[$k] + $this->dy[$k] * $this->xmax; // Nájdi ďaľší prvok
              $cisvch = 10 - $k;                             // Nájdi číslo vchodu pre nasledujúci úsek
              if ($rychl > $pr['n'][1]) $rychl = $pr['n'][1];        // Test maximálnej rýchlosti
              break;
            case 4: //UO
            case 5: //MO
              $xs = $pr['n'][$sm0 - 1];                            // Nájdi ďaľší prvok
              break;
            case 6: //NH
            case 8: //NE
              switch ($sm0) {
                case 1: $xs = $pr['c'][($pr['sm'] & 3)-1]; break;
                case 2: $xs = $pr['c'][2-($pr['sm'] & 3)]; break;
              }
              break;
            case 10: //UP
              $xs += $this->dx[10 - $cisvch];
              if ($rychl > $pr['n'][1]) $rychl = $pr['n'][1];        // Test maximálnej rýchlosti
              break;
            case 14: //KS;
              switch ($cisvch) {
                case 4: $xs +=($pr['c'][0] & 15) + 2; break;
                case 6: $xs -=($pr['c'][1] >> 4) - 2; break;
              }
              if ($rychl > $pr['n'][1]) $rychl = $pr['n'][1];        // Test maximálnej rýchlosti
              break;
            case 16: //VN;
              $pol = $cesta->vyhybky[$cisvyh]; // Poloha výhybky pre danú cestu
              if ($pr['c'][2] > 0) {                           // Existuje odvrat resp. spolupracujúca výh.
                array_push($prvky_odvrat, $pr['c'][2]);
              }
              if ($rychl > $pr['n'][2-$pol]) $rychl = $pr['n'][2-$pol];       // Test maximálnej rýchlosti
              $k = $pr['c'][$pol-1] & 4095;                     // Spočítaj číslo odchodu z prvku (num. klávesnica)
              $k = ($sm0 === 1) ? $k & 15 : $k >> 8;            // ok       
              $xs += $this->dx[$k] + $this->dy[$k] * $this->xmax;   // Nájdenie xs nasledujúceho prvku
              $cisvyh++;
              $cisvch = 10 - $k;
              break;
          }
        }
        $final--;
      } else {
        $final = 0;
        $cislo_cesty = 0;
      }
    }
    while ($final > 0);

    $out = [];                                          // Vypusť z odvratov prvky, ktoré sú súčasť cesty
    foreach($prvky_odvrat as $p) {
      if (!in_array($p, $prvky_cesty)) {
        $out[] = $p;
      }
    }
    $cesta_new = $this->oblast_cesty->oprav($cesta->id, [
      'prvky_cesty' => implode('|', $prvky_cesty),      // Do cesty vlož jej prvky
      'prvky_odvrat' => implode('|', $out),             // Do cesty vlož odvratné výhybky
      'vmax' => $rychl                                  // Do cesty vlož max. rýchlosť
    ]);
    return $cesta_new;
  }

  public function ActionVlaky(int $id) {
    $vla = $this->vlaky->findAll();
    foreach ($vla as $v) {
      $cp = explode(" ", $v->cesta);
      if (count($cp) > 1) {
        $cp_miesta = [];
        $cp_cas = [];
        if (count($cp) % 2 == 0) {
          for ($i=0; $i < count($cp); $i++) { 
            if ($i % 2 == 0) $cp_miesta[] = $cp[$i]; 
            else $cp_cas[] = $cp[$i];
          }
        } else {
          $j = 0;
          for ($i=0; $i < count($cp); $i++) { 
            if ($cp[$i] > 0) {
              if ($j % 2 == 0) $cp_miesta[] = $cp[$i]; 
              else $cp_cas[] = $cp[$i];
              $j++;
            }
          }
        }
        $cp_miesta = implode("|", $cp_miesta);
        $cp_cas = implode("|", $cp_cas);
        $this->vlaky->oprav($v->id, [
          "cp_miesta" => $cp_miesta,
          "cp_cas"    => $cp_cas,
        ]);
      }
    }

    $this->flashRedirect(["Edit:", $id], "Vygenerované v poriadku!", "success");
  }

  public function rtoArray($prvky) {
    $out = [];
    foreach($prvky as $p) {
      $out[$p->xs] = [
        'id_prvky_kluc' => $p->id_prvky_kluc,
        'xs' => $p->xs,
        'y' => $p->y,
        'c' => [$p->c0, $p->c1, $p->c2, $p->c3],
        'dl' => $p->dl,
        'n' => [$p->n0, $p->n1],
        'sm' => $p->sm,
        'ts' => $p->ts,
        'rezim' => $p->rezim,
        'odk' => $p->odk,
        'oznacenie' => $p->oznacenie,
        'stav' => 0,
        'key' => $p->prvky_kluc->key
      ];
      if ($p->id_prvky_kluc == 14) {   //Pre KS dopln KO
        for ($i = 1; $i <= (($p->c0 >> 4)+1); $i++) { //Smer 1 (->)
          $out[($p->xs - $i)] = [
            'id_prvky_kluc' => 22,
            'xs' => $p->xs - $i,
            'y' => 0,
            'c' => [$p->xs, 0, 0, 0],
            'dl' => 0,
            'n' => [0, 0],
            'sm' => 0,
            'ts' => 0,
            'rezim' => 0,
            'odk' => 0,
            'oznacenie' => '',
            'stav' => 0,
            'key' => 'KO'
          ];
        }
        for ($i = 1; $i <= (($p->c0 & 15)+1); $i++) { //Smer 2 (<-)
          $out[($p->xs + $i)] = [
            'id_prvky_kluc' => 22,
            'xs' => $p->xs + $i,
            'y' => 0,
            'c' => [$p->xs, 0, 0, 0],
            'dl' => 0,
            'n' => [0, 0],
            'sm' => 0,
            'ts' => 0,
            'rezim' => 0,
            'odk' => 0,
            'oznacenie' => '',
            'stav' => 0,
            'key' => 'KO'
          ];
        }
      }           
    }
//    dumpe($out);
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