<?php

/**
* @file
* Contains \Drupal\administerusersbyrole\Access\UsersListAccess
*/

namespace Drupal\administerusersbyrole\Access;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Access\AccessCheckInterface;
use Symfony\Component\Routing\Route;
use Symfony\Component\HttpFoundation\Request;
use Drupal\Core\Session\AccountInterface;

/**
 * Access check for panel administration.
 */

class UsersListAccess implements AccessCheckInterface {
  /**
   * {@inheritdoc}
   */
  public function applies(Route $route) {
    return array_key_exists('_access_administerusersbyrole_users_list', $route->getRequirements());
  }

  /**
   * {@inheritdoc}
   */
  public function access(Route $route, Request $request, AccountInterface $account) {

    $permissions = array(
      'access users overview',
      'create users',
      'edit users with no custom roles',
      'cancel users with no custom roles'
    );

    $roles = user_roles();

    // Indicate when we are getting custom roles
    $pos = 0;

    foreach($roles as $role => $values) {
      // When we read a custom role
      if($pos >= 3) {
        $permissions[] = "edit users with role $role";
        $permissions[] = "cancel users with role $role";
      }

      $pos++;
    }


    return AccessResult::allowedIfHasPermissions($account, $permissions, 'OR');

  }
}
