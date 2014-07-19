<?php
/**
 * @file
 * Contains \Drupal\flag\Plugin\ActionLink\AJAXactionLink.
 */

namespace Drupal\flag\Plugin\ActionLink;

use Drupal\flag\ActionLinkTypeBase;
use Drupal\Core\Entity\EntityInterface;
use Drupal\flag\FlagInterface;

/**
 * Class Reload
 *
 * @ActionLinkType(
 *   id = "AJAX Link",
 *   label = @Translation("AJAX link"),
 *   description = "An AJAX JavaScript request will be made without reloading the page."
 * )
 */
class AJAXactionLink extends ActionLinkTypeBase{

  /**
   * {@inheritdoc}
   */
  public function routeName($action = NULL) {
    if ($action === 'unflag') {
      return 'flag.link_unflag.json';
    }

    return 'flag.link_flag.json';
  }

  /**
   * {@inheritdoc}
   */
  public function renderLink($action, FlagInterface $flag, EntityInterface $entity) {
    $render = parent::renderLink($action, $flag, $entity);
    $render['#attached']['library'][] = 'core/drupal.ajax';
    $render['#attributes']['class'][] = 'use-ajax';
    return $render;
  }

} 