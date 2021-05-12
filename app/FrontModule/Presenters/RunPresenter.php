<?php

namespace App\FrontModule\Presenters;

use App\Model;
use Nette\Utils\Json;

/**
 * Prezenter pre beh simulácie.
 * Posledna zmena(last change): 10.05.2021
 *
 *	Modul: FRONT
 *
 * @author Ing. Peter VOJTECH ml. <petak23@gmail.com>
 * @copyright  Copyright (c) 2021 - 2021 Ing. Peter VOJTECH ml.
 * @license
 * @link       http://petak23.echo-msz.eu
 * @version 1.0.7
 */
class RunPresenter extends BasePresenter {
  
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
  private $jprvky;
  
  private $cesty;
  
  private $vvlaky;

  private $oblast_params = [];

  private $actualTrains;

  private $actualInArea = [];


  public function actionDefault(int $id = 0) {
    if (($this->aktualna_oblast = $this->oblast->find($id)) === null) {
      $this->flashRedirect("Homepage:", "Požadovanú oblasť som nenašiel!", "warning");
    }
    $this->oblast_params = $this->getHttpRequest()->getQuery();
    $this->oblast_params['hour'] *= 60*60*10;                     // Počiatočný čas v desatinách sekúnd x = x * 60 * 60 * 10
    unset($this->oblast_params['_fid']);
    $this->main_menu = array_merge([["nazov"=>"Ukonč oblasť: ".$this->aktualna_oblast->name, "odkaz"=>"Homepage:"]], $this->main_menu);
    $this->prvky = $this->oblast_prvky->findBy(['id_oblast'=>$id]);
    $this->jprvky = $this->rtoArray($this->prvky);
    $this->cesty = $this->oblast_cesty->findBy(['id_oblast'=>$id]);
    $this->vvlaky = $this->vlaky->findBy(['id_oblast'=>$id]);
    $this->actualTrains = $this->getActualTrains($this->oblast_params['hour'] / 600);
    //dumpe($this->actualInArea);
  }  

  public function renderDefault() {
    $this->template->oblast = $this->aktualna_oblast;
    $this->template->prvky = $this->prvky;
    $this->template->oblast_params = $this->oblast_params;
    $this->template->jcesty = Json::encode($this->ctoArray($this->cesty));
    $this->template->jprvky = Json::encode($this->jprvky);
    $this->template->jvlaky = Json::encode($this->vtoArray($this->vvlaky));
    $this->template->jvlaky_act = Json::encode($this->vtoArray($this->actualInArea));
    $this->template->initfronta = Json::encode($this->actualTrains);
  }

