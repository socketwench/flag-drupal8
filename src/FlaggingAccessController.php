<?php
/**
 * @file
 * Contains \Drupal\flag\FlaggingAccessController.
 */

namespace Drupal\flag;

use Drupal\Core\Access\AccessInterface;
use Drupal\Core\Controller\ControllerBase;
use Drupal\flag\Entity\Flag;
use Symfony\Component\HttpFoundation\Request;

/**
 * Controls flagging access permission.
 */
class FlaggingAccessController extends ControllerBase {

  /**
   * Checks flagging permission.
   *
   * @param Request $request
   *   The request object.
   *
   * @return string
   *   Returns indication value for flagging access permission.
   */
  public function checkFlag(Request $request) {
    $flag = Flag::load($request->get('flag_id'));
    if ($flag->hasActionAccess('flag')) {
      return AccessInterface::ALLOW;
    }

    return AccessInterface::DENY;
  }

  /**
   * Checks unflagging permission.
   *
   * @param Request $request
   *   The request object.
   *
   * @return string
   *   Returns indication value for unflagging access permission.
   */
  public function checkUnflag(Request $request) {
    $flag = Flag::load($request->get('flag_id'));
    if ($flag->hasActionAccess('unflag')) {
      return AccessInterface::ALLOW;
    }

    return AccessInterface::DENY;
  }

}
