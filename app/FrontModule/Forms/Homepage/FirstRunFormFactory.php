<?php

declare(strict_types=1);

namespace App\FrontModule\Forms\Homepage;

use Language_support;
use Nette\Application\UI\Form;

/**
 * First run simulation form
 * Last change 14.04.2021
 * 
 * @author     Ing. Peter VOJTECH ml. <petak23@gmail.com>
 * @copyright  Copyright (c) 2012 - 2021 Ing. Peter VOJTECH ml.
 * @license
 * @link       http://petak23.echo-msz.eu
 * @version    1.0.1
 */
class FirstRunFormFactory {

  /** @var Language_support\LanguageMain */
  private $texts;

  /**
   * @param Language_support\LanguageMain $language_main */
  public function __construct(Language_support\LanguageMain $language_main) {
    $this->texts = $language_main;
	}
  
  /**
   * @return string */
  public function getTexts() {
    return $this->texts;
  }
  
  /**
   * Prihlasovací formulár
   * @var string $language Skratka aktuálneho jazyka
   * @return Form */
  public function create($language): Form  {
    $form = new Form();
		$form->addProtection();
    //$this->texts->setLanguage($language);
    //$form->setTranslator($this->texts);
    $den = [
      1 => 'Pondelok',
      2 => 'Utorok',
      3 => 'Streda',
      4 => 'Štvrtok',
      5 => 'Piatok',
      6 => 'Sobota',
      7 => 'Nedeľa'
    ];
    $form->addSelect('day', 'Počiatočný deň v týždni:', $den)
	        ->setDefaultValue(1);
    $form->addInteger('hour', 'Začiatočná hodina:')
          ->setDefaultValue(6)
          ->addRule($form::RANGE, 'Hodina musí byť v rozsahu od %d a %d.', [0, 23]);
    $form->addSubmit('run', 'Spusť')
          ->setAttribute('class', 'btn btn-success')
          ->onClick[] = [$this, 'firstRunFormSubmitted'];
    
    $renderer = $form->getRenderer();
    $renderer->wrappers['controls']['container'] = null;//'div class=sign-in-form';
    $renderer->wrappers['pair']['container'] = 'div class="form-group row justify-content-left m-1"';
    $renderer->wrappers['pair']['.error'] = 'has-danger';
    $renderer->wrappers['control']['container'] = 'div class="col-12 col-sm-6"'; // 
    $renderer->wrappers['label']['container'] = 'div class="col-12 col-sm-6 col-form-label"';
    $renderer->wrappers['control']['description'] = 'span class=form-text';
    $renderer->wrappers['control']['errorcontainer'] = 'span class=form-control-feedback';
    $renderer->wrappers['control']['.error'] = 'is-invalid';

    foreach ($form->getControls() as $control) {
      $type = $control->getOption('type');
      if (in_array($type, ['text', 'textarea', 'select'], true)) {
        $control->getControlPrototype()->addClass('form-control');
      } elseif (in_array($type, ['checkbox', 'radio'], true)) {
        if ($control instanceof \Nette\Forms\Controls\Checkbox) {
          $control->getLabelPrototype()->addClass('form-check-label');
        } else {
          $control->getItemLabelPrototype()->addClass('form-check-label');
        }
        $control->getControlPrototype()->addClass('form-check-input');
        $control->getSeparatorPrototype()->setName('div')->addClass('form-check');
      }
    }

		return $form;
	}
  
  /** 
   * Overenie po prihlásení
   * @param \Nette\Forms\Controls\SubmitButton $button Data formulára */
	public function firstRunFormSubmitted(\Nette\Forms\Controls\SubmitButton $button) {
    $values = $button->getForm()->getValues();
    try {
      // ... 
		} catch (Security\AuthenticationException $e) {
      $button->addError($e->getCode());
    }
	}
}