  private function getActualTrains(int $minutes) {
    //dump($minutes);
    // Najdi všetky vlaky, ktorých začiatok je pred počiatočným časom
    $av = $this->vlaky->findBy(['id_oblast'=>$this->aktualna_oblast->id, 'cas_z <='.$minutes]);
    $out = [];
    foreach ($av as $k => $v) {
      $cp_cas_p = explode("|", $v->cp_cas_p);    // Čas príchodu z CP
      $cp_cas_o = explode("|", $v->cp_cas_o);    // Čas odchodu z CP
      $cp_miesta = explode("|", $v->cp_miesta);  // Miesta z CP
      $cp_kolaj = explode("|", $v->cp_kolaj);    // Koľaje z CP
      if (end($cp_cas_p) > $minutes) { // Ak je posledný čas z CP viac ako počiatočný čas... vlak je v oblasti
        //dump($v->cislo);dump($minutes);
        for ($i=0; $i < count($cp_cas_p); $i++) {
          //dump($cp_cas_p[$i]);
          //dump($cp_cas_o[$i]);
          //dump($cp_miesta[$i]);
          if ($cp_cas_p[$i] >= $minutes && $cp_miesta[$i] !== $v->mo) {
            //Nájdi správnu KS
            $ks = $this->oblast_prvky->findOneBy(['id_oblast'=>$this->aktualna_oblast->id,
                                                  'id_prvky_kluc'=>14,    // KS
                                                  'c2'=>$cp_miesta[$i],   // Príslušná SB
                                                  'oznacenie'=>$cp_kolaj[$i]]); 
            //dump($ks->xs);
            $this->jprvky[$ks->xs]["c"][3] = $v->cislo;
            $this->jprvky[$ks->xs]["stav"] = 3; // Obsadený
            if ($cp_cas_o[$i] > 0) {
              $mdo = $cp_cas_o[$i] - $minutes;  // Minúty do odchodu
              $this->jprvky[$ks->xs]["stav"] += $mdo > 2 ? 32 : ($mdo > 1 ? 64 : 128);
              $out[] = [
                'xs' => $ks->xs,
                'cas'=> $cp_cas_o[$i] * 600,
                'nst'=> $cp_cas_o[$i] - $minutes > 2 ? 3 : 1,
                'do' => 'zmenPrvok',
                'vlak'=> $v->cislo,
              ];
              $this->actualInArea[] = $v;
            } else if ($cp_cas_o[$i] == -1) {
              /*$out[] = [
                'xs' => $ks->xs,
                'cas'=> $cp_cas_p[$i] * 600,
                'nst'=> 1,
                'do' => 'zmenPrvok',
                'vlak'=> $v->cislo,
              ];*/
              $this->actualInArea[] = $v;
            }
            //dump($this->jprvky[$ks->xs]);
            //dump($out);
            $i = count($cp_cas_p); // Ukonč...
          }
        }
        //$out[] = $v;
      } else if ($v->cislo != $v->cislo_p) { // Vlaky prečíslovávané v oblasti
        $pr = $this->vlaky->findOneBy(['cislo'=>$v->cislo_p]); // Najdi prečíslovaný vlak
        $cp_cas_p = explode("|", $pr->cp_cas_p);    // Čas príchodu z CP
        $cp_cas_o = explode("|", $pr->cp_cas_o);    // Čas odchodu z CP
        $cp_miesta = explode("|", $pr->cp_miesta);  // Miesta z CP
        $cp_kolaj = explode("|", $pr->cp_kolaj);    // Koľaje z CP
        if (end($cp_cas_p) > $minutes) { // Vlaky po prečíslovaní v oblasti
          $tt = explode("|", $v->cp_kolaj);
          //dump(end($tt));
          $ls = $this->oblast_prvky->findOneBy(['id_oblast'=>$this->aktualna_oblast->id,
                                                'id_prvky_kluc'=>14,
                                                'c2'=>$pr->mz,
                                                'oznacenie'=>end($tt)]); //Nájdi správnu KS
          //dump($ls->xs);
          $this->jprvky[$ls->xs]["c"][3] = $pr->cislo;
          $this->jprvky[$ls->xs]["stav"] = 3 + 32; // Obsadený
          //dump($this->jprvky[$ls->xs]);
          $out[] = [
            'xs' => $ls->xs,
            'cas'=> $pr->cas_z - $minutes > 2 ? ($pr->cas_z - 2) * 600 : $pr->cas_z,
            'nst'=> $pr->cas_z - $minutes > 2 ? 67 : ($pr->cas_z - $minutes > 1 ? 131 : 3),
            'do' => 'zmenPrvok',
            'vlak'=> $pr->cislo,
          ];
          $this->actualInArea[] = $v;
        }
        //dump($this->jprvky[$ls->xs]);
      }
    }
    
    //dumpe($out);
    return $out;
  }

  private function vtoArray($vlaky) {
    $out = [];
    foreach($vlaky as $v) {
      $out[] = [
        'id' => $v->id,
        'cislo' => $v->cislo,
        'cislo_p' => $v->cislo_p,
        'mz' => $v->mz,
        'mo' => $v->mo,
        'cas_z' => $v->cas_z,
        'ry' => $v->ry,
        'dl' => $v->dl,
        'sm' => $v->sm,
        'cp_miesta' => $v->cp_miesta,
        'cp_cas_p'  => $v->cp_cas_p,
        'cp_cas_o'  => $v->cp_cas_o,
        'cp_kolaj'  => $v->cp_kolaj,
        'vact' => 0,
      ];
    }
    return $out;
  }
  
  private function ctoArray($cesty) {
    $out = [];
    foreach($cesty as $c) {
      $prvky_cesty = explode('|', $c->prvky_cesty);
      $prvky_odvrat = explode('|', $c->prvky_odvrat);
      $cisvyh = 0;
      $prvky_cesty_upd = [];
      $prvky_odvrat_upd = [];
      foreach($prvky_cesty as $p) {
        if ($this->jprvky[$p]['id_prvky_kluc'] == 16) {
          $prvky_cesty_upd[] = $p."|".$c->vyhybky[$cisvyh];
          if ($this->jprvky[$p]['c'][2] > 0 && in_array($this->jprvky[$p]['c'][2], $prvky_odvrat)) {
            $prvky_odvrat_upd[] = $this->jprvky[$p]['c'][2]."|".$c->vyhybky[$cisvyh];
          }
          $cisvyh++;
        } else {
          $prvky_cesty_upd[] = $p;
        }
      }
      $out[$c->id] = [
        'typ' => $c->typ,
        'zc' => $c->zc,
        'kc' => $c->kc,
        'vyh' => $c->vyhybky,
        'prvky_cesty' => $prvky_cesty_upd,
        'prvky_odvrat' => $prvky_odvrat_upd,    
        'vmax' => $c->vmax
      ];
    }
    //dumpe($out);
    return $out;
  }
  
  private function rtoArray($prvky) {
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
}