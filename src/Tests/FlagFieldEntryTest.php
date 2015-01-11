<?php
/**
 * @file
 * Contains \Drupal\flag\Tests\FlagFieldEntryTest.
 */

namespace Drupal\flag\Tests;

use Drupal\simpletest\WebTestBase;
use Drupal\user\Entity\Role;

/**
 * Test the Field Entry link type.
 *
 * @group flag
 */
class FlagFieldEntryTest extends WebTestBase {

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

  protected $nodeId;

  protected $flagConfirmMessage = 'Flag test label 123?';
  protected $flagDetailsMessage = 'Enter flag test label 123 details';
  protected $unflagConfirmMessage = 'Unflag test label 123?';

  protected $flagFieldId = 'flag_text_field';
  protected $flagFieldLabel = 'Flag Text Field';
  protected $flagFieldValue;

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
  public static $modules = array('views', 'flag', 'node', 'field_ui', 'text');

  /**
   * Create a new flag with the Field Entry type, and add fields.
   */
  public function testCreateFieldEntryFlag() {
    $this->adminUser = $this->drupalCreateUser([
      'administer flags',
      'administer flagging display',
      'administer flagging fields',
      'administer node display',
    ]);

    $this->drupalLogin($this->adminUser);
    $this->doCreateFlag();
    $this->doAddFields();
    $this->doCreateFlagNode();
    $this->doEditFlagField();
  }

  /**
   * Create a node type and flag.
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
      'label' => $this->label,
      'id' => $this->id,
      'types[' . $this->nodeType . ']' => $this->nodeType,
      'link_type' => 'field_entry',
    ];
    $this->drupalPostAjaxForm(NULL, $edit, 'link_type');

    // Check confirm form field entry.
    $this->assertText(t('Flag confirmation message'));
    $this->assertText(t('Enter flagging details message'));
    $this->assertText(t('Unflag confirmation message'));

    $edit = [
      'flag_confirmation' => $this->flagConfirmMessage,
      'flagging_edit_title' => $this->flagDetailsMessage,
      'unflag_confirmation' => $this->unflagConfirmMessage,
    ];
    $this->drupalPostForm(NULL, $edit, t('Create Flag'));

    // Check to see if the flag was created.
    $this->assertText(t('Flag @this_label has been added.', ['@this_label' => $this->label]));
  }

  /**
   * Add fields to flag.
   */
  public function doAddFields() {
    $edit = [
      'fields[_add_new_field][label]' => $this->flagFieldLabel,
      'fields[_add_new_field][field_name]' => $this->flagFieldId,
      'fields[_add_new_field][type]' => 'text',
    ];
    $this->drupalPostForm('admin/structure/flags/manage/' . $this->id . '/fields', $edit, t('Save'));

    $edit = [
      'field_storage[cardinality]' => '-1',
      'field_storage[cardinality_number]' => '1',
    ];
    $this->drupalPostForm(NULL, $edit, t('Save field settings'));

    $this->assertText(t('Updated field') . ' ' . $this->flagFieldLabel . ' ' . t('field settings.'));
  }

  /**
   * Create a node and flag it.
   */
  public function doCreateFlagNode() {
    $node = $this->drupalCreateNode(['type' => $this->nodeType]);
    $this->nodeId = $node->id();

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
    $this->drupalGet('node/' . $this->nodeId);
    $this->clickLink(t('Flag this item'));

    // Check if we have the confirm form message displayed.
    $this->assertText($this->flagConfirmMessage);

    // Enter the field value and submit it.
    $this->flagFieldValue = $this->randomString();
    $edit = [
      'field_' . $this->flagFieldId . '[0][value]' => $this->flagFieldValue,
    ];
    $this->drupalPostForm(NULL, $edit, t('Update Flagging'));

    // Check that the node is flagged.
    $this->assertLink(t('Unflag this item'));
  }

  /**
   * Edit the field value of the existing flagging.
   */
  public function doEditFlagField() {
    // Get the details form.
    $this->drupalGet('flag/details/edit/' . $this->id . '/' . $this->nodeId);

    // See if the details message is displayed.
    $this->assertText($this->flagDetailsMessage);

    // See if the field value was preserved.
    $this->assertFieldByName('field_' . $this->flagFieldId . '[0][value]', $this->flagFieldValue);

    // Update the field value.
    $this->flagFieldValue = $this->randomString();
    $edit = [
      'field_' . $this->flagFieldId . '[0][value]' => $this->flagFieldValue,
    ];
    $this->drupalPostForm(NULL, $edit, t('Update Flagging'));

    // Get the details form.
    $this->drupalGet('flag/details/edit/' . $this->id . '/' . $this->nodeId);

    // See if the field value was preserved.
    $this->assertFieldByName('field_' . $this->flagFieldId . '[0][value]', $this->flagFieldValue);
  }

}
