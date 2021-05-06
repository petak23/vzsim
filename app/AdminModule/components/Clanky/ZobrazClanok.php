<?php
declare(strict_types=1);

namespace App\AdminModule\Components\Clanky;

use App\Model;
use Nette\Application\UI\Control;
use Nette\Utils\Html;
use Texy;
/**
 * Komponenta pre zobrazenie konkretneho clanku
 * Posledna zmena(last change): 14.04.2021
 *
 * @author Ing. Peter VOJTECH ml <petak23@gmail.com>
 * @copyright Copyright (c) 2012 - 2021 Ing. Peter VOJTECH ml.
 * @license
 * @link http://petak23.echo-msz.eu
 * @version 1.1.6
 */
class ZobrazClanokAControl extends Control {
  /** @var Model\Hlavne_menu_lang */
	public $hlavne_menu_lang;
  /** @var int */
  protected $id_hlavne_menu;
  /** @var boolean $zobraz_anotaciu Zobrazenie anotacie polozky*/
  private $zobraz_anotaciu;
  /** @var Texy\Texy */
	public $texy;
  
  /**
   * @param bool $zobraz_anotaciu Povolenie zobrazenia anotacie - Nastavenie priamo cez servises.neon
   * @param Model\Hlavne_menu_lang $hlavne_menu_lang
   * @param Texy\Texy $texy */   
  public function __construct(bool $zobraz_anotaciu, Model\Hlavne_menu_lang $hlavne_menu_lang, Texy\Texy $texy) {
    $this->zobraz_anotaciu = $zobraz_anotaciu;
    $this->hlavne_menu_lang = $hlavne_menu_lang;
    $this->texy = $texy;
  }
  
  /** 
   * Nastavenie komponenty
   * @param int $id_hlavne_menu 
   * @return $this */
  public function setZobraz(int $id_hlavne_menu): ZobrazClanokAControl {
    $this->id_hlavne_menu = $id_hlavne_menu;
    return $this;
  }
  
  /** Render */
  public function render() {
    $this->template->setFile(__DIR__ . "/ZobrazClanok.latte");
    $this->template->cl_texts = $this->hlavne_menu_lang->findBy(["id_hlavne_menu"=> $this->id_hlavne_menu]);
    $this->template->zobraz_anotaciu = $this->zobraz_anotaciu;
    $servise = $this;
    $this->template->addFilter('obr_v_txt', function ($text) use($servise){
      $rozloz = explode("#", $text);
      $serv = $servise->presenter;
      $vysledok = '';
      $cesta = 'http://'.$serv->nazov_stranky."/";
      foreach ($rozloz as $k=>$cast) {
        if (substr($cast, 0, 2) == "I-") {
          $obr = $serv->dokumenty->find((int)substr($cast, 2));
          if ($obr !== FALSE) {
            $cast = Html::el('img class="jslghtbx-thmb img-rounded noajax"')->src($cesta.$obr->thumb_file)
                    ->alt($obr->name)->addAttributes([ 'data-jslghtbx' => $cesta.$obr->main_file, 'data-ajax'=>'false', 'data-jslghtbx-group'=>"mygroup1"]);
          }
        }
        $vysledok .= $cast;
      }
      return $vysledok;
    });
    $this->template->addFilter('koncova_znacka', function ($text) use($servise){
      $rozloz = explode("{end}", $text);
      $vysledok = $text;
      if (count($rozloz)>1) {    //Ak som nasiel znacku
        $vysledok = $rozloz[0].Html::el('a class="cely_clanok"')->href($servise->link("this"))->title("Zobrazenie celého článku")
                ->setHtml('&gt;&gt;&gt; viac').'<div class="ostatok">'.$rozloz[1].'</div>';
      }
      return $vysledok;
    });
    
    $this->texy->allowedTags = TRUE;
    $this->texy->headingModule->balancing = "FIXED";
    $this->template->addFilter('texy', [$this->texy, 'process']);
    $this->template->render();
  }
}

interface IZobrazClanokAControl {
  /** @return ZobrazClanokAControl */
  function create();
}