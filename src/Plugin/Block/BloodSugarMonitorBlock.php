<?php
/**
 * Provides a 'Hello' Block
 *
 * @Block(
 *   id = "blood_sugar_monitor_block",
 *   admin_label = @Translation("Blood Sugar Monitor block"),
 * )
 */

namespace Drupal\blood_sugar_monitor\Plugin\Block;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Session\AccountInterface;

class BloodSugarMonitorBlock extends BlockBase{

  /**
   * Builds and returns the renderable array for this block plugin.
   *
   * If a block should not be rendered because it has no content, then this
   * method must also ensure to return no content: it must then only return an
   * empty array, or an empty array with #cache set (with cacheability metadata
   * indicating the circumstances for it being empty).
   *
   * @return array
   *   A renderable array representing the content of the block.
   *
   * @see \Drupal\block\BlockViewBuilder
   */
  public function build() {

    $render = array();
    $form = \Drupal::formBuilder()->getForm('Drupal\blood_sugar_monitor\Form\BloodSugarMonitorForm');
    $render[] = $form;

    return $render;
  }

  /**
   * @param \Drupal\Core\Session\AccountInterface $account
   * @return bool
   */
  public function blockAccess(AccountInterface $account) {
    if($account->hasPermission('store bsm data')) return AccessResult::allowed();
    else return AccessResult::forbidden();
  }

}
