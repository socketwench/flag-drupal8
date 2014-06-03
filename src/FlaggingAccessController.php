<?php
/**
 * Created by PhpStorm.
 * User: tess
 * Date: 2/7/14
 * Time: 7:50 PM
 */

namespace Drupal\flag;

use Drupal\Core\Access\AccessInterface;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityAccessController;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
use Symfony\Component\HttpFoundation\Request;


class FlaggingAccessController extends ControllerBase {

  public function checkFlag(Request $request) {
    $entity_id = $request->get('entity_id');

    if (user_access('flag' . $entity_id)) {
      return AccessInterface::ALLOW;
    }

    return AccessInterface::DENY;
  }

  /**
   *
   */
  public function checkUnflag(Request $request) {
    $entity_id = $request->get('entity_id');

    if (user_access('unflag' . $entity_id)) {
      return AccessInterface::ALLOW;
    }

    return AccessInterface::DENY;
  }

} 