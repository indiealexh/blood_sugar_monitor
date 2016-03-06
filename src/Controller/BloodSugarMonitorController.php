<?php
/**
 * @file
 * Contains \Drupal\blood_sugar_monitor\BloodSugarMonitorController.
 */

namespace Drupal\blood_sugar_monitor\Controller;

use Drupal\Core\Controller\ControllerBase;

class BloodSugarMonitorController extends ControllerBase{

  public function content() {
    return array(
      '#type' => 'markup',
      '#markup' => $this->t('Blood sugar monitor page'),
    );
  }

}
