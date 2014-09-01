<?php
/**
 * @file
 * Contains views API hooks for Flag module.
 */

/**
 * Implements hook_views_data().
 */
function flag_views_data() {
  $data = [];
  $data['flagging']['table']['group'] = t('Flag');
  $data['flag_counts']['table']['group'] = t('Flaggings');

  $data['flagging']['table']['join'] = [
    'node' => [
      'type' => 'LEFT',
      'left_field' => 'nid',
      'field' => 'entity_id',
    ],
  ];

  $data['flagging']['uid'] = [
    'title' => t('User uid'),
    'help' => t('The user that flagged an item. If you need more fields than the uid add the "Flags: User" relationship.'),
    'relationship' => [
      'base' => 'users',
      'title' => t('User'),
      'help' => t('Relate an item to the user that flagged it.'),
      'id' => 'standard',
      'label' => t('Flag user'),
    ],
    'filter' => [
      'id' => 'numeric',
    ],
    'argument' => [
      'id' => 'numeric',
    ],
    'field' => [
      'id' => 'user',
    ],
  ];

  $data['flagging']['created'] = [
    'title' => t('Last Flagged Time'),
    'help' => t('Display latest time the content was flagged by a user.'),
    'field' => [
      'id' => 'date',
    ],
    'sort' => [
      'id' => 'date',
    ],
    'filter' => [
      'id' => 'date',
    ],
    'argument' => [
      'id' => 'date',
    ],
  ];

  $data['flagging']['entity_id'] = [
    'title' => t('Flaggable ID'),
    'help' => t('The unique ID of the object that has been flagged.'),
    'sort' => [
      'id' => 'standard',
    ],
    'argument' => [
      'id' => 'FlagViewsFlaggableArgument',
    ],
  ];

  // Flag content links.
  $data['flagging']['link_flag'] = [
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
      'id' => 'flag_filter',
      'label' => t('Flagged'),
      'help' => t('Filter to ensure content has or has not been flagged.'),
    ],
    'sort' => [
      'id' => 'flag_sort',
      'label' => t('Flagged'),
      'help' => t('Sort by whether entities have or have not been flagged.'),
    ],
  ];

  $data['flag_counts']['count'] = [
    'title' => t('Flag counter'),
    'help' => t('The number of times a piece of content is flagged by any user.'),
    'field' => [
      'id' => 'numeric',
      // 'click sortable' => TRUE,
    ],
    /*'sort' => array(
      'id' => 'groupby_numeric',
    ),
    'filter' => array(
      'id' => 'numeric',
    ),
    'argument' => array(
      'id' => 'numeric',
    ),*/
  ];

  $data['flag_counts']['last_updated'] = [
    'title' => t('Time last flagged'),
    'help' => t('The time a piece of content was most recently flagged by any user.'),
    'field' => [
      'id' => 'date',
      // 'click sortable' => TRUE,
    ],
    /*'sort' => array(
      'id' => 'date',
    ),
    'filter' => array(
      'id' => 'date',
    ),
    'argument' => array(
      'id' => 'date',
    ),*/
  ];

  return $data;
}

/**
 * Implements hook_views_data_alter().
 */
function flag_views_data_alter(array &$data) {
  $flags = \Drupal::service('flag')->getFlags();

  foreach ($flags as $fid => $flag) {
    $flag_type_plugin = $flag->getFlagTypePlugin();
    $flag_type_def = $flag_type_plugin->getPluginDefinition();
    $entity_type = $flag_type_def['entity_type'];

    $info = \Drupal::entityManager()->getDefinition($entity_type);

    $base_table = $info->getBaseTable();
    if ($flag_type_def['entity_type'] == 'node') {
      $base_table = $info->getDataTable();
    }

    $data[$base_table]['flag_content_rel'] = [
      'title' => t('@entity_label flag', ['@entity_label' => $entity_type]),
      'help' => t('Limit results to only those entity flagged by a certain flag; Or display information about the flag set on a entity.'),
      'relationship' => [
        'group' => t('Flag'),
        'label' => t('Flags'),
        'base' => 'flagging',
        'base field' => 'entity_id',
        'relationship field' => $info->getKey('id'),
        'id' => 'flag_relationship',
        'flaggable' => $entity_type,
      ],
    ];

  }
}
