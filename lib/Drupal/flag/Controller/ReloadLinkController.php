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
    $flag = entity_load('flag_flag', $flag_id);
    $entity = entity_load($flag->getFlaggableEntityType(), $entity_id);
    \Drupal::service('flag')->flag($flag, $entity);

    $destination = \Drupal::request()->get('destination', $entity->url());

    //@todo SECURITY HOLE. Please fix!
    return new RedirectResponse($destination);
  }

  public function unflag($flag, $entity) {
    $flag = entity_load('flag_flag', $flag_id);
    $entity = entity_load($flag->getFlaggableEntityType(), $entity_id);
    \Drupal::service('flag')->unflag($flag, $entity);

    $destination = \Drupal::request()->get('destination', $entity->url());

    //@todo SECURITY HOLE. Please fix!
    return new RedirectResponse($destination);
  }

} 