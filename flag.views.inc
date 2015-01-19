<?php
/**
 * @file
 * Contains views API hooks for Flag module.
 */

/**
 * Implements hook_views_data_alter().
 */
function flag_views_data_alter(array &$data) {
  /** @var \Drupal\flag\Entity\Flag[] $flags */
  $flags = \Drupal::service('flag')->getFlags();

  foreach ($flags as $fid => $flag) {
    /** @var \Drupal\flag\Plugin\Flag\EntityFlagType $flag_type_plugin */
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
        'id' => 'flag',
        'flaggable' => $entity_type,
      ],
    ];

  }
}
