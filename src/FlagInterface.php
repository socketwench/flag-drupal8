<?php
/**
 * @file
 * Contains \Drupal\flag\FlagInterface.
 */

namespace Drupal\flag;

use Drupal\Core\Config\Entity\ConfigEntityInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityWithPluginCollectionInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * Defines the interface for Flag entities.
 */
interface FlagInterface extends ConfigEntityInterface, EntityWithPluginCollectionInterface {

  /* @todo: Add getters and setters as necessary. */

  /**
   * Enables the Flag for use.
   */
  public function enable();

  /**
   * Disables the Flag for use.
   */
  public function disable();

  /**
   * @return bool
   *  TRUE if the flag is enabled, FALSE otherwise.
   */
  public function isEnabled();

  /**
   * Returns true of there's a flagging for this flag and the given entity.
   *
   * @param EntityInterface $entity
   *   The flaggable entity.
   * @param AccountInterface $account
   *   Optional. The account of the user that flagged the entity.
   *
   * @return bool
   *   True if the given entity is flagged, FALSE otherwise.
   */
  public function isFlagged(EntityInterface $entity, AccountInterface $account = NULL);

  /**
   * Returns the flaggable entity type ID.
   *
   * @return string
   *   The flaggable entity ID.
   */
  public function getFlaggableEntityType();

  /**
   * Set the flag type plugin.
   *
   * @param string $plugin_id
   *   A string containing the flag type plugin ID.
   */
  public function setFlagTypePlugin($plugin_id);

  /**
   * Get the link type plugin for this flag.
   *
   * @return \Drupal\flag\ActionLinkTypePluginInterface
   *   The link type plugin for the flag.
   */
  public function getLinkTypePlugin();

  /**
   * Set the link type plugin.
   *
   * @param string $plugin_id
   *   A string containing the link type plugin ID.
   */
  public function setlinkTypePlugin($plugin_id);

  /**
   * Returns an associative array of permissions used by flag_permission().
   *
   * Typically there are two permissions, one to flag, and one to unflag.
   * Each key of the array is the permission name. Each value is an array with
   * a single element, 'title', which provides the display name for the
   * permission.
   *
   * @return array
   *   An array of permissions.
   *
   * @see \Drupal\flag\Entity\Flag::getPermissions()
   */
  public function getPermissions();

  /**
   * Returns true if the flag is global, false otherwise.
   *
   * Global flags disable the default behavior of a Flag. Instead of each
   * user being able to flag or unflag the entity, a global flag may be flagged
   * once for all users.
   *
   * @return bool
   *   TRUE if the flag is global, FALSE otherwise.
   */
  public function isGlobal();

  /**
   * Sets the flag as global or not.
   *
   * @param bool $is_global
   *   TRUE to mark the flag as global, FALSE for the default behavior.
   *
   * @see \Drupal\flag\Entity\Flag::isGlobal()
   */
  public function setGlobal($is_global);

  /**
   * The flag short text.
   *
   * @param string $text
   *   The flag short text to set.
   */
  public function setFlagShortText($text);

  /**
   * Gets the flag short text.
   *
   * @return string
   *   A string containing the flag short text.
   */
  public function getFlagShortText();

  /**
   * Gets the flag long text.
   *
   * @return string
   *   A string containing the flag long text.
   */
  public function getFlagLongText();

  /**
   * Sets the flag long text.
   *
   * @param string $flag_long
   *   The flag long text to use.
   */
  public function setFlagLongText($flag_long);

  /**
   * Gets the flag message.
   *
   * @return string
   *   A string continaing the flag message.
   */
  public function getFlagMessage();

  /**
   * Sets the flag message.
   *
   * @param string $flag_message
   *   The flag message text to use.
   */
  public function setFlagMessage($flag_message);

  /**
   * Gets the unflag short text.
   *
   * @return string
   *   A string containing the unflag short text.
   */
  public function getUnflagShortText();

  /**
   * Sets the unflag short text.
   *
   * @param string $flag_short
   *   The unflag short text to use.
   */
  public function setUnflagShortText($flag_short);

  /**
   * Gets the flag long text.
   *
   * @return string
   *   A string containing the unflag long text.
   */
  public function getUnflagLongText();

  /**
   * Sets the unflag long text.
   *
   * @param string $unflag_long
   *   The unflag lnog text to use.
   */
  public function setUnflagLongText($unflag_long);

  /**
   * Gets the unflag message.
   *
   * @return string
   *   The unflag message text to use.
   */
  public function getUnflagMessage();

  /**
   * Sets the unflag message.
   *
   * @param string $unflag_message
   *   The unflag message text to use.
   */
  public function setUnflagMessage($unflag_message);

  /**
   * Get the flag's weight.
   *
   * @return int
   *   The flag's weight.
   */
  public function getWeight();

  /**
   * Set the flag's weight.
   *
   * @param int $weight
   *   An int containing the flag weight to use.
   */
  public function setWeight($weight);

  /**
   * Get the flag's unflag denied message text.
   *
   * @return string
   *   A string containing the unflag denied message text.
   */
  public function getUnflagDeniedText();

  /**
   * Set's the flag's unflag denied message text.
   *
   * @param string $unflag_denied_text
   *   The unflag denied message text to use.
   */
  public function setUnflagDeniedText($unflag_denied_text);

  /**
   * {@inheritdoc}
   */
  public function getPluginCollections();

  /**
   * User access permission for flagging actions.
   *
   * Checks whether a user has permission to flag/unflag or not.
   *
   * @param string $action
   *   An indicator flag.
   * @param AccountInterface $account
   *   (optional) An AccountInterface object.
   *
   * @return bool|null
   *   Returns a bool defining the users access permission for flagging action.
   */
  public function hasActionAccess($action, AccountInterface $account = NULL);

}
