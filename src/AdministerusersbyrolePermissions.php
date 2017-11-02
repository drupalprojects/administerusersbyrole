<?php

namespace Drupal\administerusersbyrole;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\administerusersbyrole\AccessManager\AccessManagerBase;

/**
 * Provides dynamic permissions of the administerusersbyrole module.
 */
class AdministerusersbyrolePermissions implements ContainerInjectionInterface {

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static();
  }

  /**
   * Returns an array of administerusersbyrole permissions.
   *
   * @return array
   */
  public function permissions() {
    return AccessManagerBase::get()->permissions();
  }
}
