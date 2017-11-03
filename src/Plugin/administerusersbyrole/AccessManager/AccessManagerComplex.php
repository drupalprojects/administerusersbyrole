<?php

namespace Drupal\administerusersbyrole\Plugin\administerusersbyrole\AccessManager;

use Drupal\administerusersbyrole\Plugin\administerusersbyrole\AccessManager\AccessManagerBase;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;

/**
 * Complex access manager based on permissions for each role.
 *
 * @AccessManager(
 *   id = "complex",
 *   label = @Translation("Complex"),
 * )
 */
class AccessManagerComplex extends AccessManagerBase {

  /**
   * {@inheritdoc}
   */
  public function access(array $roles, $operation, AccountInterface $account) {
    if (!$this->preAccess($operation, $account)) {
      return AccessResult::neutral();
    }

    foreach ($roles as $rid) {
      if (!$this->hasPerm($operation, $account, $rid)) {
        return AccessResult::neutral();
      }
    }

    return AccessResult::allowed();
  }

  public function listRoles($operation, AccountInterface $account) {
    if (!$this->preAccess($operation, $account)) {
      return [];
    }

    $result = [];
    foreach ($this->allRoles() as $rid) {
      if ($this->hasPerm($operation, $account, $rid)) $result[] = $rid;
    }

    return $result;
  }

  /**
   * {@inheritdoc}
   */
  public function permissions() {
    $perms = parent::permissions();

    foreach ($this->allRoles(TRUE) as $rid => $role) {
      $op_titles = [
        'edit' => $this->t('Edit users with role %role', ['%role' => $role->label()]),
        'cancel' => $this->t('Cancel users with role %role', ['%role' => $role->label()]),
        'view' => $this->t('View users with role %role', ['%role' => $role->label()]),
        'role-assign' => $this->t('Assign role %role', ['%role' => $role->label()]),
      ];

      foreach ($op_titles as $op => $title) {
        $perm_string = $this->buildPermString($op, $rid);
        $perms[$perm_string] = ['title' => $title];
      }
    }

    return $perms;
  }

}
