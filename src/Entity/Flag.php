<?php
/**
 * @file
 * Contains \Drupal\flag\Entity\Flag.
 */

namespace Drupal\flag\Entity;

use Drupal\Core\Plugin\DefaultSinglePluginBag;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Config\Entity\ConfigEntityBase;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\flag\Event\FlagDeleteEvent;
use Drupal\flag\Event\FlagEvents;
use Drupal\flag\FlagInterface;

/**
 * Class Flag
 * @package Drupal\flag\Entity
 *
 * @ConfigEntityType(
 *   id = "flag",
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
 *     "edit-form" = "flag.edit",
 *     "delete-form" = "flag.delete"
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
   * {@inheritdoc}
   */
  public function __construct(array $values, $entity_type) {
    parent::__construct($values, $entity_type);

    if ($this->flag_type) {
      $this->flagTypeBag = new DefaultSinglePluginBag(\Drupal::service('plugin.manager.flag.flagtype'),
                                                      $this->flag_type, $this->flagTypeConfig);
    }

    if ($this->link_type) {
      $this->linkTypeBag = new DefaultSinglePluginBag(\Drupal::service('plugin.manager.flag.linktype'),
                                                      $this->link_type, $this->linkTypeConfig);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function enable() {
    $this->enabled = TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function disable() {
    $this->enabled = FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function isFlagged(EntityInterface $entity, AccountInterface $account = NULL) {
    if($account == NULL) {
      $account = \Drupal::currentUser();
    }

    $result = \Drupal::entityQuery('flagging')
      ->condition('uid', $account->id())
      ->condition('fid', $this->id())
      ->condition('entity_type', $entity->getEntityTypeId())
      ->condition('entity_id', $entity->id())
      ->execute();

    if (!empty($result)) {
      return TRUE;
    }

    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function getPluginBags() {
    return array(
      'flagTypeConfig' => $this->flagTypeBag,
      'linkTypeConfig' => $this->linkTypeBag,
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFlagTypePlugin() {
    return $this->flagTypeBag->get($this->flag_type);
  }

  /**
   * {@inheritdoc}
   */
  public function setFlagTypePlugin($pluginID) {
    $this->flag_type = $pluginID;
    //$this->flagTypeBag->addInstanceId($pluginID);

    // Workaround for https://www.drupal.org/node/2288805
    $this->flagTypeBag = new DefaultSinglePluginBag(\Drupal::service('plugin.manager.flag.flagtype'),
      $this->flag_type, $this->flagTypeConfig);

    // Get the entity type from the plugin definition.
    $plugin = $this->getFlagTypePlugin();
    $pluginDef = $plugin->getPluginDefinition();
    $this->entity_type = $pluginDef['entity_type'];
  }

  /**
   * {@inheritdoc}
   */
  public function getLinkTypePlugin() {
    return $this->linkTypeBag->get($this->link_type);
  }

  /**
   * {@inheritdoc}
   */
  public function setlinkTypePlugin($pluginID) {
    $this->link_type = $pluginID;

    //$this->linkTypeBag->addInstanceId($pluginID);

    // Workaround for https://www.drupal.org/node/2288805
    $this->linkTypeBag = new DefaultSinglePluginBag(\Drupal::service('plugin.manager.flag.linktype'),
      $this->link_type, $this->linkTypeConfig);
  }

  /**
   * {@inheritdoc}.
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
   * {@inheritdoc}.
   */
  public function hasActionAccess($action, AccountInterface $account = NULL) {
    if ($action === 'flag' || $action === 'unflag') {
      $account = $account ?: \Drupal::currentUser();
      return $account->hasPermission($action . ' ' . $this->id);
    }
    else {
      // @todo: Is this the correct response?
      return FALSE;
    }
  }

  /**
   * {@inheritdoc}.
   */
  public function isGlobal() {
    return $this->is_global;
  }

  /**
   * {@inheritdoc}.
   */
  public function setGlobal($isGlobal = TRUE) {
    if ($isGlobal) {
      $this->is_global = TRUE;
    }
    else {
      $this->is_global = FALSE;
    }
  }

  /**
   * {@inheritdoc}.
   */
  public function getFlaggableEntityType() {
    return $this->entity_type;
  }

  /**
   * {@inheritdoc}
   */
  public function preSave(EntityStorageInterface $storage) {
    parent::preSave($storage);
/*
    // Save the Flag Type configuration.
    $flagTypePlugin = $this->getFlagTypePlugin();
    $this->set('flagTypeConfig', $flagTypePlugin->getConfiguration());

    // Save the Link Type configuration.
    $linkTypePlugin = $this->getLinkTypePlugin();
    $this->set('linkTypeConfig', $linkTypePlugin->getConfiguration());
*/
    // Reset the render cache for the entity.
    \Drupal::entityManager()
      ->getViewBuilder($this->getFlaggableEntityType())
      ->resetCache();
    // Clear entity extra field caches.
    \Drupal::entityManager()->clearCachedFieldDefinitions();

  }

  /**
   * {@inheritdoc}
   */
  public static function preDelete(EntityStorageInterface $storage, array $entities) {
    parent::preDelete($storage, $entities);

    foreach ($entities as $entity) {
      \Drupal::service('event_dispatcher')
        ->dispatch(FlagEvents::FLAG_DELETED, new FlagDeleteEvent($entity));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function toArray() {
    $properties = parent::toArray();
    $names = array(
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
