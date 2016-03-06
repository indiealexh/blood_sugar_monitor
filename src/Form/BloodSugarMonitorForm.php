<?php
/**
 * @file
 * Contains \Drupal\blood_sugar_monitor\Form\BloodSugarMonitorForm.
 */

namespace Drupal\blood_sugar_monitor\Form;

use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\ChangedCommand;
use Drupal\Core\Ajax\CssCommand;
use Drupal\Core\Ajax\HtmlCommand;
use Drupal\Core\Ajax\InvokeCommand;
use Drupal\Core\Database\Connection;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Database\Database;

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
      '#description' => 'Please enter a blood sugar value',
      '#default_value' => 5.00,
      '#min' => 1.00,
      '#max' => 10.00,
      '#step' => 0.01,
      '#required' => TRUE,
      '#suffix' => t('<div id="search-suggest"></div>'),
    );
    $form['submit_blood_sugar'] = array(
      '#type' => 'submit',
      '#value' => t('Add blood sugar value'),
      '#ajax' => array(
        'callback' => array($this, 'ajaxCallback'),
        'event' => 'click',
        'progress' => array(
          'type' => 'throbber',
          'message' => 'Submitting',
        ),
      ),
    );

    return $form;
  }

  protected function checkHourPassed() {
    return true;
  }

  protected function validateBSValue($value) {
    $value = (float)$value;
    $valid = true;
    if(!is_float($value)) $valid = false;
    if($value < 1.00 || $value > 10.00) $valid = false;
    if(!preg_match('/^\d{1,2}\.?\d?\d?$/',$value)) $valid = false;
    return $valid;
  }

  protected function successResponse() {
    $response = new AjaxResponse();
    $user = \Drupal\user\Entity\User::load(\Drupal::currentUser()->id());
    $response->addCommand(new HtmlCommand('#bsm-form',t('Thank you '.$user->get('name')->value.', please come back in an hour to submit your next blood sugar value')));
    $response->addCommand(new CssCommand('#bsm-form',[
      'background' => '#f3faef no-repeat 10px 17px',
      'padding' => '15px 20px 15px 35px',
      'border' => '1px solid #c9e1bd',
      'border-radius' => '2px',
      'box-shadow' => '-8px 0 0 #77b259',
      'background-image' => 'url(/core/misc/icons/73b355/check.svg)'
    ]));
    return $response;
  }

  public function ajaxCallback(array &$form, FormStateInterface $form_state) {
    $user = \Drupal\user\Entity\User::load(\Drupal::currentUser()->id());

    try {
      if(!$this->checkHourPassed()) throw new \Exception('Error');
      if(!$this->validateBSValue($form_state->getValue('blood_sugar'))) throw new \Exception('Error');

      $fields = array(
        'uid' => \Drupal::currentUser()->id(),
        'value' => $form_state->getValue('blood_sugar'),
        'created' => time(),
      );
      Database::getConnection()->insert('blood_sugar_monitor')->fields($fields)->execute();

      return $this->successResponse();
    } catch (\Exception $e) {
      $response = new AjaxResponse();
      $response->addCommand(new CssCommand('#edit-blood-sugar',['border-color' => 'red']));
      $response->addCommand(new CssCommand('#edit-blood-sugar--description',['color' => 'red']));
      $response->addCommand(new HtmlCommand('#edit-blood-sugar--description',t('Invalid')));
      return $response;
    }

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
    //ToDo: Decide what to do with this
  }
}
