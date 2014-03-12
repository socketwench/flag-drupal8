<?php
/**
 * Created by PhpStorm.
 * User: tess
 * Date: 3/10/14
 * Time: 9:10 PM
 */

namespace Drupal\flag\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\flag\FlagInterface;
use Drupal\Core\Entity\EntityInterface;

class ReloadLinkController extends ControllerBase {

  public function flag($action, FlagInterface $flag, EntityInterface $entity) {
    if ($action == 'flag') {
      \Drupal::service('flag')->flag($flag, $entity);
    }
    else if ($action == 'unflag') {
      \Drupal::service('flag')->unflag($flag, $entity);
    }
  }

} 