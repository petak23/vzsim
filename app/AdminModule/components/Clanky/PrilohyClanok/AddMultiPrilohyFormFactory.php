<?php
declare(strict_types=1);

namespace App\AdminModule\Components\Clanky\PrilohyClanok;

use App\Model;
use Nette\Application\UI\Form;
use Nette\Database;
use Nette\Security\User;
use Nette\Utils\Strings;
use Nette\Utils\Image;

/**
 * Formular a jeho spracovanie pre pridanie viacerich prilohy polozky.
 * Posledna zmena 14.04.2021
 * 
 * @author     Ing. Peter VOJTECH ml. <petak23@gmail.com>
 * @copyright  Copyright (c) 2012 - 2021 Ing. Peter VOJTECH ml.
 * @license
 * @link       http://petak23.echo-msz.eu
 * @version    1.0.9
 */
class AddMultiPrilohyFormFactory {
  
  /** @var Model\Dokumenty */
	private $dokumenty;
  /** @var string */
  private $prilohy_adresar;
  /** @var array */
  private $prilohy_images;
  /** @var int */
  private $id_user_main;
  /** @var string */
  private $wwwDir;


  /**
   * @param string $prilohy_adresar Cesta k adresaru pre ukladanie priloh od www adresara - Nastavenie priamo cez servises.neon
   * @param array $prilohy_images Nastavenie obrazkov pre prilohy - Nastavenie priamo cez servises.neon
   * @param string $wwwDir WWW adresar - Nastavenie priamo cez servises.neon
   * @param Model\Dokumenty $dokumenty
   * @param User $user
   * @param string $wwwDir  */
  public function __construct(string $prilohy_adresar, array $prilohy_images, string $wwwDir, 
                              Model\Dokumenty $dokumenty, User $user) {
    $this->dokumenty = $dokumenty;
    $this->id_user_main = $user->getId();
    $this->wwwDir = $wwwDir;
    $this->prilohy_adresar = $prilohy_adresar;
    $this->prilohy_images = $prilohy_images;
	}
  
  /**
   * Formular pre pridanie prilohy a editaciu polozky.
   * @return Form  */
  public function create(): Form  {
    //Vypocet max. velkosti suboru pre upload
    $ini_v = trim(ini_get("upload_max_filesize"));
    $s = ['g'=> 1<<30, 'm' => 1<<20, 'k' => 1<<10];
    $upload_size =  intval($ini_v) * ($s[strtolower(substr($ini_v,-1))] ?: 1);
    $form = new Form();
		$form->addProtection();
    $form->addHidden("id_hlavne_menu");$form->addHidden("id_user_roles");
    $form->addRadioList('type', 'Typ prílohy:', [1=>"Iné", 2=>"Obrázok", 3=>"Video"]);
//    $form->addFileUpload("uploader");
    $form->addMultiUpload('priloha', 'Pridaj prílohy')
         ->setOption('description', sprintf('Max veľkosť prílohy v bytoch %s kB.  Maximálny počet nahrávaných obrázkov je %s.', 
                                            $upload_size/1024,
                                            trim(ini_get("max_file_uploads"))))
         ->setRequired("Príloha musí byť vybraná!")
         ->addCondition(Form::FILLED)
          ->addRule(Form::MAX_FILE_SIZE, 'Max veľkosť prílohy v bytoch %d B', $upload_size)
          ->addRule(Form::MAX_LENGTH, 
                    'Max. počet nahrávaných príloh naraz, ktoré dovoľuje server, je %d! Zvolte prosím patričný počet, alebo nahrajte prílohy na viac ráz!',
                    trim(ini_get("max_file_uploads"))
                    )
         ->endCondition()
         ->addConditionOn($form['type'], Form::EQUAL, 2)
          ->addRule(Form::IMAGE, 'Príloha musí byť obrázok!');
		$form->addSubmit('uloz', 'Ulož')
         ->setAttribute('class', 'btn btn-success')
         ->onClick[] = [$this, 'editPrilohaFormSubmitted'];
    $form->addSubmit('cancel', 'Cancel')
         ->setAttribute('class', 'btn btn-default')
         ->setAttribute('data-dismiss', 'modal')
         ->setAttribute('aria-label', 'Close')
         ->setValidationScope([]);
		return $form;
	}
  
