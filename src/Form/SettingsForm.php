<?php

namespace Drupal\administerusersbyrole\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\administerusersbyrole\Plugin\administerusersbyrole\AccessManager\AccessManagerBase;

/**
 * Configure AlbanyWeb settings for this site.
 */
class SettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'administerusersbyrole_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'administerusersbyrole.settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('administerusersbyrole.settings');
    $plugins = \Drupal::service('plugin.manager.administerusersbyrole')->getAll();

    $options = array_map(function($plugin) { return $plugin->getLabel(); }, $plugins);
    $form['mode'] = [
      '#type' => 'select',
      '#title' => $this->t('Configuration mode'),
      '#options' => $options,
      '#default_value' => $config->get('mode'),
      '#description' => $this->t('Select mode for configuring access.'),
    ];

    foreach ($plugins as $id => $plugin) {
      $form[$id] = [
        '#type' => 'fieldset',
        '#title' => $this->t('%mode options', ['%mode' => $plugin->getLabel()]),
        '#states' => [
          'visible' => [':input[name="mode"]' => ['value' => $id]]
        ],
      ];

      $form[$id] += $plugin->form();
    }

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->config('administerusersbyrole.settings');
    $plugins = \Drupal::service('plugin.manager.administerusersbyrole')->getAll();
    $values = $form_state->getValues();

    foreach ($plugins as $id => $plugin) {
      $plugin->formSave($config, $values);
    }
    $config->set('mode', $values['mode']);
    $config->save();

    parent::submitForm($form, $form_state);
  }

}
