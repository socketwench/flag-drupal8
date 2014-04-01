<?php
/**
 * Created by PhpStorm.
 * User: tess
 * Date: 10/12/13
 * Time: 2:57 PM
 */

namespace Drupal\flag\Entity;

use Drupal\Core\Plugin\DefaultSinglePluginBag;
use Drupal\Compontent\Plugin\ConfigurablePluginInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Config\Entity\ConfigEntityBase;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\Annotation\EntityType;
use Drupal\Core\Annotation\Translation;
use Drupal\flag\FlagInterface;

/**
 * Class Flag
 * @package Drupal\flag\Entity
 *
 * @ConfigEntityType(
 *   id = "flag_flag",
 *   label = @Translation("Flag"),
 *   admin_permission = "administer flags",
 *   controllers = {
 *     "list_builder" = "Drupal\flag\Controller\FlagListController",
 *     "form" = {
 *       "add" = "Drupal\flag\Form\FlagAddForm",
 *       "edit" = "Drupal\flag\Form\FlagEditForm",
 *       "delete" = "Drupal\flag\Form\FlagDeleteForm"
 *     }
 *   },
 *   bundle_of = "flagging",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *   },
 *   links = {
 *     "edit-form" = "flag_edit",
 *     "delete-form" = "flag_delete"
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
   * The entity type this flag works with.
   *
   * @var string
   */
  public $entity_type = NULL;

  /**
   * The flag label.
   *
   * @var string
   */
  public $label;

  /**
   * Whether this flag state should act as a single toggle to all users.
   *
   * @var bool
   */
  protected $is_global = FALSE;

  /**
   * Whether this flag is enabled.
   *
   * @var bool
   */
  protected $enabled = TRUE;

  /**
   * The sub-types, AKA bundles, this flag applies to.
   *
   * This may be an empty array to indicate all types apply.
   *
   * @var array
   */
  public $types = array();

  /**
   * The text for the "flag this" link for this flag.
   *
   * @var string
   */
  public $flag_short = '';

  /**
   * The description of the "flag this" link.
   *
   * @var string
   */
  public $flag_long = '';

  /**
   * Message displayed after flagging an entity.
   *
   * @var string
   */
  public $flag_message = '';

  /**
   * The text for the "unflag this" link for this flag.
   *
   * @var string
   */
  public $unflag_short = '';

  /**
   * The description of the "unflag this" link.
   *
   * @var string
   */
  public $unflag_long = '';

  /**
   * Message displayed after flagging an entity.
   *
   * @var string
   */
  public $unflag_message = '';

  /**
   * Message displayed if users aren't allowed to unflag.
   *
   * @var string
   */
  public $unflag_denied_text = '';

  /**
   * The plugin ID of the flag type.
   *
   * @var string
   */
  protected $flag_type;

  protected $flagTypeBag;

  protected $flagTypeConfig = array();

  /**
   * The link type used by the flag, as defined in hook_flag_link_type_info().
   *
   * @var string
   */
  protected $link_type;

  protected $linkTypeBag;

  protected $linkTypeConfig = array();

  /**
   * The weight of the flag.
   *
   * @var int
   */
  public $weight = 0;

  /**
   * Overrides \Drupal\Core\Config\Entity\ConfigEntityBase::__construct();
   */
  public function __construct(array $values, $entity_type) {
    parent::__construct($values, $entity_type);

    $this->flagTypeBag = new DefaultSinglePluginBag(\Drupal::service('plugin.manager.flag.flagtype'),
                                                    $this->flag_type, $this->flagTypeConfig);

    $this->linkTypeBag = new DefaultSinglePluginBag(\Drupal::service('plugin.manager.flag.linktype'),
                                                    $this->link_type, $this->linkTypeConfig);
  }

  public function enable() {
    $this->enabled = TRUE;
  }

  public function disable() {
    $this->enabled = FALSE;
  }

  public function isFlagged(EntityInterface $entity, AccountInterface $account = NULL) {
    if($account == NULL) {
      global $user;
      $account = $user;
    }

    $result = \Drupal::entityQuery('flagging')
      ->condition('uid', $account->id())
      ->condition('fid', $this->id())
      ->condition('entity_id', $entity->id())
      ->execute();

    if (isset($result['node'])) {
      $flagging_ids = array_keys($result['flagging']);
    }
  }

  /**
   * Get the flag type plugin for this flag.
   *
   * @return FlagTypePluginInterface
   */
  public function getFlagTypePlugin() {
    return $this->flagTypeBag->get($this->flag_type);
  }

  /**
   * Set the flag type plugin.
   *
   * @param string $pluginID
   *   A string containing the flag type plugin ID.
   */
  public function setFlagTypePlugin($pluginID) {
    $this->flag_type = $pluginID;
    $this->flagTypeBag->addInstanceId($pluginID);

    // Get the entity type from the plugin definition.
    $plugin = $this->getFlagTypePlugin();
    $pluginDef = $plugin->getPluginDefinition();
    $this->entity_type = $pluginDef['entity_type'];
  }

  /**
   * Get the link type plugin for this flag.
   *
   * @return LinkTypePluginInterface
   */
  public function getLinkTypePlugin() {
    return $this->linkTypeBag->get($this->link_type);
  }

  /**
   * Set the link type plugin.
   *
   * @param string $pluginID
   *   A string containing the link type plugin ID.
   */
  public function setlinkTypePlugin($pluginID) {
    $this->link_type = $pluginID;
    $this->linkTypeBag->addInstanceId($pluginID);
  }

  /**
   * Provides permissions for this flag.
   *
   * @return
   *  An array of permissions for hook_permission().
   */
  function getPermissions() {
    return array(
      "flag $this->id" => array(
        'title' => t('Flag %flag_title', array(
          '%flag_title' => $this->label,
        )),
      ),
      "unflag $this->id" => array(
        'title' => t('Unflag %flag_title', array(
          '%flag_title' => $this->label,
        )),
      ),
    );
  }

  public function isGlobal() {
    return $this->is_global;
  }

  public function setGlobal($isGlobal = TRUE) {
    if ($isGlobal) {
      $this->is_global = TRUE;
    }
    else {
      $this->is_global = FALSE;
    }
  }

  public function getEntityType() {
    return $this->entity_type;
  }

  /**
   * @param EntityStorageControllerInterface $storage_controller
   */
  public function preSave(EntityStorageInterface $storage_controller) {
    parent::preSave($storage_controller);

    // Save the Flag Type configuration.
    $flagTypePlugin = $this->getFlagTypePlugin();
    $this->set('flagTypeConfig', $flagTypePlugin->getConfiguration());

    // Save the Link Type configuration.
    $linkTypePlugin = $this->getLinkTypePlugin();
    $this->set('linkTypeConfig', $linkTypePlugin->getConfiguration());
  }

  public function toArray() {
    $properties = parent::toArray();
    $names = array(
      'roles',
      'flag_type',
      'link_type',
      'flagTypeConfig',
      'linkTypeConfig',
    );

    foreach ($names as $name) {
      $properties[$name] = $this->get($name);
    }

    return $properties;
  }

} 