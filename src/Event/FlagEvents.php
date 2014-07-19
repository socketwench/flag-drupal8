<?php
/**
 * @file
 * Contains \Drupal\flag\Event\FlagEvents.
 */
namespace Drupal\flag\Event;

/**
 * Contains all events thrown in the Flag module.
 *
 */
final class FlagEvents {

  const ENTITY_FLAGGED = 'flag.entity_flagged';

  const ENTITY_UNFLAGGED = 'flag.entity_unflagged';

  const FLAG_DELETED = 'flag.flag_deleted';
}
