<?php
declare(strict_types=1);

namespace App\Model;

use Nette;
use Nette\Security;


/**
 * Model starajuci sa o uzivatela
 * 
 * Posledna zmena(last change): 06.10.2020
 * 
 * @author     Ing. Peter VOJTECH ml. <petak23@gmail.com>
 * @copyright  Copyright (c) 2012 - 2020 Ing. Peter VOJTECH ml.
 * @license
 * @link       http://petak23.echo-msz.eu
 * @version    1.0.7
 */
class UserManager implements Security\IAuthenticator {
	use Nette\SmartObject;

	const
    // Mandatory columns for table user_main
		COLUMN_ID = 'id',
    COLUMN_ID_USER_ROLES = 'id_user_roles',
    COLUMN_ID_USER_PROFILES = 'id_user_profiles',
    COLUMN_PASSWORD_HASH = 'password',
		COLUMN_MENO = 'meno',
    COLUMN_PRIEZVISKO = 'priezvisko',
		COLUMN_EMAIL = 'email',
    COLUMN_ACTIVATED = 'activated',
    COLUMN_BANNED = 'banned',
    COLUMN_BAN_REASON = 'ban_reason',
    COLUMN_CREATED = 'created',
    // Mandatory columns for table user_profiles
    // Mandatory columns for table user_roles
  	COLUMN_ROLE = 'role';

  /** @var User_main */
  private $user_main;
  /** @var User_profiles */
  private $user_profiles;
  /** @var User_login */
  private $user_login;
  /** @var Nette\Http\Request */
  private $httpres;
  /** @var Security\Passwords */
  private $passwords;
  
  /**
   * @param User_main $user_main
   * @param User_profiles $user_profiles
   * @param User_login $user_login
   * @param Nette\Http\Request $httpres
   * @param Security\Passwords $passwords */
  public function __construct(User_main $user_main, 
                              User_profiles $user_profiles, 
                              User_login $user_login, 
                              Nette\Http\Request $httpres,
                              Security\Passwords $passwords) {
    $this->user_main = $user_main;
    $this->user_profiles = $user_profiles;
    $this->user_login = $user_login;
    $this->httpres = $httpres;
    $this->passwords = $passwords;
	}

	/**
	 * Performs an authentication.
   * @param array $credentials
	 * @return Nette\Security\Identity
	 * @throws Nette\Security\AuthenticationException 
   *  IDENTITY_NOT_FOUND = 1
   *  INVALID_CREDENTIAL = 2
   *  FAILURE = 3
   *  NOT_APPROVED = 4 */
	public function authenticate(array $credentials): Security\IIdentity {
		[$email, $password] = $credentials;
    
    $row = $this->user_main->findOneBy([self::COLUMN_EMAIL => $email]);
		if (!$row) {
			throw new Security\AuthenticationException("The email '$email' is incorrect. Užívateľský email '$email' nie je správny!", self::IDENTITY_NOT_FOUND);
    } elseif (!$row[self::COLUMN_ACTIVATED]) {
			throw new Security\AuthenticationException("User with email '$email' not activated. Užívateľ s email-om '$email' ešte nie je aktivovaný!", self::FAILURE);
		} elseif ($row[self::COLUMN_BANNED]) {
			throw new Security\AuthenticationException("User with email '$email' is banned! Because: ".$row[self::COLUMN_BAN_REASON].". Užívateľ s email-om '$email' je blokovaný! Lebo: ".$row[self::COLUMN_BAN_REASON], self::FAILURE);
		} elseif (!$this->passwords->verify($password, $row[self::COLUMN_PASSWORD_HASH])) {
			throw new Security\AuthenticationException('Invalid email or password. Chybné užívateľský email alebo heslo!', self::INVALID_CREDENTIAL);
		} elseif ($this->passwords->needsRehash($row[self::COLUMN_PASSWORD_HASH])) {
			$row->update([
				self::COLUMN_PASSWORD_HASH => Security\Passwords::hash($password),
			]);
		}
    $role = $row->user_roles->{self::COLUMN_ROLE};
		$arr = $row->toArray();
		unset($arr[self::COLUMN_PASSWORD_HASH], $password);
    $this->user_profiles->updateAfterLogIn($arr['id_user_profiles']);
    $this->user_main->logLastIp($row[self::COLUMN_ID], $this->httpres->getRemoteAddress());
    $this->user_login->addLogIn($row[self::COLUMN_ID]);
		return new Security\Identity($row[self::COLUMN_ID], $role, $arr);
	}
}
