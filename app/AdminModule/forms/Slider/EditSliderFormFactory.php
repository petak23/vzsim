<?php

declare(strict_types=1);

namespace App\AdminModule\Forms\Slider;

use App\Model;
use Nette\Application\UI\Form;
use Nette\Database;
use Nette\Utils\Html;
use Nette\Utils\Image;

/**
 * Komponenta pre formular slidera 
 * 
 * Posledna zmena(last change): 14.04.2021
 *
 * @author Ing. Peter VOJTECH ml. <petak23@gmail.com> 
 * @copyright Copyright (c) 2012 - 2021 Ing. Peter VOJTECH ml.
 * @license
 * @link http://petak23.echo-msz.eu
 * @version 1.0.6
 */
class EditSliderFormFactory {
  /** @var Model\Slider */
  private $slider;
  /** @var Model\Hlavne_menu */
  private $hlavne_menu;
  /** @var array Pole nastaveni slidera*/
  private $slider_i = [
      'x' => 10,                  //X rozmer obrazka
      'y' => 10,                  //Y rozmer obrazka
      'varianta' => 0,            //Varianta slidera
      'odkaz' => false,           //Ci je obrazok slidera aj ako odkaz
      'dir'=>"www/files/slider/", //Adresar pre ulozenie obrazkov slidera od wwwDir
      ];    
  /** @var string */
  private $wwwDir;
  
  /**
   * @param Model\Slider $slider
   * @param Model\Hlavne_menu $hlavne_menu */
  public function __construct(Model\Slider $slider, Model\Hlavne_menu $hlavne_menu) {
		$this->slider = $slider;
    $this->hlavne_menu = $hlavne_menu;
	}
  
  /**
   * Edit hlavne menu form component factory.
   * @param array $nastavenie
   * @param type $menu
   * @return Form */
  public function create(array $nastavenie, $menu): Form  {
    $this->slider_i = array_merge($this->slider_i, $nastavenie['slider']);
    $this->wwwDir = $nastavenie['wwwDir'];
    $form = new Form();
		$form->addProtection();
    $form->addHidden('id');$form->addHidden('poradie');
    $form->addGroup();
    $form->addUpload('subor', 'Obrázok slideru')
				 ->setOption('description', 'Odporúčaný rozmer obrázku je: '.$this->slider_i['x'].'x'.$this->slider_i['y'].' alebo násobky tejto veľkosti. Inak môže dôjsť k deformácii alebo orezaniu obrázku pri ukladaní!' )
				 ->addCondition(Form::FILLED)
					->addRule(Form::IMAGE, 'Obrázok musí byť JPEG, PNG alebo GIF.')
					->addRule(Form::MAX_FILE_SIZE, 'Maximálna veľkosť súboru je 1 MB.', 1024 * 1024 /* v bytech */);
		$form->addText('nadpis', 'Nadpis:', 50, 50);
		$form->addText('popis', 'Popis:', 50, 150);
    if ($this->slider_i['odkaz']) {
      $form->addText('id_hlavne_menu', 'Id článku pre odkaz:', 5, 5)
           ->setType('number')
           ->addCondition(Form::FILLED)
            ->addRule(Form::INTEGER, 'Id článku musí byť číslo!');
    }
    $form->addCheckbox("zobrazenie_null", " Obrázok sa zobrazí vždy")
         ->setDefaultValue(TRUE)
         ->addCondition(Form::EQUAL, TRUE)
         ->toggle("zobr", FALSE);
    $form->addGroup()->setOption('container', Html::el('fieldset')->id("zobr"));
    $form->addMultiSelect('zobrazenie_1', 'Zobrazenie pre:', $menu->getFullTreeMenu())->setHtmlAttribute('size', 15);
    $form->setCurrentGroup(NULL);
    $form->addSubmit('uloz', 'Ulož')
         ->setAttribute('class', 'btn btn-success')
         ->onClick[] = array($this, 'editSliderFormSubmitted');
    $form->addSubmit('cancel', 'Cancel')->setAttribute('class', 'btn btn-default')
         ->setValidationScope([]);
		return $form;
	}
  
  /** 
   * Spracovanie vstupov z formulara
   * @param \Nette\Forms\Controls\SubmitButton $button Data formulara */
	public function editSliderFormSubmitted(\Nette\Forms\Controls\SubmitButton $button)	{
    $values = $button->getForm()->getValues();
		$data = $this->slider->find($values->id); //Nacitanie editovanej polozky
		if ($values->zobrazenie_null) { 
      $values->zobrazenie = NULL; 
    } else {
      $values->zobrazenie = implode(",", $values->zobrazenie_1);
    }
		unset($values->zobrazenie_null, $values->zobrazenie_1);
    $values->nadpis = strlen($values->nadpis) == 0 ? NULL : $values->nadpis;
    $values->popis = strlen($values->popis) == 0 ? NULL : $values->popis;
    if ($this->slider_i['odkaz']) {
      $values->id_hlavne_menu = (int)$values->id_hlavne_menu > 0 ? (int)$values->id_hlavne_menu : NULL;
      if ($values->id_hlavne_menu !== NULL) { //Kontrola exzistencie id_hlavne_menu
        if ($this->hlavne_menu->find($values->id_hlavne_menu) == FALSE) {
          $button->addError('Zadali ste nesprávne číslo článku. Skúste znovu!');
          return;
        }
      }
    }
    if ($values->subor->hasFile()) {
      if ($values->subor->isImage()) {
        $slider_dir = $this->wwwDir."/".$this->slider_i['dir'];
        $finalFileName = $this->_imageFileName($slider_dir, $values->subor->getSanitizedName());
        $image_name = $slider_dir.$finalFileName;
        $values->subor->move($image_name);
        $image = Image::fromFile($image_name);
        $image->resize($this->slider_i['x'], $this->slider_i['y'], Image::SHRINK_ONLY | Image::EXACT);
        $image->save($image_name, 80);
        if (is_file($this->slider_i['dir'].$data['subor'])) { unlink($slider_dir.$data['subor']); }
				$values->subor = $finalFileName;
      } else {
        $button->addError('Zadali ste nesprávne číslo článku. Skúste znovu!');
				unset($values->subor);
        return;
      }
    } else { 
			unset($values->subor);
		}
    try {
      $this->slider->uloz($values, $values->id);
		} catch (Database\DriverException $e) {
			$button->addError($e->getMessage());
		}
	}
  
  /**
   * @param string $dir
   * @param string $fileName
   * @return string */
  private function _imageFileName(string $dir, string $fileName): string {
    $path_info = pathinfo($fileName);
    $file = $path_info['filename'];
    $ext = $path_info['extension'];
    $additionalToken = 0;
    if (file_exists($dir.$fileName)) {
			do{
					$additionalToken++;
			} while (file_exists($dir.$file.$additionalToken. ".".$ext));
		}
    if ($additionalToken == 0) { $finalFileName = $fileName;
    } else { $finalFileName = $file . $additionalToken . "." . $ext; }
    return $finalFileName;
  }
}