  /** 
   * Spracovanie formulara pre pridanie a editaciu prilohy polozky.
   * @param \Nette\Forms\Controls\SubmitButton $button Data formulara 
   * @throws Database\DriverException   */
  public function editPrilohaFormSubmitted(\Nette\Forms\Controls\SubmitButton $button) {
		$values = $button->getForm()->getValues(); 	//Nacitanie hodnot formulara
    try {
      if (count($values->priloha)) {
        $this->_multiUpload($values->priloha, [ 
            'id_hlavne_menu'	 	=> $values->id_hlavne_menu,
            'id_user_main'      => $this->id_user_main,
            'id_user_roles'     => $values->id_user_roles,
            'change'						=> StrFTime("%Y-%m-%d %H:%M:%S", Time()),
        ]);
      } 
		} catch (Database\DriverException $e) {
			$button->addError($e->getMessage());
		}
  }
  
  /** Ulozenie viacerich priloh
   * @param array $prilohy
   * @param array $data
   * @return bool */
  private function _multiUpload(array $prilohy, array $data): bool {
    $vysledok = 0;
    foreach ($prilohy as $vp) {
      $priloha_info = $this->_uploadPriloha($vp);
      if ($this->dokumenty->ulozPrilohu(array_merge($data, $priloha_info), 0) !== FALSE) {  $vysledok++;}
    }
    return (count($prilohy) == $vysledok);
  }

  /**
   * Upload prilohy
   * @param \Nette\Http\FileUpload $priloha
   * @return array */
  private function _uploadPriloha(\Nette\Http\FileUpload $priloha): array {
    $fileName = $priloha->getSanitizedName();
		$pi = pathinfo($fileName);
		$file = $pi['filename'];
		$ext = $pi['extension'];
		$additionalToken = 0;
		//Najdi meno suboru
		if (file_exists($this->prilohy_adresar.$fileName)) {
			do { $additionalToken++;
			} while (file_exists($this->prilohy_adresar.$file.$additionalToken.".".$ext));
    }
		$finalFileName = ($additionalToken == 0) ? $file : $file.$additionalToken;
		//Presun subor na finalne miesto a ak je to obrazok tak vytvor nahlad
		$priloha->move($this->prilohy_adresar.$finalFileName.".". $ext);
		if ($priloha->isImage()) {
			$image_name = $this->prilohy_adresar.$finalFileName.".". $ext;
			$thumb_name = $this->prilohy_adresar.'tb_'.$finalFileName.".". $ext;
			$image = Image::fromFile($image_name);
      $image->resize($this->prilohy_images['x'], $this->prilohy_images['y'], Image::SHRINK_ONLY);
      $image->save($image_name, $this->prilohy_images['kvalita']);
			copy($image_name, $thumb_name);
			$thumb = Image::fromFile($thumb_name);
			$thumb->resize($this->prilohy_images['tx'], $this->prilohy_images['ty'], Image::SHRINK_ONLY); //| Image::EXACT
			$thumb->save($thumb_name, $this->prilohy_images['tkvalita']);
		}

    return [
      'name'				=> $finalFileName,
      'web_name'    => Strings::webalize($finalFileName),
      'pripona'     => $ext,
      'main_file'		=> $this->prilohy_adresar.$finalFileName.".". $ext,
      'thumb_file'	=> isset($thumb_name) ? $thumb_name : NULL,
      'type'        => $priloha->isImage() ? 2 : 1,
      'is_image'    => $priloha->isImage()  
    ];
  }
}