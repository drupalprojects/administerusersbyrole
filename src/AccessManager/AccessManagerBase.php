<?php

namespace Drupal\administerusersbyrole\AccessManager;

use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Session\AccountInterface;
use Drupal\administerusersbyrole\AccessManager\AccessManagerInterface;

/**
 * Defines a common interface for all entity objects.
 *
 * @ingroup entity_api
 */
abstract class AccessManagerBase implements AccessManagerInterface {

  use StringTranslationTrait;

  /* @var \Drupal\administerusersbyrole\AccessManagerInterface $manager */
  private static $manager;

  protected $config;

  const CONVERT_OP = [
    'cancel' => 'cancel',
    'delete' => 'cancel',
    'edit' => 'edit',
    'update' => 'edit',
    'view' => 'view',
    'role-assign' => 'role-assign',
  ];

  protected $op_names;

  public static function get() {
    if (!self::$manager) {
      $config = \Drupal::config('administerusersbyrole.settings');
      $mode = $config->get('mode');

      if ($mode == 'complex') self::$manager = new AccessManagerComplex();
      else self::$manager = new AccessManagerSimple();
    }
    return self::$manager;
  }

  function __construct () {
    $this->config = \Drupal::config('administerusersbyrole.settings');

    $this->op_names = [
      'edit' => $this->t('Edit'),
      'cancel' => $this->t('Cancel'),
      'view' => $this->t('View'),
      'role-assign' => $this->t('Assign roles to'),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function permissions() {
    foreach ($this->op_names as $op => $name) {
      $perm_string = $this->buildPermString($op);
      $perm_title = $this->t("@operation users by role", [
        '@operation' => $name,
      ]);
      $perms[$perm_string] = ['title' => $perm_title];
    }
    return $perms;
  }

  /**
   * {@inheritdoc}
   */
  public function form() {
    return [];
  }

  /**
   * Initial access check for an operation to test if access might be granted for some roles.
   *
   * @param string $operation: The operation that is to be performed on the user.
   *   Value is updated to match the canonical value used in this module.
   *
   * @param \Drupal\Core\Session\AccountInterface $account: The account trying to access the entity.
   *
   * @return \Drupal\Core\Access\AccessResultInterface
   *   The access result. hook_entity_access() has detailed documentation.
   */
  protected function preAccess(&$operation, AccountInterface $account) {
    // Full admins already have permissions so we are wasting our time to continue.
    if ($account->hasPermission('administer users')) {
      return FALSE;
    }

    // Ignore unrecognised operation.
    if (!isset(self::CONVERT_OP[$operation])) {
      return FALSE;
    }

    $operation = self::CONVERT_OP[$operation];
    return $this->hasPerm($operation, $account);
  }

  /**
   * Return array of all roles that are manageable by this module.
   */
  protected function allRoles($asObjects = FALSE) {
    $roles = user_roles(TRUE);

    // Exclude the AUTHENTICATED_ROLE which is not a real role.
    unset($roles[AccountInterface::AUTHENTICATED_ROLE]);

    // Exclude admin roles.  Once you can edit an admin, you can set their password, log in and do anything,
    // which defeats the point of using this module.
    $roles = array_filter($roles, function($role) { return !$role->isAdmin(); });

    return $asObjects ? $roles : array_keys($roles);
  }

  /**
   * Checks access to a permission for a given role name.
   */
  protected function hasPerm($op, AccountInterface $account, $rid = NULL) {
    return $account->hasPermission($this->buildPermString($op, $rid));
  }

  /**
   * Generates a permission string for a given role name.
   */
  protected function buildPermString($op, $rid = NULL) {
    return $rid ? "$op users with role $rid" : "$op users by role";
  }

}
