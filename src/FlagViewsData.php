<?php

/**
 * @file
 * Contains \Drupal\flag\FlagViewsData.
 */

namespace Drupal\flag;

use Drupal\views\EntityViewsData;
use Drupal\views\EntityViewsDataInterface;

/**
 * Provides the views data for the flag entity type.
 */
class FlagViewsData extends EntityViewsData implements EntityViewsDataInterface {

  /**
   * {@inheritdoc}
   */
  public function getViewsData() {
    $data = parent::getViewsData();
    $data['flagging']['entity_id']['argument']['id'] = 'flag';

    // Flag content links.
    $data['flagging']['flag_link'] = [
      'field' => [
        'title' => t('Flag Links'),
        'help' => t('Display flag/unflag link.'),
        'id' => 'flag_link',
      ],
    ];

    // Specialized is null/is not null filter.
    $data['flagging']['flagged'] = [
      'title' => t('Flagged'),
      'real field' => 'uid',
      'field' => [
        'id' => 'flag_flagged',
        'label' => t('Flagged'),
        'help' => t('A boolean field to show whether the flag is set or not.'),
      ],
      'filter' => [
        'id' => 'flag_flagged',
        'label' => t('Flagged'),
        'help' => t('Filter to ensure content has or has not been flagged.'),
      ],
      'sort' => [
        'id' => 'flag_flagged',
        'label' => t('Flagged'),
        'help' => t('Sort by whether entities have or have not been flagged.'),
      ],
    ];
    // @todo fix this.
    /*
    $data['flag_counts']['count'] = [
      'title' => t('Flag counter'),
      'help' => t('The number of times a piece of content is flagged by any user.'),
      'field' => [
        'id' => 'numeric',
      ],
      'sort' => array(
        'id' => 'groupby_numeric',
      ),
      'filter' => array(
        'id' => 'numeric',
      ),
      'argument' => array(
        'id' => 'numeric',
      ),
    ];

    $data['flag_counts']['last_updated'] = [
      'title' => t('Time last flagged'),
      'help' => t('The time a piece of content was most recently flagged by any user.'),
      'field' => [
        'id' => 'date',
      ],
      'sort' => array(
        'id' => 'date',
      ),
      'filter' => array(
        'id' => 'date',
      ),
      'argument' => array(
        'id' => 'date',
      ),
    ];*/

    // @todo Remove this when data table is actually used.
    unset($data['flagging_data']);

    return $data;
  }

}
