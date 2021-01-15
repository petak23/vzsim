<?php

namespace App\Presenters;

use App\Model;

/**
 * Presenter pre homepage vo FRONT module
 * 
 * Posledna zmena(last change): 09.10.2020
 *
 *	Modul: FRONT
 *
 * @author Ing. Peter VOJTECH ml. <petak23@gmail.com>
 * @copyright Copyright (c) 2012 - 2020 Ing. Peter VOJTECH ml.
 * @license
 * @link      http://petak23.echo-msz.eu
 * @version 1.0.0
 */
class HomepagePresenter extends BasePresenter {
  /** @var Model\Oblast @inject */
	public $oblast;

  public function startup() {
    parent::startup();
    if (!$this->getUser()->isLoggedIn()) { //Neprihlaseny
      $this->flashRedirect('User:default', "Nie ste prihlásený! Prosím, prihláste sa.", "info");
    }
  }

  public function renderDefault() {
    $this->template->oblast = $this->oblast->findAll();
	}
}
