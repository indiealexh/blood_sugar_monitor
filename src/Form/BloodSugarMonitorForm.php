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

    $userid = \Drupal::currentUser()->id();

    $count = Database::getConnection()
      ->select('blood_sugar_monitor','bsm')
      ->fields('bsm')
      ->range(0,1)
      ->condition('bsm.uid',$userid)
      ->orderBy('bsm.created', 'DESC')
      ->countQuery()->execute()->fetchField();

    if((int)$count == (int)0) return true;

    $listings = Database::getConnection()
      ->select('blood_sugar_monitor','bsm')
      ->fields('bsm')
      ->range(0,1)
      ->condition('bsm.uid',$userid)
      ->orderBy('bsm.created', 'DESC')
      ->execute();

    foreach($listings as $listing) {
      $tpHour = (float)$listing->created + (float)3600;
      if((float)time() > (float)$tpHour) {
        return true;
      }
    }

    return false;
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

  protected function timeErrorResponse() {
    $response = new AjaxResponse();
    $user = \Drupal\user\Entity\User::load(\Drupal::currentUser()->id());
    $response->addCommand(new HtmlCommand('#bsm-form',t('Sorry '.$user->get('name')->value.', you already submitted a value within the past hour')));
    $response->addCommand(new CssCommand('#bsm-form',[
      'background' => '#fcf4f2 no-repeat 10px 17px',
      'padding' => '15px 20px 15px 35px',
      'border' => '1px solid #f9c9bf',
      'border-radius' => '2px',
      'box-shadow' => '-8px 0 0 #e62600',
      'background-image' => 'url(/core/misc/icons/e32700/error.svg)'
    ]));
    return $response;
  }

  public function ajaxCallback(array &$form, FormStateInterface $form_state) {
    $user = \Drupal\user\Entity\User::load(\Drupal::currentUser()->id());

    if(!$this->checkHourPassed()) {
      return $this->timeErrorResponse();
    }

    if(!$this->validateBSValue($form_state->getValue('blood_sugar'))) {
      throw new \Exception('Error');
    }

    try {
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
