<?php
/**
 * @file
 * Contains \Drupal\flag\Event\FlagEvents.
 */

namespace Drupal\flag\Event;

/**
 * Contains all events thrown in the Flag module.
 */
final class FlagEvents {

  /**
   * Event ID for when an entity is flagged.
   */
  const ENTITY_FLAGGED = 'flag.entity_flagged';

  /**
   * Event ID for when a previously flagged entity is unflagged.
   */
  const ENTITY_UNFLAGGED = 'flag.entity_unflagged';

  /**
   * Event ID for when a flag (not a flagging) is deleted.
   */
  const FLAG_DELETED = 'flag.flag_deleted';
}
