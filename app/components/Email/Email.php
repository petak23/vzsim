<?php
namespace PeterVojtech\Email;

use App\Model;
use Nette\Mail\Message;
use Nette\Mail\SendmailMailer;
use Nette\Application\UI;
use Latte;

/**
 * Komponenta pre zjedndusenie odoslania emailu
 * Posledna zmena(last change): 14.04.2021
 * 
 * @author Ing. Peter VOJTECH ml. <petak23@gmail.com> 
 * @copyright  Copyright (c) 2012 - 2021 Ing. Peter VOJTECH ml.
 * @license
 * @link       http://petak23.echo-msz.eu
 * @version 1.0.9
 */

class EmailControl extends UI\Control {
  
  /** @var Nette\Mail\Message */
  private $mail;
  /** @var string */
  private $email_list;
  /** @var string */
  private $template;
  /** @var string */
  private $from;
  /** @var Model\User_main */
  public $user_main;

  /**
   * @param Model\User_main $user_main */
  public function __construct(Model\User_main $user_main) {
    $this->mail = new Message;
    $this->user_main = $user_main;
  }
  
  /** 
   * @param string $template Kompletná cesta k súboru template
   * @param type $from Od koho
   * @param type $id_user_roles Minimalna uroven registracie
   * @return \PeterVojtech\Email\EmailControl */
  public function nastav($template, $from, $id_user_roles) {
    $this->template = $template;
    $this->from = $this->user_main->find($from)->email;
    $this->email_list = $this->user_main->emailUsersListStr($id_user_roles);
    foreach (explode(",", $this->email_list) as $c) {
      $this->mail->addTo(trim($c));
    }
    return $this;
  }
  
  /** Funkcia pre odoslanie emailu
   * @param array $params Parametre správy
   * @param string $subjekt Subjekt emailu
   * @return string Zoznam komu bol odoslany email
   * @throws SendException
   */
  public function send($params, $subjekt) {
    $templ = new Latte\Engine;
    $this->mail->setFrom($params["site_name"].' <'.  $this->from.'>');
    $this->mail->setSubject($subjekt)
         ->setHtmlBody($templ->renderToString($this->template, $params));
    try {
      $sendmail = new SendmailMailer;
      $sendmail->send($this->mail);
      return $this->email_list;
    } catch (Exception $e) {
      throw new SendException('Došlo k chybe pri odosielaní e-mailu. Skúste neskôr znovu...'.$e->getMessage());
    }
  }
}

interface IEmailControl {
  /** @return EmailControl */
  function create();
}