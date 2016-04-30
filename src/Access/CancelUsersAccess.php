<?php

/**
* @file
* Contains Drupal\administerusersbyrole\Access\CancelUsersAccess
*/

namespace Drupal\administerusersbyrole\Access;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Access\AccessCheckInterface;
use Drupal\user\Entity\Role;
use Symfony\Component\Routing\Route;
use Symfony\Component\HttpFoundation\Request;
use Drupal\Core\Session\AccountInterface;

/**
 * Access check for user cancellation routes.
 */

class CancelUsersAccess implements AccessCheckInterface {
  /**
   * {@inheritdoc}
   */
  public function applies(Route $route) {
    return array_key_exists('_access_administerusersbyrole_cancel_users', $route->getRequirements());
  }

  /**
   * {@inheritdoc}
   */
  public function access(Route $route, Request $request, AccountInterface $account) {

    if($account->hasPermission('administer users')) {
      return AccessResult::allowed();
    }

    $user_requested =\Drupal::routeMatch()->getParameter('user');

    // If it's its own account
    if($account->id() == $user_requested->id()) {
      return AccessResult::allowed();
    }

    // Roles of the uid requested
    $user_requested_roles = $user_requested->getRoles();

    // If it's for an administrator account, deny the access
    if(in_array('administrator', $user_requested_roles)) {
      return AccessResult::forbidden();
    }

    // Roles of the current user
    $account_roles = $account->getRoles();

    // If it's an anonymous user, deny the access
    if(in_array(DRUPAL_ANONYMOUS_RID, $account_roles)) {
      return AccessResult::forbidden();
    }

    // All permissions of the current user
    $account_permissions = array();

    foreach($account_roles as $account_role) {
      $role = Role::load($account_role)->getPermissions();
      $account_permissions = array_merge($account_permissions, $role);
    }

    // If the user requested is just an authenticated user
    if(in_array(DRUPAL_AUTHENTICATED_RID, $user_requested_roles) and
        sizeof($user_requested_roles) == 1) {

      // If the current user has the permission to modify no custom roles
      if(in_array('cancel users with no custom roles', $account_permissions)) {
        return AccessResult::allowed();
      }

      return AccessResult::forbidden();
    }

    // truncate authenticated value
    unset($user_requested_roles[0]);

    // Switch to see if one of the $role belong to the account permissions
    $hasPermission = false;
    // Check if the current user can modify the user requested because of its roles
    foreach ($user_requested_roles as $user_requested_role) {
      if(in_array("cancel users with role $user_requested_role", $account_permissions)) {
        $hasPermission = true;
      }
    }

    if($hasPermission) {
      return AccessResult::allowed();
    }
    else {
      return AccessResult::forbidden();
    }
  }
}
