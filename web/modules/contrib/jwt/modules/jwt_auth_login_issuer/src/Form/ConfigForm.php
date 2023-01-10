<?php

namespace Drupal\jwt_auth_login_issuer\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class ConfigForm.
 *
 * @package Drupal\jwt_auth_login_issuer\Form
 */
class ConfigForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'jwt_auth_login_issuer.config',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'jwt_auth_login_issuer';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $defaultValue = $this->config('jwt_auth_login_issuer.config')->get('expiry');
    $form['jwt_token_expiry'] = [
      '#type' => 'number',
      '#default_value' => $defaultValue,
      // Don't allow negative time. Zero would set the token immediately out of
      // date. Not sure the use case for it but will leave that in.
      '#min' => 0,
      '#title' => $this->t('Token expire value (in minutes)'),
    ];
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $expiry = $form_state->getValue('jwt_token_expiry');
    if (!is_numeric((int) $expiry)) {
      $form_state->setErrorByName('jwt_token_expiry', $this->t('Expire time must be a number (in minutes)'));
    }
    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);

    $values = $form_state->getValues();

    if (isset($values['jwt_token_expiry'])) {
      $this->config('jwt_auth_login_issuer.config')->set('expiry', $values['jwt_token_expiry'])->save();
    }
  }

}
