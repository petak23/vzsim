<?php

namespace App\Presenters;

use App\Model;

/**
 * Presenter pre homepage vo FRONT module
 * 
 * Posledna zmena(last change): 12.03.2021
 *
 *	Modul: FRONT
 *
 * @author Ing. Peter VOJTECH ml. <petak23@gmail.com>
 * @copyright Copyright (c) 2012 - 2021 Ing. Peter VOJTECH ml.
 * @license
 * @link      http://petak23.echo-msz.eu
 * @version 1.0.1
 */
class HomepagePresenter extends BasePresenter {
  /** @var Model\Oblast @inject */
	public $oblast;

  /** @var int */
  private $aktivna_oblast = 0;

  // -- Forms
  /** @var \App\Forms\Homepage\FirstRunFormFactory @inject*/
  public $firstRunForm;

  public function startup() {
    parent::startup();
    if (!$this->getUser()->isLoggedIn()) { //Neprihlaseny
      $this->flashRedirect('User:default', "Nie ste prihlásený! Prosím, prihláste sa.", "info");
    }
  }

  public function renderDefault() {
    $this->template->oblast = $this->oblast->findAll();
	}

  public function actionOblast(int $id) {
    $this->aktivna_oblast = $this->oblast->find($id);
  }

  public function renderOblast() {
    $this->template->oblast = $this->aktivna_oblast;
  }

  /** 
   * Formular pre prvé spustenie simulácie.
   * @return Nette\Application\UI\Form */
  protected function createComponentFirstRunForm() {
    $form = $this->firstRunForm->create($this->language);
    $form['run']->onClick[] = function ($form) {
      $val = $form->getForm()->getValues();
      $params = [
        'id'=> $this->aktivna_oblast->id,
        'day' => $val->day,
        'hour' => $val->hour,
        'save' => 0,
      ];
      $this->flashOut(!count($form->errors), ['Run:', $params] , '', 'Er');
    };
    return $form;
  }
  
}
