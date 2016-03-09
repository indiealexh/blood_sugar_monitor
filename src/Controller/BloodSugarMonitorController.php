<?php
/**
 * @file
 * Contains \Drupal\blood_sugar_monitor\BloodSugarMonitorController.
 */

namespace Drupal\blood_sugar_monitor\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Database\Database;

class BloodSugarMonitorController extends ControllerBase{

  public function content() {

    $userid = \Drupal::currentUser()->id();

    $listings = Database::getConnection()
      ->select('blood_sugar_monitor','bsm')
      ->fields('bsm')
      ->range(0,10)
      ->condition('bsm.uid',$userid)
      ->orderBy('bsm.created', 'DESC')
      ->execute();

    $rows = [];

    foreach($listings as $listing) {
      $rows[]['data'] = [
        $listing->value,
        date('Y-m-d H:i:s',$listing->created),
      ];
    }

    return array(
      '#theme' => 'table',
      '#header' => [t('Value'),t('Recorded')],
      '#rows' => $rows,
    );
  }

}
