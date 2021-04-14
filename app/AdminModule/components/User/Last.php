<?php
declare(strict_types=1);

namespace App\AdminModule\Components\User;

use App\Model;
use Nette;

/**
 * Komponenta pre vypis poslednych prihlasenych clenov
 * Posledna zmena(last change): 14.04.2021
 *
 * @author Ing. Peter VOJTECH ml. <petak23@gmail.com>
 * @copyright  Copyright (c) 2012 - 2021 Ing. Peter VOJTECH ml.
 * @license
 * @link       http://petak23.echo-msz.sk
 * @version 1.0.7
 */
class UserLastControl extends Nette\Application\UI\Control {
	/** @var \Nette\Database\Table\Selection $last Vyber z DB*/
	private $user_prihlasenie;
  /** @var int $pocet Pocet zobrazených poslednych prihlaseni */
	private $pocet = 25;
  
  /** 
   * Konstruktor komponenty
   * @param Model\User_login $user_prihlasenie Vyber z DB */
	public function __construct(Model\User_login $user_prihlasenie) {
		$this->user_prihlasenie = $user_prihlasenie;
	}
  
  /** 
   * Nastavenie poctu poslednych zobrazenych prihlaseni
   * @param int $pocet Pocet poslednych zobrazenych prihlaseni(Min 2) */
  public function setPocet(int $pocet): void {
    if (is_int($pocet) && $pocet > 1) {
      $this->pocet = $pocet;
    }
  }
  
  /** Zakladny render */
	public function render() {
		$this->template->setFile(__DIR__ . '/Last.latte');
		$this->template->h3 = 'Posledných '.$this->pocet.' prihlásení';
		$this->template->last = $this->user_prihlasenie->getLastPr($this->pocet);
		$this->template->render();
	}
  
  /** Signál pre vymazanie zoznamu */
	public function handleZoznamErase() {
    $this->user_prihlasenie->delAll();
    $this->flashMessage('Zoznam vymazaný', 'success');
    if (!$this->presenter->isAjax()) {
      $this->presenter->redirect('this');
    } else {
      $this->redrawControl('llogin');
    }
	}
}

interface IUserLastControl {
  /** @return UserLastControl */
  function create();
}