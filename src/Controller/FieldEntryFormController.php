<?php
/**
 * @file
 * Contains the \Drupal\flag\Controller\FieldEntryFormController class.
 */

namespace Drupal\flag\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\flag\FlaggingInterface;
use Drupal\flag\Entity\Flag;

/**
 * Provides a controller for the Field Entry link type.
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
    $account = $this->currentUser();
    $flag = Flag::load($flag_id);

    $flagging = $this->entityManager()->getStorage('flagging')->create([
      'fid' => $flag->id(),
      'entity_type' => $flag->getFlaggableEntityType(),
      'entity_id' => $entity_id,
      'type' => $flag->id(),
      'uid' => $account->id(),
    ]);

    return $this->getForm($flagging, 'add');
  }

  /**
   * Return the flagging edit form.
   *
   * @param string $flag_id
   *   The flag ID.
   * @param mixed $entity_id
   *   The entity ID.
   *
   * @return array
   *   The flagging edit form.
   */
  public function edit($flag_id, $entity_id) {
    $flagging = $this->getFlagging($flag_id, $entity_id);

    return $this->getForm($flagging, 'edit');
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
    $flagging = $this->getFlagging($flag_id, $entity_id);

    return $this->getForm($flagging, 'delete');
  }

  /**
   * Title callback when creating a new flagging.
   *
   * @param int $flag_id
   *   The flag ID.
   * @param int $entity_id
   *   The entity ID to unflag.
   *
   * @return string
   *   The flag field entry form title.
   */
  public function flagTitle($flag_id, $entity_id) {
    $flag = \Drupal::service('flag')->getFlagById($flag_id);
    $link_type = $flag->getLinkTypePlugin();
    return $link_type->getFlagQuestion();
  }

  /**
   * Title callback when editing an existing flagging.
   *
   * @param int $flag_id
   *   The flag ID.
   * @param int $entity_id
   *   The entity ID to unflag.
   *
   * @return string
   *   The flag field entry form title.
   */
  public function editTitle($flag_id, $entity_id) {
    $flag = \Drupal::service('flag')->getFlagById($flag_id);
    $link_type = $flag->getLinkTypePlugin();
    return $link_type->getEditFlaggingTitle();
  }

  /**
   * Get a flagging that already exists.
   *
   * @param string $flag_id
   *   The flag ID.
   * @param mixed $entity_id
   *   The flaggable ID.
   *
   * @return FlaggingInterface|null
   *   The flagging or NULL.
   */
  protected function getFlagging($flag_id, $entity_id) {
    $account = $this->currentUser();
    $flag = \Drupal::service('flag')->getFlagById($flag_id);
    $entity = \Drupal::service('flag')->getFlaggableById($flag, $entity_id);
    $flaggings = \Drupal::service('flag')->getFlaggings($entity, $flag, $account);

    return reset($flaggings);
  }

  /**
   * Get the flag's field entry form.
   *
   * @param FlaggingInterface $flagging
   *   The flagging from which to get the form.
   * @param string|null $operation
   *   The operation identifying the form variant to return.
   *
   * return array
   *   The form array.
   */
  protected function getForm(FlaggingInterface $flagging, $operation = 'default') {
    return $this->entityFormBuilder()->getForm($flagging, $operation);
  }
}
