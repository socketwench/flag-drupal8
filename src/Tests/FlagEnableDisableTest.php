<?php
/**
 * @file
 * Contains \Drupal\flag\Tests\FlagEnableDisableTest.
 */

namespace Drupal\flag\Tests;

use Drupal\simpletest\WebTestBase;
use Drupal\user\Entity\Role;

/**
 * Test the disabling and enabling a flag.
 *
 * @group flag
 */
class FlagEnableDisableTest extends WebTestBase {

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

  protected $node_id;

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

  protected $flagShortText = 'Flag this stuff';
  protected $unflagShortText = 'Unflag this stuff';

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
   * Test the enabling and disabling of a flag from the Admin UI.
   */
  public function testDiableEnableFlag() {
    // Create and log in our user.
    $this->adminUser = $this->drupalCreateUser([
      'administer flags',
      'administer flagging display',
      'administer node display',
    ]);

    $this->drupalLogin($this->adminUser);

    $this->doCreateFlag();
    $this->doCreateNode();
    $this->doDisableFlag();
    $this->doEnableFlag();
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

    $edit = [
      'label' => $this->label,
      'id' => $this->id,
      'types[' . $this->nodeType . ']' => $this->nodeType,
      'flag_short' => $this->flagShortText,
      'unflag_short' => $this->unflagShortText,
    ];
    $this->drupalPostForm(NULL, $edit, t('Create Flag'));
  }

  /**
   * Create a node and flag it.
   */
  public function doCreateNode() {
    $node = $this->drupalCreateNode(['type' => $this->nodeType]);
    $this->node_id = $node->id();

    // Grant the flag permissions to the authenticated role, so that both
    // users have the same roles and share the render cache.
    $role = Role::load(DRUPAL_AUTHENTICATED_RID);
    $role->grantPermission('flag ' . $this->id);
    $role->grantPermission('unflag ' . $this->id);
    $role->save();

    // Click the flag link.
    $this->drupalGet('node/' . $this->node_id);

    $this->assertText($this->flagShortText);
  }

  /**
   * Disable the flag and ensure the link does not appear on entities.
   */
  public function doDisableFlag() {
    $this->drupalGet('admin/structure/flags');
    $this->assertText(t('enabled'));

    $this->drupalPostForm('flag/disable/' . $this->id, [], t('Disable'));
    $this->assertResponse(200);

    $this->drupalGet('admin/structure/flags');
    $this->assertText(t('disabled'));

    $this->drupalGet('node/' . $this->node_id);
    $this->assertNoText($this->flagShortText);
  }

  /**
   * Enable the flag and ensure it appears on target entities.
   */
  public function doEnableFlag() {
    $this->drupalGet('admin/structure/flags');
    $this->assertText(t('disabled'));

    $this->drupalPostForm('flag/enable/' . $this->id, [], t('Enable'));
    $this->assertResponse(200);

    $this->drupalGet('admin/structure/flags');
    $this->assertText(t('enabled'));

    $this->drupalGet('node/' . $this->node_id);
    $this->assertText($this->flagShortText);
  }
}
