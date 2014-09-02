<?php
/**
 * @file
 * Contains \Drupal\flag\Event\FlaggingEvent.
 */

namespace Drupal\flag\Event;


use Drupal\Core\Entity\EntityInterface;
use Drupal\flag\FlagInterface;

/**
 * Event manages the flagging of events.
 */
class FlaggingEvent extends FlagEventBase {

  /**
   * The Flag in question.
   *
   * @var \Drupal\Core\Entity\EntityInterface
   */
  protected $entity;

  /**
   * The action.
   *
   * @var string
   */
  protected $action;

  /**
   * Builds a new FlaggingEvent.
   *
   * @param \Drupal\flag\FlagInterface $flag
   *   The flag.
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity to be acted upon.
   * @param string $action
   *   The action to perform. One of 'flag' or 'unflag'.
   */
  public function __construct(FlagInterface $flag, EntityInterface $entity, $action) {
    parent::__construct($flag);

    $this->entity = $entity;
    $this->action = $action;
  }

}
