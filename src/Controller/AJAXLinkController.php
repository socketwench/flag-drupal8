<?php
/**
 * Created by PhpStorm.
 * User: tess
 * Date: 4/13/14
 * Time: 3:28 PM
 */

namespace Drupal\flag\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\flag\FlagInterface;
use Drupal\flag\FlaggingInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\ReplaceCommand;

class AJAXLinkController extends ControllerBase {

  /**
   * @param $flag_id
   * @param $entity_id
   * @return AjaxResponse
   */
  public function flag($flag_id, $entity_id) {
    $flagging = \Drupal::service('flag')->flag($flag_id, $entity_id);

    $flag = $flagging->getFlag();
    $entity = $flagging->getFlaggable();

    return $this->generateResponse('unflag', $flag, $entity);
  }

  /**
   * @param $flag_id
   * @param $entity_id
   * @return AjaxResponse
   */
  public function unflag($flag_id, $entity_id) {
    $flagService = \Drupal::service('flag');
    $flagService->unflag($flag_id, $entity_id);

    $flag = $flagService->getFlagById($flag_id);
    $entity = $flagService->getFlaggableById($flag, $entity_id);

    return $this->generateResponse('flag', $flag, $entity);
  }

  /**
   * @param $action
   * @param FlagInterface $flag
   * @param EntityInterface $entity
   * @return AjaxResponse
   */
  protected function generateResponse($action, FlagInterface $flag, EntityInterface $entity) {
    // Create a new AJAX response.
    $response = new AjaxResponse();

    // Get the link type plugin.
    $linkType = $flag->getLinkTypePlugin();

    // Generate the link render array and get the link CSS ID.
    $link = $linkType->renderLink($action, $flag, $entity);
    $linkId = '#' . $link['#attributes']['id'];

    // Create a new JQuery Replace command to update the link display.
    $replace = new ReplaceCommand($linkId, drupal_render($link));
    $response->addCommand($replace);

    return $response;
  }

} 