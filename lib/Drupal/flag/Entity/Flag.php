<?php
/**
 * Created by PhpStorm.
 * User: tess
 * Date: 10/12/13
 * Time: 2:57 PM
 */

namespace Drupal\flag\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;
use Drupal\Core\Entity\EntityStorageControllerInterface;
use Drupal\Core\Entity\Annotation\EntityType;
use Drupal\Core\Annotation\Translation;
use Drupal\flag\FlagInterface;

/**
 * Class Flag
 * @package Drupal\flag\Entity
 *
 * @EntityType(
 *   id = "flag_flag",
 *   label = @Translation("Flag"),
 *   module = "flag",
 *   controllers = {
 *     "storage" = "Drupal\Core\Config\Entity\ConfigStorageController",
 *     "list" = "Drupal\flag\Controller\FlagListController",
 *   },
 *   config_prefix = "flag.flag",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "uuid" = "uuid"
 *   }
 * )
 *
 */
class Flag extends ConfigEntityBase implements FlagInterface {

  //@todo Add AccessController, ListController, and form controllers. See \Drupal\contact\Entity\Category

  /**
   * The flag ID.
   *
   * @var string
   */
  public $id;

  /**
   * The flag UUID.
   *
   * @var string
   */
  public $uuid;

  /**
   * The flag label.
   *
   * @var string
   */
  public $label;

} 