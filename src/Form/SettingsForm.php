<?php

namespace Drupal\administerusersbyrole\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\administerusersbyrole\AccessManager\AccessManagerBase;

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

    $form['mode'] = [
      '#type' => 'select',
      '#title' => 'Configuration mode',
      '#options' => ['simple' => $this->t('Simple'), 'complex' => $this->t('Complex')],
      '#default_value' => $config->get('mode'),
      '#description' => 'Select mode for configuring access.',
    ];

    $form['manager'] = [
      '#type' => 'fieldset',
      '#title' => 'Mode options',
    ];

    $form['manager'] += AccessManagerBase::get()->form();
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->config('administerusersbyrole.settings');

    // Remove button and internal Form API values from submitted values.
    $values = $form_state->cleanValues()->getValues();
    $values['roles'] = array_filter($values['roles']);

    // Write variables.
    foreach ($values as $key => $value) {
      $config->set($key, $value);
    }
    $config->save();

    parent::submitForm($form, $form_state);
  }

}
