<?php
/**
 * Created by PhpStorm.
 * User: tess
 * Date: 10/5/13
 * Time: 1:02 PM
 */

namespace Drupal\flag\Plugin\Derivative;

use Drupal\Component\Plugin\Derivative\DerivativeBase;
use Drupal\Core\Entity\EntityStorageControllerInterface;
use Drupal\Core\Plugin\Discovery\ContainerDerivativeInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class FlagBase  extends DerivativeBase implements ContainerDerivativeInterface {

  protected $flagStorage;

  public function __construct(EntityStorageControllerInterface $flag_storage) {
    $this->flagStorage = $flag_storage;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, $base_plugin_id) {
    return new static(
      $container->get('entity.manager')->getStorageController('flag')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getDerivativeDefinitions(array $base_plugin_definition) {
    foreach ($this->flagStorage->loadMultiple() as $flag => $entity) {
      $this->derivatives[$flag] = $base_plugin_definition;
      $this->derivatives[$flag]['admin_label'] = $entity->label();
      $this->derivatives[$flag]['cache'] = DRUPAL_NO_CACHE;
    }
    return $this->derivatives;
  }
} 