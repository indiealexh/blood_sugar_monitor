<?php
/**
 * @file
 * Contains \Drupal\blood_sugar_monitor\Form\BloodSugarMonitorForm.
 */

namespace Drupal\blood_sugar_monitor\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

class BloodSugarMonitorForm extends FormBase{

  /**
   * Returns a unique string identifying the form.
   *
   * @return string
   *   The unique string identifying the form.
   */
  public function getFormId() {
    return 'bsm_form';
  }

  /**
   * Form constructor.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   *
   * @return array
   *   The form structure.
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $form['blood_sugar'] = array(
      '#type' => 'number',
      '#title' => t('blood sugar level'),
      '#default_value' => 5.00,
      '#min' => 1.00,
      '#max' => 10.00,
      '#step' => 0.01,
      '#required' => TRUE,
    );
    $form['submit'] = array(
      '#type' => 'submit',
      '#value' => t('Submit'),
    );

    return $form;
  }

  /**
   * Form submission handler.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    drupal_set_message($this->t('Thank you.<br>You will be able to submit another result in an hours time'));
  }
}
