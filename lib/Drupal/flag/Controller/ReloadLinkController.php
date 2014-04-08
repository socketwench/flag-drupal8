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
use Symfony\Component\HttpFoundation\RedirectResponse;

class ReloadLinkController extends ControllerBase {

  public function flag($flag_id, $entity_id) {
    // Get the Flag Service.
    $flagService = \Drupal::service('flag');

    // Get the Flag and Entity objects.
    $flag = $flagService->getFlagById($flag_id);
    $entity = $flagService->getFlaggableById($flag, $entity_id);

    // While we could use FlagService::flag() here, we wouldn't have the URL
    // to redirect Drupal afterward. So we flag by object instead.
    $flagService->flagByObject($flag, $entity);

    // Get the destination.
    $destination = \Drupal::request()->get('destination', $entity->url());

    //@todo SECURITY HOLE. Please fix!
    return new RedirectResponse($destination);
  }

  public function unflag($flag_id, $entity_id) {
    // Get the Flag Service.
    $flagService = \Drupal::service('flag');

    // Get the Flag and Entity objects.
    $flag = $flagService->getFlagById($flag_id);
    $entity = $flagService->getFlaggableById($flag, $entity_id);

    $flaggings = \Drupal::service('flag')->getFlaggings($entity, $flag);
    foreach ($flaggings as $flagging) {
      \Drupal::service('flag')->unflagByObject($flagging);
    }

    $destination = \Drupal::request()->get('destination', $entity->url());

    //@todo SECURITY HOLE. Please fix!
    return new RedirectResponse($destination);
  }

} 