<?php
/**
 * @file
 * Contains \Drupal\flag\Tests\FlagConfirmFormTest.
 */

namespace Drupal\flag\Tests;

use Drupal\simpletest\WebTestBase;
use Drupal\user\Entity\Role;

/**
 * Tests the confirm form link type.
 *
 * @group flag
 */
class FlagConfirmFormTest extends WebTestBase {

  /**
   * Set to TRUE to strict check all configuration saved.
   *
   * @see \Drupal\Core\Config\Testing\ConfigSchemaChecker
   *
   * @var bool
   */
  protected $strictConfigSchema = FALSE;

  /**
   * The label of the flag to create for the test.
   *
   * @var string
   */
  protected $label = 'Test label 123';

  /**
   * The ID of the flag to create for the test.
   *
   * @var string
   */
  protected $id = 'test_label_123';

  /**
   * The flag link type.
   *
   * @var string
   */
  protected $flagLinkType;

  /**
   * The node type to use in the test.
   *
   * @var string
   */
  protected $nodeType = 'article';

  protected $flagConfirmMessage = 'Flag test label 123?';
  protected $unflagConfirmMessage = 'Unflag test label 123?';

  /**
   * User object.
   *
   * @var \Drupal\user\Entity\User|false
   */
  protected $adminUser;

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = array('views', 'flag', 'node', 'field_ui');

  /**
   * Test the confirm form link type.
   */
  public function testCreateConfirmFlag() {
    // Create and log in our user.
    $this->adminUser = $this->drupalCreateUser([
      'administer flags',
      'administer flagging display',
      'administer node display',
    ]);

    $this->drupalLogin($this->adminUser);
    $this->doCreateFlag();
    $this->doCreateNode();
  }

  /**
   * Create a node type and a flag.
   */
  public function doCreateFlag() {
    // Create content type.
    $this->drupalCreateContentType(['type' => $this->nodeType]);

    // Test with minimal value requirement.
    $edit = [
      'flag_entity_type' => 'flagtype_node',
    ];
    $this->drupalPostForm('admin/structure/flags/add', $edit, t('Continue'));

    // Update the flag.
    $edit = [
      'link_type' => 'confirm',
    ];
    $this->drupalPostAjaxForm(NULL, $edit, 'link_type');

    // Check confirm form field entry.
    $this->assertText(t('Flag confirmation message'));
    $this->assertText(t('Unflag confirmation message'));

    $edit = [
      'label' => $this->label,
      'id' => $this->id,
      'types[' . $this->nodeType . ']' => $this->nodeType,
      'flag_confirmation' => $this->flagConfirmMessage,
      'unflag_confirmation' => $this->unflagConfirmMessage,
    ];
    $this->drupalPostForm(NULL, $edit, t('Create Flag'));

    // Check to see if the flag was created.
    $this->assertText(t('Flag @this_label has been added.', ['@this_label' => $this->label]));
  }

  /**
   * Create a node and flag it.
   */
  public function doCreateNode() {
    $node = $this->drupalCreateNode(['type' => $this->nodeType]);
    $node_id = $node->id();

    // Grant the flag permissions to the authenticated role, so that both
    // users have the same roles and share the render cache.
    $role = Role::load(DRUPAL_AUTHENTICATED_RID);
    $role->grantPermission('flag ' . $this->id);
    $role->grantPermission('unflag ' . $this->id);
    $role->save();

    // Create and login a new user.
    $user_1 = $this->drupalCreateUser();
    $this->drupalLogin($user_1);

    // Click the flag link.
    $this->drupalGet('node/' . $node_id);
    $this->clickLink(t('Flag this item'));

    // Check if we have the confirm form message displayed.
    $this->assertText($this->flagConfirmMessage);

    // Submit the confirm form.
    $this->drupalPostForm('flag/confirm/flag/' . $this->id . '/' . $node_id, [], t('Flag'));
    $this->assertResponse(200);

    // Check that the node is flagged.
    $this->drupalGet('node/' . $node_id);
    $this->assertLink(t('Unflag this item'));
  }

}
