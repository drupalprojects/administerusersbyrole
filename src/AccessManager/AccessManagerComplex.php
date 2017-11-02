<?php

namespace Drupal\administerusersbyrole\AccessManager;

use Drupal\administerusersbyrole\AccessManager\AccessManagerBase;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;

/**
 * Defines a common interface for all entity objects.
 *
 * @ingroup entity_api
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
      foreach ($this->op_names as $op => $name) {
        $perm_string = $this->buildPermString($op, $rid);
        $perm_title = $this->t("@operation users with role %role", [
          '@operation' => $name,
          '%role' => $role->label(),
        ]);
        $perms[$perm_string] = ['title' => $perm_title];
      }
    }

    return $perms;
  }

}
