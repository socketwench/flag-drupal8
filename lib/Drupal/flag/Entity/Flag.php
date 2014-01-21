<?php
/**
 * Created by PhpStorm.
 * User: tess
 * Date: 10/12/13
 * Time: 2:57 PM
 */

namespace Drupal\flag\Entity;

use Drupal\Component\Plugin\DefaultSinglePluginBag;
use Drupal\Compontent\Plugin\ConfigurablePluginInterface;
use Drupal\Core\Session\AccountInterface;
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
 *   admin_permission = "administer flags",
 *   controllers = {
 *     "storage" = "Drupal\Core\Config\Entity\ConfigStorageController",
 *     "list" = "Drupal\flag\Controller\FlagListController",
 *     "form" = {
 *       "add" = "Drupal\flag\Form\FlagAddForm",
 *       "edit" = "Drupal\flag\Form\FlagEditForm",
 *       "delete" = "Drupal\flag\Form\FlagDeleteForm"
 *     }
 *   },
 *   bundle_of = "flagging",
 *   config_prefix = "flag.flag",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "uuid" = "uuid"
 *   },
 *   links = {
 *     "edit-form" = "flag_edit"
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

  protected $roles = array(
    'flag' => array(),
    'unflag' => array(),
  );

  /**
   * Overrides \Drupal\Core\Config\Entity\ConfigEntityBase::__construct();
   */
  public function __construct(array $values, $entity_type) {
    parent::__construct($values, $entity_type);

    $this->flagTypeBag = new DefaultSinglePluginBag(\Drupal::service('plugin.manager.flag.flagtype'),
                                                    array($this->flag_type), $this->flagTypeConfig);

    $this->linkTypeBag = new DefaultSinglePluginBag(\Drupal::service('plugin.manager.flag.linktype'),
                                                    array($this->link_type), $this->linkTypeConfig);
  }

  public function enable() {
    $this->enabled = TRUE;
  }

  public function disable() {
    $this->enabled = FALSE;
  }

  public function isFlagged(AccountInterface $account = NULL) {
    if($account == NULL) {
      global $user;
      $account = $user;
    }

    $query = \Drupal::entityQuery('flagging');
    $query->condition('uid', $account->id());

    $result = $query->execute();

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
   * @return array
   */
  public function getRoles() {
    return $this->roles;
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

  /**
   * @param $roleID
   * @param $canFlag
   * @param $canUnflag
   */
  public function setPermission($roleID, $canFlag, $canUnflag) {
    if (!$canFlag && !$canUnflag) {
      unset($this->roles[$roleID]);
    }
    else {
      $this->roles[$roleID] = array(
        'flag' => $canFlag ? TRUE : FALSE,
        'unflag' => $canUnflag ? TRUE : FALSE,
      );
    }
  }

  /**
   * @param array $flagPermssions
   */
  public function setPermissions(array $flagRoles, array $unflagRoles) {
    $this->roles = array(
      'flag' => $flagRoles,
      'unflag' => $unflagRoles,
    );
  }

  public function canFlag(AccountInterface $account) {
    if ($account->id() == 0) {
      return TRUE;
    }

    if (in_array($account->getRoles(), $this->roles['flag'])) {
      return TRUE;
    }

    return FALSE;
  }

  public function canUnflag(AccountInterface $account) {
    return TRUE;
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

  /**
   * @param EntityStorageControllerInterface $storage_controller
   */
  public function preSave(EntityStorageControllerInterface $storage_controller) {
    parent::preSave($storage_controller);

    // Save the Flag Type configuration.
    $flagTypePlugin = $this->getFlagTypePlugin();
    $this->set('flagTypeConfig', $flagTypePlugin->getConfiguration());

    // Save the Link Type configuration.
    $linkTypePlugin = $this->getLinkTypePlugin();
    $this->set('linkTypeConfig', $linkTypePlugin->getConfiguration());

    foreach ($this->roles['flag'] as $rid => $value) {

      if (!empty($value)) {
        user_role_change_permissions($rid, "flag $this->id");
      }
    }

    foreach (user_roles() as $rid => $rinfo) {
      $perms = array();

      // Get the permissions from the $roles class variable.
      foreach ($this->roles as $action => $roles) {
        if (!empty($roles[$rid])) {
          $perms["$action $this->id"] = TRUE;
        }
        else {
          $perms["$action $this->id"] = FALSE;
        }
      }

      // Assign the permissions.
      user_role_change_permissions($rid, $perms);
    }
  }

  public function getExportProperties() {
    $properties = parent::getExportProperties();
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