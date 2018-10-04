<?php

namespace Drupal\behat_generate\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Configure behat_generate settings for this site.
 */
class SettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'behat_generate_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['behat_generate.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['installation_path'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Behat Installation Path'),
      '#default_value' => $this->config('behat_generate.settings')->get('installation_path'),
    ];
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    if ($form_state->getValue('installation_path') != 'installation_path') {
      $form_state->setErrorByName('installation_path', $this->t('The value is not correct.'));
    }
    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config('behat_generate.settings')
      ->set('installation_path', $form_state->getValue('installation_path'))
      ->save();
    parent::submitForm($form, $form_state);
  }

}
