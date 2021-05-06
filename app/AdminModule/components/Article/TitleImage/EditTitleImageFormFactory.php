<?php
declare(strict_types=1);

namespace App\AdminModule\Components\Article\TitleImage;

use App\Model;
use Nette\Application\UI\Form;
use Nette\Utils\Html;

/**
 * Formular a jeho spracovanie pre pridanie a editaciu titulneho obrazku polozky.
 * Posledna zmena 14.04.2021
 * 
 * @author     Ing. Peter VOJTECH ml. <petak23@gmail.com>
 * @copyright  Copyright (c) 2012 - 2021 Ing. Peter VOJTECH ml.
 * @license
 * @link       http://petak23.echo-msz.eu
 * @version    1.0.7
 */
class EditTitleImageFormFactory {
  /** @var Model\Hlavne_menu */
	private $hlavne_menu;
  /** @var string */
  private $dir_to_menu;
  /** @var string */
  private $www_dir;
  /**
   * @param string $dir_to_menu Adresar pr ukladanie obrazkov menu - Nastavenie priamo cez servises.neon
   * @param string $www_dir WWW adresar - Nastavenie priamo cez servises.neon
   * @param Model\Hlavne_menu $hlavne_menu */
  public function __construct(string $dir_to_menu, string $www_dir, Model\Hlavne_menu $hlavne_menu) {
		$this->hlavne_menu = $hlavne_menu;
    $this->dir_to_menu = $dir_to_menu;
    $this->www_dir = $www_dir;
	}
  
  /**
   * Formular pre pridanie a editaciu titulneho obrazku polozky.
   * @return Form */  
  public function create(): Form {
    $form = new Form();
		$form->addProtection();
    $form->addHidden("id");
    $form->addHidden("old_avatar");
    $form->addGroup("Zvoľ čo ideš meniť:");
    $form->addRadioList('vyber', 'Zmeň:', [1=>"Ikonku", 2=>"Obrázok"])
         ->addCondition(Form::EQUAL, 1)
          ->toggle("view_ikonka")
         ->endCondition()
         ->addCondition(Form::EQUAL, 2)
          ->toggle("view_avatar");
    $form->addGroup("Obrázok")->setOption('container', Html::el('fieldset id=view_avatar'));
		$form->addUpload('avatar', 'Titulný obrázok')
         ->setOption('description', sprintf('Max veľkosť obrázka v bytoch %d kB', 1024 * 1024/1000 /* v bytoch */))
         ->setRequired(FALSE)
         ->setHtmlAttribute('accept', 'image/*')
         ->addRule(Form::MAX_FILE_SIZE, 'Max veľkosť obrázka v bytoch %d B', 1024 * 1024 /* v bytoch */)
           ->addRule(Form::IMAGE, 'Titulný obrázok musí byť JPEG, PNG alebo GIF.');
    $form->addGroup("Ikonka")->setOption('container', Html::el('fieldset id=view_ikonka'));
    $form->addText('ikonka', 'Názov class ikonky pre FontAwesome:', 0, 30);
    $form->addGroup("");
    $form->addSubmit('uloz', 'Zmeň')
         ->setAttribute('class', 'btn btn-success')
         ->onClick[] = [$this, 'editTitleImageFormSubmitted'];
    $form->addSubmit('cancel', 'Cancel')
         ->setAttribute('class', 'btn btn-default')
         ->setAttribute('data-dismiss', 'modal')
         ->setAttribute('aria-label', 'Close')
         ->setValidationScope([]);
		return $form;
	}
  
  /** 
   * Spracovanie formulara pre zmenu vlastnika clanku.
   * @param \Nette\Forms\Controls\SubmitButton $button Data formulara 
   * @throws Database\DriverException   */
  public function editTitleImageFormSubmitted(\Nette\Forms\Controls\SubmitButton $button) {
    try {
      $this->hlavne_menu->zmenTitleImage($button->getForm()->getValues(), $this->dir_to_menu, $this->www_dir);
		} catch (Database\DriverException $e) {
			$button->addError($e->getMessage());
		}
  }
}