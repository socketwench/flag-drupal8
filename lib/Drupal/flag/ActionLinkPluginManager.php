<?php

/**
 * @file
 * Contains \Drupal\action_link\ActionLinkPluginManager.
 */

namespace Drupal\flag;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Language\LanguageManager;
use Drupal\Core\Plugin\DefaultPluginManager;

/**
 * Manages image effect plugins.
 */
class ActionLinkPluginManager extends DefaultPluginManager {

  /**
   * {@inheritdoc}
   */
  public function __construct(\Traversable $namespaces, CacheBackendInterface $cache_backend, LanguageManager $language_manager, ModuleHandlerInterface $module_handler) {
    parent::__construct('Plugin/ActionLink', $namespaces, $module_handler, 'Drupal\flag\Annotation\ActionLinkType');
    $this->alterInfo('flag_link_type_info');
    $this->setCacheBackend($cache_backend, $language_manager, 'flag_link_type_plugins');
  }

  public function getAllLinkTypes() {
    $linkTypes = array();
    foreach($this->getDefinitions() as $pluginID => $pluginDef) {
      $linkTypes[$pluginID] = t($pluginDef['label']);
    }

    return $linkTypes;
  }

}
