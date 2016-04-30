<?php

/**
* @file
* Contains \Drupal\administerusersbyrole\Acess\CreateUsersAccess
*/
namespace Drupal\administerusersbyrole\Access;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Access\AccessCheckInterface;
use Symfony\Component\Routing\Route;
use Symfony\Component\HttpFoundation\Request;
use Drupal\Core\Session\AccountInterface;

/**
 * Access check for user registration routes.
 */

class CreateUsersAccess implements AccessCheckInterface {
  /**
   * {@inheritdoc}
   */
  public function applies(Route $route) {
    return array_key_exists('_access_administerusersbyrole_create_users', $route->getRequirements());
  }

  /**
   * {@inheritdoc}
   */
  public function access(Route $route, Request $request, AccountInterface $account) {

    $permissions = array('administer users', 'create users');
    return AccessResult::allowedIfHasPermissions($account, $permissions, 'OR');

  }
}
