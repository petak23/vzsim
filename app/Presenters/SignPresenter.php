<?php

namespace App\Presenters;

use App\Model;


/**
 * Create user presenters.
 */
class SignPresenter extends BasePresenter {
  
  public function actionCreateUser($username, $password) {
    
    try {
      $this->userManager->add($username, $password);
      $this->flashMessage("User $username was added.", 'success');

    } catch (App\Model\DuplicateNameException $e) {
      $this->flashMessage('Error: duplicate name.', 'danger');
    }
    
    $this->redirect('Homepage:default');
  }

}
