<?php
namespace App\AdminModule\Presenters;

use App\AdminModule\Forms\Slider;
use App\Model;
use Ublaboo\DataGrid\DataGrid;
use Ublaboo\DataGrid\Localization\SimpleTranslator;
use PeterVojtech;
/**
 * Prezenter pre administraciu slider-u.
 * 
 * Posledna zmena(last change): 14.04.2021
 *
 * Modul: ADMIN
 *
 * @author Ing. Peter VOJTECH ml. <petak23@gmail.com>
 * @copyright  Copyright (c) 2012 - 2021 Ing. Peter VOJTECH ml.
 * @license
 * @link       http://petak23.echo-msz.eu
 * @version 1.1.3
 */

class SliderPresenter extends BasePresenter {
  // -- DB
  /** @var Model\Slider @inject */
  public $slider;

  /** @var array Nastavenie slider-u */
  private $slider_i;
  
  // -- Forms
  /** @var Slider\EditSliderFormFactory @inject*/
	public $editSliderForm;
  
  protected function startup() {
		parent::startup();
		if (isset($this->nastavenie['slider'])) {
			$this->slider_i = $this->nastavenie['slider'];
      $this->template->slider_i = $this->slider_i;
		}
	}

  /** Akcia pre pridanie položky slideru */
	public function actionAdd() {
    $this["sliderEditForm"]->setDefaults(['poradie' => $this->slider->getNextCounter()]);
    $this->setView('edit');
	}

  /**
   * Akcia pre editaciu polozky slider-u
   * @param int $id id editovanej polozky
   */
	public function actionEdit($id) {
    if (($pol_slider = $this->slider->find($id)) === FALSE) {
      $this->setView('notFound');
		} else {
      $this->template->sucasny = $pol_slider;
			$this["sliderEditForm"]->setDefaults($pol_slider);
      $this["sliderEditForm"]->setDefaults(['zobrazenie_null' => ($pol_slider->zobrazenie==NULL) ? 1 : 0,
                                            'zobrazenie_1'      => ($pol_slider->zobrazenie==NULL) ? NULL : explode(',', $pol_slider->zobrazenie),
                                           ]);
    }
	}
  
  public function createComponentSliderGrid($name) {
		$grid = new DataGrid($this, $name);

		$grid->setDataSource($this->slider->findAll());
    $grid->setTemplateFile(__DIR__ . '/../templates/Slider/custom_datagrid_template.latte');
    $grid->setSortable();
    $grid->setDefaultSort(['poradie' => 'ASC']);
    $grid->addColumnText('subor', 'Subor')
         ->setSortable()
         ->setTemplate(__DIR__ . '/../templates/Slider/grid.subor.latte', 
                       ['popis' => FALSE, 
                        'slider_dir' => $this->nastavenie['slider']['dir'],
                        'dir_to_images' => $this->nastavenie['dir_to_images'],
                       ]);
    $grid->addColumnText('subor1', 'Subor', 'subor');
		$grid->addColumnText('poradie', 'Poradie')->setDefaultHide();
    $grid->addColumnText('nadpis', 'Nadpis')->setSortable()
          ->setRenderer(function($item) {
            return ($item->nadpis !== NULL ? $item->nadpis : 'Bez nadpisu');
          });
    $grid->addColumnText('zobrazenie', 'Zobrazenie')
          ->setRenderer(function($item) {
            return ($item->zobrazenie !== NULL ? $item->zobrazenie : 'Vždy');
          });
    $grid->addAction('edit', '')
         ->setIcon('pencil-alt fa-2x')
         ->setClass('btn btn-xs btn-default edit-ikon')
         ->setTitle('Editácia položky');
    $grid->addAction('delete', '', 'confirmDeleteSliderForm:confirmDelete!')
         ->setIcon('trash-alt fa-2x')
         ->setClass('btn btn-xs btn-default del-ikon')
         ->setTitle('Vymazanie položky');
    
    $translator = new SimpleTranslator([
      'ublaboo_datagrid.no_item_found_reset' => 'Žiadné položky neboli nájdené. Filter môžete vynulovať...',
      'ublaboo_datagrid.no_item_found' => 'Žiadné položky neboli nájdené.',
      'ublaboo_datagrid.here' => 'tu',
      'ublaboo_datagrid.items' => 'Položky',
      'ublaboo_datagrid.all' => 'všetky',
      'ublaboo_datagrid.from' => 'z',
      'ublaboo_datagrid.reset_filter' => 'Resetovať filter',
      'ublaboo_datagrid.group_actions' => 'Hromadné akcie',
      'ublaboo_datagrid.show_all_columns' => 'Zobraziť všetky stĺpce',
      'ublaboo_datagrid.hide_column' => 'Skryť stĺpec',
      'ublaboo_datagrid.action' => 'Akcia',
      'ublaboo_datagrid.previous' => 'Predošlá',
      'ublaboo_datagrid.next' => 'Daľšia',
      'ublaboo_datagrid.choose' => 'Vyberte',
      'ublaboo_datagrid.execute' => 'Vykonať',
      'ublaboo_datagrid.short' => 'Usporiadaj',
    ]);
    $grid->setTranslator($translator);
    $grid->setRememberState(false);
	}

