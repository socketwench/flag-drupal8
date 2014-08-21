<?php
/**
 * @file
 * Contains views API hooks for Flag module.
 */

/**
 * Implements hook_views_data().
 * @return array
 */
function flag_views_data() {
  $data = array();
  $data['flagging']['table']['group'] = t('Flag');
  $data['flag_counts']['table']['group'] = t('Flaggings');

  $data['flagging']['table']['join'] = array(
    'node' => array(
      'type' => 'LEFT',
      'left_field' => 'nid',
      'field' => 'entity_id',
    ),
  );

  $data['flagging']['uid'] = array(
    'title' => t('User uid'),
    'help' => t('The user that flagged an item. If you need more fields than the uid add the "Flags: User" relationship.'),
    'relationship' => array(
      'base' => 'users',
      'title' => t('User'),
      'help' => t('Relate an item to the user that flagged it.'),
      'id' => 'standard',
      'label' => t('Flag user'),
    ),
    'filter' => array(
      'id' => 'numeric',
    ),
    'argument' => array(
      'id' => 'numeric',
    ),
    'field' => array(
      'id' => 'user',
    ),
  );

  $data['flagging']['created'] = array(
    'title' => t('Last Flagged Time'),
    'help' => t('Display latest time the content was flagged by a user.'),
    'field' => array(
      'id' => 'date',
    ),
    'sort' => array(
      'id' => 'date',
    ),
    'filter' => array(
      'id' => 'date',
    ),
    'argument' => array(
      'id' => 'date',
    ),
  );

  $data['flagging']['entity_id'] = array(
    'title' => t('Flaggable ID'),
    'help' => t('The unique ID of the object that has been flagged.'),
    'sort' => array(
      'id' => 'standard',
    ),
    'argument' => array(
      'id' => 'FlagViewsFlaggableArgument',
    ),
  );

  // Flag content links.
  $data['flagging']['link_flag'] = array(
    'field' => array(
      'title' => t('Flag Links'),
      'help' => t('Display flag/unflag link.'),
      'id' => 'flag_link',
    ),
  );

  // Specialized is null/is not null filter.
  $data['flagging']['flagged'] = array(
    'title' => t('Flagged'),
    'real field' => 'uid',
    'field' => array(
      'id' => 'flag_flagged',
      'label' => t('Flagged'),
      'help' => t('A boolean field to show whether the flag is set or not.'),
    ),
    'filter' => array(
      'id' => 'flag_filter',
      'label' => t('Flagged'),
      'help' => t('Filter to ensure content has or has not been flagged.'),
    ),
    'sort' => array(
      'id' => 'flag_sort',
      'label' => t('Flagged'),
      'help' => t('Sort by whether entities have or have not been flagged.'),
    ),
  );

  $data['flag_counts']['count'] = array(
    'title' => t('Flag counter'),
    'help' => t('The number of times a piece of content is flagged by any user.'),
    'field' => array(
      'id' => 'numeric',
      // 'click sortable' => TRUE,
    ),
    /*'sort' => array(
      'id' => 'groupby_numeric',
    ),
    'filter' => array(
      'id' => 'numeric',
    ),
    'argument' => array(
      'id' => 'numeric',
    ),*/
  );

  $data['flag_counts']['last_updated'] = array(
    'title' => t('Time last flagged'),
    'help' => t('The time a piece of content was most recently flagged by any user.'),
    'field' => array(
      'id' => 'date',
      // 'click sortable' => TRUE,
    ),
    /*'sort' => array(
      'id' => 'date',
    ),
    'filter' => array(
      'id' => 'date',
    ),
    'argument' => array(
      'id' => 'date',
    ),*/
  );

  return $data;
}

/**
 * Implements hook_views_data_alter().
 * @param $data
 */
function flag_views_data_alter(&$data) {
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

    $data[$base_table]['flag_content_rel'] = array(
      'title' => t('@entity_label flag', array('@entity_label' => $entity_type)),
      'help' => t('Limit results to only those entity flagged by a certain flag; Or display information about the flag set on a entity.'),
      'relationship' => array(
        'group' => t('Flag'),
        'label' => t('Flags'),
        'base' => 'flagging',
        'base field' => 'entity_id',
        'relationship field' => $info->getKey('id'),
        'id' => 'flag_relationship',
        'flaggable' => $entity_type,
      ),
    );

  }
}
