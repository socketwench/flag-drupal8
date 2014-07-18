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
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\ReplaceCommand;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;

class ReloadLinkController extends ControllerBase {

  public function flag(Request $request, $flag_id, $entity_id) {
    /* @var \Drupal\flag\FlaggingInterface $flagging */
    $flagging = \Drupal::service('flag')->flag($flag_id, $entity_id);

    // Redirect back to the entity. A passed in destination query parameter
    // will automatically override this.
    $url_info = $flagging->getFlaggable()->urlInfo();
    return $this->redirect($url_info->getRouteName(), $url_info->getRouteParameters());
  }

  public function unflag(Request $request, $flag_id, $entity_id) {
    /* @var \Drupal\flag\FlagService $flag_service */
    $flag_service = \Drupal::service('flag');
    $flag_service->unflag($flag_id, $entity_id);

    $flag = $flag_service->getFlagById($flag_id);
    $entity = $flag_service->getFlaggableById($flag, $entity_id);

    // Redirect back to the entity. A passed in destination query parameter
    // will automatically override this.
    $url_info = $entity->urlInfo();
    return $this->redirect($url_info->getRouteName(), $url_info->getRouteParameters());
  }

}