	/** Edit Slider form component factory for admin.
	 * @return Nette\Application\UI\Form
	 */
	public function createComponentSliderEditForm() {
    $form = $this->editSliderForm->create($this->nastavenie, $this->getComponent('menu'));  
    $form['uloz']->onClick[] = function ($button) {
      $this->flashOut(!count($button->errors), 'Slider:', 'Položka bola uložená!', 'Došlo k chybe a položka sa neuložila. Skúste neskôr znovu...');
		};
    $form['cancel']->onClick[] = function () {
			$this->redirect('Slider:');
		};
		return $this->_vzhladForm($form);
	}

  /**
   * Komponenta Confirmation Dialog pre delete News
   * @return Nette\Application\UI\Form */
  public function createComponentConfirmDeleteSliderForm() {
    $form = new PeterVojtech\Confirm\ConfirmationDialog($this->getSession('news'));
    $form->addConfirmer(
        'delete', // názov signálu bude confirmDelete!
        [$this, 'confirmedDeleteSlider'], // callback na funkciu pri kliknutí na YES
        [$this, 'questionDeleteSlider'] // otázka
    );
    return $form;
  }
  
  /**
   * Zostavenie otázky pre ConfDialog s parametrom
   * @param Nette\Utils\Html $dialog
   * @param array $params
   * @return string $question */
  public function questionDeleteSlider($dialog, $params) {
     $dialog->getQuestionPrototype();
     $temp_del = $this->slider->find($params['id']);
     return sprintf("Naozaj chceš zmazať obrázok '%s'?", $temp_del->subor);
  }
  
  /** 
   * Spracovanie signálu vymazavania
	 * @param int $id - id polozky slideru */
	function confirmedDeleteSlider($id)	{
    $temp_del = $this->slider->find($id);
    if (is_file($this->nastavenie['slider']['dir'].$temp_del->subor)) { 
      unlink($this->nastavenie["wwwDir"].'/'.$this->nastavenie['slider']['dir'].$temp_del->subor); 
    }
    $this->_ifMessage($temp_del->delete() == 1, 'Položka bola úspešne vymazaná!', 'Došlo k chybe a položka nebola vymazaná!');
    if ($this->isAjax()) {
      $this->redrawControl('flashes');
      $this['sliderGrid']->reload();
    } else {
      $this->redirect('Slider:');
    }
  }
  
  /**
   * Spracovanie signalu pri zmene poradia
   * @param int $item_id Prvok, ktoreho poradie sa meni
   * @param int $prev_id Za ktory prvok sa vklada
   * @param int $next_id Pred ktory prvok sa vklada */
  public function handleSort($item_id, $prev_id, $next_id) {
    $this->slider->sortItem($item_id, $prev_id, $next_id);
//    $this->flashMessage("Poradie bolo zmenené."/*"Id: $item_id, Previous id: $prev_id, Next id: $next_id"*/, 'success');
    $this->redrawControl('flashes');
    $this['sliderGrid']->redrawControl();
  }
}	