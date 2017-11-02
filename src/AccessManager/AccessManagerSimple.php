<?php

namespace Drupal\administerusersbyrole\AccessManager;

use Drupal\administerusersbyrole\AccessManager\AccessManagerBase;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;
use Drupal\Component\Utility\Html;

/**
 * Defines a common interface for access managers.
 */
class AccessManagerSimple extends AccessManagerBase {

  /**
   * {@inheritdoc}
   */
  public function access(array $roles, $operation, AccountInterface $account) {
    if (!$this->preAccess($operation, $account)) {
      return AccessResult::neutral();
    }

    $allowed = $this->listRoles($operation, $account);
    $errors = array_diff($roles, $allowed);
    return AccessResult::allowedIf(!$errors);
  }

  /**
   * {@inheritdoc}
   */
  public function listRoles($operation, AccountInterface $account) {
    if (!$this->preAccess($operation, $account)) {
      return [];
    }

    $result = $this->config->get('roles');
    if ($this->config->get('include_exclude') == 'exclude') {
      $result = array_diff($this->allRoles(), $result);
    }
    else {
      $result = array_intersect($this->allRoles(), $result);
    }

    return $result;
  }

  /**
   * {@inheritdoc}
   */
  public function form() {
    $form['include_exclude'] = [
      '#type' => 'radios',
      '#title' => $this->t('Available roles'),
      '#options' => [
        'exclude' => $this->t('All actions, except selected'),
        'include' => $this->t('Only selected actions'),
      ],
      '#default_value' => $this->config->get('include_exclude'),
    ];

    $options = array_map(function ($item) { return Html::escape($item->label()); }, $this->allRoles(TRUE));
    $form['roles'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Roles'),
      '#default_value' => $this->config->get('roles') ?: [],
      '#options' => $options,
    ];

    return $form;
  }

}
