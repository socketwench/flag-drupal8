<?php
/**
 * @file
 * Contains the FlagEvents class.
 */

namespace Drupal\flag\Event;


/**
 * Contains all events thrown in the Flag module.
 *
 * @package Drupal\flag\Event
 */
final class FlagEvents {

  const ENTITY_FLAGGED = 'flag.entity_flagged';

  const ENTITY_UNFLAGGED = 'flag.entity_unflagged';

  const FLAG_DELETED = 'flag.flag_deleted';
}
