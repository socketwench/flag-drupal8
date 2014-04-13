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

    $flagging = \Drupal::service('flag')->flag($flag_id, $entity_id);

    // If the response is coming from JavaScript, we can't return a redirect.
    // Instead, we replace the flag link with an unflag link via JSON.
    if ($request->request->get('js')) {
      $response = new AjaxResponse();
      $linkType = $flagging->getFlag()->getLinkTypePlugin();
      $link = $linkType->renderLink('unflag', $flagging->getFlag(), $flagging->getFlaggable());
      $linkId = '#' . $link['#attributes']['id'];
      $html = drupal_render($link);
      $replace = new ReplaceCommand($linkId, $html);
      $response->addCommand($replace);

      return $response;
    }

    // Get the destination.
    $destination = $request->get('destination', $flagging->getFlaggable()->url());

    //@todo SECURITY HOLE. Please fix!
    return new RedirectResponse($destination);
  }

  public function unflag(Request $request, $flag_id, $entity_id) {
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