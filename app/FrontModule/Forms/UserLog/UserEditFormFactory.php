<?php
declare(strict_types=1);

namespace App\FrontModule\Forms\UserLog;

use Language_support;
use Nette\Application\UI\Form;
use Nette\Database\Table;
use Nette\Security;
use Nette\Utils;
use PeterVojtech\News_key;

/**
 * Formular editacie prihlaseneho uzivatela
 * Posledna zmena 14.04.2021
 * 
 * @author     Ing. Peter VOJTECH ml. <petak23@gmail.com>
 * @copyright  Copyright (c) 2012 - 2021 Ing. Peter VOJTECH ml.
 * @license
 * @link       http://petak23.echo-msz.eu
 * @version    1.0.9
 */
class UserEditFormFactory {
  /** @var Language_support\LanguageMain */
  private $texts;
  /** @var \App\Model\User_profiles */
  public $user_profiles;
  /** @var \App\Model\User_main */
  public $user_main;
  /** @var Nette\Security\User */
  public $user;
  /** @var string */
  private $wwwDir;
  /** @var string */
  private $avatar_path;
  /** @var Table\ActiveRow|FALSE */
  private $clen;

  /**
   * @param Security\User $user
   * @param Language_support\LanguageMain $language_main
   * @param \App\Model\User_profiles $user_profiles
   * @param \App\Model\User_main $user_main */
  public function __construct(Security\User $user, 
                              Language_support\LanguageMain $language_main, 
                              \App\Model\User_profiles $user_profiles, 
                              \App\Model\User_main $user_main) {
    $this->user = $user;
    $this->texts = $language_main;
    $this->user_profiles = $user_profiles;
    $this->user_main = $user_main;
	}

  /**
   * Formular pre editaciu prihlaseneho pouzivatela
   * @param array $nastavenie
   * @param string $basePath
   * @param Nette\Database\Table\ActiveRow $clen
   * @return Form  */
  public function create(array $nastavenie, string $basePath, Table\ActiveRow $clen, string $language)  {
    $this->wwwDir = $nastavenie["wwwDir"];
    $this->clen = $clen;
    $this->avatar_path = $nastavenie["dir_to_user"].$this->clen->id."/";
    $this->texts->setLanguage($language);
    $form = new Form();
		$form->addProtection();
    $form->setTranslator($this->texts);
    $form->addHidden('id');
		$form->addText('meno', 'UserEditForm_meno', 30, 50)
				 ->addRule(Form::MIN_LENGTH, 'UserEditForm_meno_ar', 3)
				 ->setRequired('UserEditForm_meno_sr');
    $form->addText('priezvisko', 'UserEditForm_priezvisko', 30, 50)
				 ->addRule(Form::MIN_LENGTH, 'UserEditForm_priezvisko_ar', 3)
				 ->setRequired('UserEditForm_priezvisko_sr');
    $form->addText('email', 'default_email', 30)->setDisabled(TRUE);
    if ($nastavenie['user_view_fields']["pohl"]) {
      $form->addSelect('pohl', 'UserEditForm_pohl',
                     ['M'=>'UserEditForm_m','Z'=>'UserEditForm_z']);
    }
    if ($nastavenie["send_e_mail_news"]) {
      $form->addSelect('news', 'UserEditForm_news',
                       ['A'=>'UserEditForm_news_a','N'=>'UserEditForm_news_n']);
    }
    if ($nastavenie['user_view_fields']["avatar"]) {
      $user_avatar = $this->avatar_path.$this->clen->user_profiles->avatar;
      $form->addUpload('avatar', 'UserEditForm_avatar')
           ->setHtmlAttribute('accept', 'image/*')
           ->setOption('description', Utils\Html::el('p')->setHtml(
              Utils\Html::el('img')->src($basePath."/".(is_file($user_avatar) ? $user_avatar : $nastavenie["dir_to_icons"]."64/figurky_64.png"))->alt('avatar').
              "<br>".$this->texts->trText('default_avatar_txt')))
           ->addCondition(Form::FILLED)
            ->addRule(Form::IMAGE, 'UserEditForm_avatar_oi')
            ->addRule(Form::MAX_FILE_SIZE, 'UserEditForm_avatar_ar', 300 * 1024 /* v bytech */);
    }
		$form->addSubmit('uloz', 'base_save')
         ->setAttribute('class', 'btn btn-success')
         ->onClick[] = [$this, 'userEditFormSubmitted'];
    $form->addSubmit('cancel', 'Cancel')->setAttribute('class', 'btn btn-default')
         ->setValidationScope([]);
		return $form;
	}
  
  /** 
   * Spracovanie formulara
   * @param \Nette\Forms\Controls\SubmitButton $button Data formulara */
	public function userEditFormSubmitted(\Nette\Forms\Controls\SubmitButton $button) {
    $form = $button->getForm();
    $values = $form->getValues(TRUE); 	//Nacitanie hodnot formulara
		$id = $values['id']; // Ak je == 0 tak sa pridava
    $news = isset($values['news']) ? $values['news'] : FALSE;
    $pohl = isset($values['pohl']) ? $values['pohl'] : 'M';
    try {
      $this->_saveAvatar($values['avatar'], $values['id']);
      unset($values['id'], $values['avatar'], $values['news'], $values['pohl']);
      $uloz = $this->user_main->uloz($values, $id);
      $this->user_profiles->uloz(['pohl'=>$pohl], $uloz['id_user_profiles']); 
      if (isset($uloz['id']) && $this->clen->user_profiles->news != $news) { //Ak doslo k zmene v posielani noviniek
        $news_key = new News_key\NewsKeyControl($this->user_profiles, $this->user);
        $news_key->Spracuj($news == "A", $id);
      } 
    } catch (Security\AuthenticationException $e) {
      $button->addError($e->getMessage());
    } catch (Utils\ImageException $e) {
      $form['avatar']->addError($e->getMessage());
      $button->addError();
    }
	}
  
  /**
   * Funkcia pre ulozenie avatara
   * @param \Nette\Http\FileUpload $avatar
   * @param string $id
   * @throws Utils\ImageException */
  protected function _saveAvatar(\Nette\Http\FileUpload $avatar, string $id) {
    if ($avatar->hasFile()) {
      if ($avatar->isImage()){
        $avatar_path = "files/".$id."/";
        $path = $this->wwwDir."/".$avatar_path;
        $pi = pathinfo($avatar->getSanitizedName());
        $ext = $pi['extension'];
        $this->_delUserImages($path);
        $avatar_name = Utils\Random::generate(25).".".$ext;
        $avatar->move($path.$avatar_name);
        $image = Utils\Image::fromFile($path.$avatar_name);
        $image->resize(75, 75, Utils\Image::SHRINK_ONLY);
        $image->save($path.$avatar_name, 90);
        $this->user_profiles->oprav($id, ['avatar'=>$avatar_name]);
      } else {
        throw new Utils\ImageException('user_edit_avatar_err');
      }
    }
  }
  
  /**
   * Zmazanie uzivatelskych avatarov|vytvorenie uzivatelskeho priecinka
   * @param string $path */
  private function _delUserImages(string $path) {
    if (is_dir($path)) {
      foreach (glob("$path*.{jpg,jpeg,gif,png}", GLOB_BRACE) as $file) {
        @unlink($file);
      }
    }	else { mkdir($path, 0777); }
  } 
}