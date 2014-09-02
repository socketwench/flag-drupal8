<?php
/**
 * @file
 * Contains the Normal Link (Reload) link type.
 */

namespace Drupal\flag\Plugin\ActionLink;

use Drupal\flag\ActionLinkTypeBase;

/**
 * Provides the Normal Link (Reload) link type.
 *
 * @ActionLinkType(
 *   id = "reload",
 *   label = @Translation("Normal link"),
 *   description = "A normal non-JavaScript request will be made and the
 *     current page will be reloaded."
 * )
 */
class Reload extends ActionLinkTypeBase {

  /**
   * {@inheritdoc}
   */
  public function routeName($action = NULL) {
    if ($action === 'unflag') {
      return 'flag_link_unflag.html';
    }

    return 'flag_link_flag.html';
  }

}
