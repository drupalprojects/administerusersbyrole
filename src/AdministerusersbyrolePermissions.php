<?php

/**
 * @file
 * Contains \Drupal\administerusersbyrole\AdministerusersbyrolePermissions.
 */

namespace Drupal\administerusersbyrole;

use Drupal\Component\Utility\String;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides dynamic permissions of the administerusersbyrole module.
 */
class AdministerusersbyrolePermissions implements ContainerInjectionInterface {

  use StringTranslationTrait;

  /**
   * The entity manager.
   *
   * @var \Drupal\Core\Entity\EntityManagerInterface
   */
  protected $entityManager;

  /**
   * Constructs a new AdministerusersbyrolePermissions instance.
   *
   * @param \Drupal\Core\Entity\EntityManagerInterface $entity_manager
   *   The entity manager.
   */
  public function __construct(EntityManagerInterface $entity_manager) {
    $this->entityManager = $entity_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static($container->get('entity.manager'));
  }

  /**
   * Returns an array of administerusersbyrole permissions.
   *
   * @return array
   */
  public function permissions() {

    $roles = user_roles();


    // Exclude the admin role.  Once you can edit an admin, you can set their password, log in and do anything,
    // which defeats the point of using this module.
    $admin_rid = 'administrator';
    $permissions = array();

    foreach ($roles as $role) {
      $rid = $role->get('id');

      if ($rid == $admin_rid) {
        continue;
      }

      foreach (array('Edit', 'Cancel') as $op) {
        if(!($rid == DRUPAL_AUTHENTICATED_RID or $rid == DRUPAL_ANONYMOUS_RID)) {
          $permission_string = lcfirst(_administerusersbyrole_build_perm_string($rid, $op));
          $role_label = $role->get('label');
          $permission_title = "$op users with role $role_label";
          $permissions[$permission_string] = array('title' => $permission_title);
        }
      }
    }

    return $permissions;
  }

}
