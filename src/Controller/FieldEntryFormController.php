<?php
/**
 * @file
 */

namespace Drupal\flag\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\flag\FlagInterface;

/**
 * Provides a controller for the Field Entry link type.
 *
 * @package Drupal\flag\Controller
 */
class FieldEntryFormController extends ControllerBase {

  /**
   * Performs a flagging when called via a route.
   *
   * @param int $flag_id
   *   The flag ID.
   * @param int $entity_id
   *   The flaggable ID.
   *
   * @return AjaxResponse
   *   The response object.
   *
   * @see \Drupal\flag\Plugin\ActionLink\AJAXactionLink
   */
  public function flag($flag_id, $entity_id) {
  }

  /**
   * Performs an unflagging when called via a route.
   *
   * @param int $flag_id
   *   The flag ID.
   * @param int $entity_id
   *   The entity ID to unflag.
   *
   * @return AjaxResponse
   *   The response object.
   *
   * @see \Drupal\flag\Plugin\ActionLink\AJAXactionLink
   */
  public function unflag($flag_id, $entity_id) {
  }

  /**
   * Get the flag's field entry form.
   *
   * @param FlagInterface $flag
   *   The flag from which to get the form.
   */
  protected function getForm(FlagInterface $flag) {
    $form = \Drupal::service('entity.form_builder')->getForm($flag, 'default');
  }
}
