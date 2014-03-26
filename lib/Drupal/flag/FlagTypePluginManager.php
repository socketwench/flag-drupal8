<?php
/**
 * Created by PhpStorm.
 * User: tess
 * Date: 10/6/13
 * Time: 4:57 PM
 */

namespace Drupal\flag;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Language\LanguageManager;
use Drupal\Core\Plugin\DefaultPluginManager;

class FlagTypePluginManager extends DefaultPluginManager {

  /**
   * Constructs a new FlagTypePluginManager.
   *
   * @param \Traversable $namespaces
   *   An object that implements \Traversable which contains the root paths
   *   keyed by the corresponding namespace to look for plugin implementations,
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache_backend
   *   Cache backend instance to use.
   * @param \Drupal\Core\Language\LanguageManager $language_manager
   *   The language manager.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler to invoke the alter hook with.
   */
  public function __construct(\Traversable $namespaces, CacheBackendInterface $cache_backend, LanguageManager $language_manager, ModuleHandlerInterface $module_handler) {
    parent::__construct('Plugin/Flag', $namespaces, $module_handler, 'Drupal\flag\Annotation\FlagType');

    $this->alterInfo('flag_type_info');
    $this->setCacheBackend($cache_backend, $language_manager, 'flag');
  }

  public function getAllFlagTypes() {
    $flagTypes = array();

    foreach ($this->getDefinitions() as $pluginID => $pluginDef) {
      $flagTypes[$pluginID] = t($pluginDef['title']);
    }

    return $flagTypes;
  }

} 