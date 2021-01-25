<?php

namespace App\Presenters;

use App\Model;
use Nette\Utils\Json;

/**
 * Prezenter pre beh simulácie.
 * Posledna zmena(last change): 22.01.2021
 *
 *	Modul: FRONT
 *
 * @author Ing. Peter VOJTECH ml. <petak23@gmail.com>
 * @copyright  Copyright (c) 2021 - 2021 Ing. Peter VOJTECH ml.
 * @license
 * @link       http://petak23.echo-msz.eu
 * @version 1.0.0
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
  
  private $cesty;
  
  private $vvlaky;


  public function actionDefault(int $id = 0) {
    if (($this->aktualna_oblast = $this->oblast->find($id)) === null) {
      $this->flashRedirect("Homepage:", "Požadovanú oblasť som nenašiel!", "warning");
    }
    $this->main_menu = array_merge([["nazov"=>"Ukonč oblasť: ".$this->aktualna_oblast->name, "odkaz"=>"Homepage:"]], $this->main_menu);
    $this->prvky = $this->oblast_prvky->findBy(['id_oblast'=>$id]);
    $this->cesty = $this->oblast_cesty->findBy(['id_oblast'=>$id]);
    $this->vvlaky = $this->vlaky->findBy(['id_oblast'=>$id]);
  }

  public function renderDefault() {
    $this->template->oblast = $this->aktualna_oblast;
    $this->template->prvky = $this->prvky;
    $this->template->jcesty = Json::encode($this->ctoArray($this->cesty));
    $this->template->jprvky = Json::encode($this->rtoArray($this->prvky));
    $this->template->jvlaky = Json::encode($this->vtoArray($this->vvlaky));
  }

  public function vtoArray($vlaky) {
    $out = [];
    foreach($vlaky as $v) {
      $out[$v->id] = [
        'cislo' => $v->cislo,
        'cislo_p' => $v->cislo_p,
        'mz' => $v->mz,
        'mo' => $v->mo,
        'cas_z' => $v->cas_z,
        'ry' => $v->ry,
        'dl' => $v->dl,
        'sm' => $v->sm,
        'cesta' => $v->cesta,
      ];
    }
    return $out;
  }
  
  public function ctoArray($cesty) {
    $out = [];
    foreach($cesty as $c) {
      $out[$c->id] = [
        'typ' => $c->typ,
        'zc' => $c->zc,
        'kc' => $c->kc,
        'vyh' => $c->vyhybky,
      ];
    }
    return $out;
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