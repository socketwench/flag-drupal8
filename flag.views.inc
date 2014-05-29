<?php
/**
 * Created by PhpStorm.
 * User: tess
 * Date: 5/15/14
 * Time: 9:26 PM
 */

function flag_views_data() {
  $data = array();
  $data['flagging']['table']['group'] = t('Flag');
  $data['flagging']['table']['join'] = array(
    'node' => array(
      'type' => 'INNER',
      'left_field' => 'nid',
      'field' =>  'entity_id',
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

  $data['flagging']['timestamp'] = array(
    'title' => t('Flagged time'),
    'help' => t('Display the time the content was flagged by a user.'),
    'field' => array(
      'id' => 'date',
      //'click sortable' => TRUE,
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
    'title' => t('Entity ID'),
    'help' => t('The ID of the entity'),
    'sort' => array(
      'id' => 'standard',
    ),
  );

/*
  // Flag content links.
  $data['flagging']['ops'] = array(
    'title' => t('Flag link'),
    'help' => t('Display flag/unflag link.'),
    'field' => array(
      'handler' => 'flag_handler_field_ops',
    ),
  );*/

  return $data;
}

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


/*
function flag_views_data() {
  $data = array();




  $data['flagging']['table']['group'] = t('Flags');
  $data['flagging']['table']['base'] = array(
    'field' => 'id',
    'title' => t('Flag Id'),
    'help' => t('The flagging Id.'),
  );
  $data['flagging']['table']['entity type'] = 'flagging';

//  $data['flag_counts']['table']['group'] = t('Flags');

  $data['flagging']['uid'] = array(
    'title' => t('User uid'),
    'help' => t('The user that flagged an item. If you need more fields than the uid add the "Flags: User" relationship.'),
    'relationship' => array(
      'title' => t('User'),
      'help' => t('Relate an item to the user that flagged it.'),
      'id' => 'standard',
      'base' => 'users',
      'field' => uid,
      'label' => t('Flag user'),
    ),
/*    'filter' => array(
      'handler' => 'views_handler_filter_user_name',
    ),
    'argument' => array(
      'handler' => 'views_handler_argument_numeric',
    ),
    'field' => array(
      'handler' => 'views_handler_field_user',
    ),
  );

  $data['flagging']['timestamp'] = array(
    'title' => t('Flagged time'),
    'help' => t('Display the time the content was flagged by a user.'),
    'field' => array(
      'handler' => 'views_handler_field_date',
      'click sortable' => TRUE,
    ),
    'sort' => array(
      'id' => 'date',
    ),
    'filter' => array(
      'id' => 'date',
    ),
//    'argument' => array(
//      'handler' => 'views_handler_argument_date',
//    ),
  );
/*
  // Argument for content ID, used for "Who's flagged this" views.
  $data['flagging']['entity_id'] = array(
    'title' => t('Content ID'),
    'help' => t('The unique ID of the object that has been flagged.'),
    'argument' => array(
      'handler' => 'flag_handler_argument_entity_id',
    ),
  );

  // Specialized is null/is not null filter.
  $data['flagging']['flagged'] = array(
    'title' => t('Flagged'),
    'real field' => 'uid',
    'field' => array(
      'handler' => 'flag_handler_field_flagged',
      'label' => t('Flagged'),
      'help' => t('A boolean field to show whether the flag is set or not.'),
    ),
    'filter' => array(
      'handler' => 'flag_handler_filter_flagged',
      'label' => t('Flagged'),
      'help' => t('Filter to ensure content has or has not been flagged.'),
    ),
    'sort' => array(
      'handler' => 'flag_handler_sort_flagged',
      'label' => t('Flagged'),
      'help' => t('Sort by whether entities have or have not been flagged.'),
    ),
  );

  // Flag content links.
  $data['flagging']['ops'] = array(
    'title' => t('Flag link'),
    'help' => t('Display flag/unflag link.'),
    'field' => array(
      'handler' => 'flag_handler_field_ops',
    ),
  );

  $data['flag_counts']['count'] = array(
    'title' => t('Flag counter'),
    'help' => t('The number of times a piece of content is flagged by any user.'),
    'field' => array(
      'handler' => 'views_handler_field_numeric',
      'click sortable' => TRUE,
    ),
    'sort' => array(
      'handler' => 'views_handler_sort',
    ),
    'filter' => array(
      'handler' => 'views_handler_filter_numeric',
    ),
    'argument' => array(
      'handler' => 'views_handler_argument_numeric',
    ),
  );

  $data['flag_counts']['last_updated'] = array(
    'title' => t('Time last flagged'),
    'help' => t('The time a piece of content was most recently flagged by any user.'),
    'field' => array(
      'handler' => 'views_handler_field_date',
      'click sortable' => TRUE,
    ),
    'sort' => array(
      'handler' => 'views_handler_sort_date',
    ),
    'filter' => array(
      'handler' => 'views_handler_filter_date',
    ),
    'argument' => array(
      'handler' => 'views_handler_argument_date',
    ),
  );

  return $data;
}

*/