<?php

/**
 * @file
 * Contains \Drupal\flag\FlagSimpleTest.
 */

namespace Drupal\flag\Tests;

use Drupal\simpletest\WebTestBase;
use Drupal\user\Entity\Role;


/**
 * Tests the Flag form actions (add/edit/delete).
 *
 * @group flag
 */
class FlagSimpleTest extends WebTestBase {

  /**
   * @var string
   */
  protected $label = 'Test label 123';

  /**
   * @var string
   */
  protected $id = 'test_label_123';

  /**
   * @var string
   */
  protected $flagLinkType;

  /**
   *
   *
   * @var string
   */
  protected $nodeType = 'article';

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
   * Configures test base and executes test cases.
   */
  public function testFlagForm() {
    // Create and log in our user.
    $this->adminUser = $this->drupalCreateUser(array(
      'administer flags',
      'administer node display',
    ));

    $this->drupalLogin($this->adminUser);

    $this->doTestFlagAdd();
    $this->doTestHideFlagLinkFromTeaser();
  }

  /**
   * Flag creation.
   */
  public function doTestFlagAdd() {
    // Create content type.
    $this->drupalCreateContentType(array('type' => $this->nodeType));

    // Test with minimal value requirement.
    $edit = array(
      'label' => $this->label,
      'id' => $this->id,
    );
    $this->drupalPostForm('admin/structure/flags/add', $edit, t('Continue'));
    // Check for fieldset titles.
    $this->assertText(t('Messages'));
    $this->assertText(t('Flag access'));
    $this->assertText(t('Display options'));

    $edit = array(
      'types[' . $this->nodeType . ']' => $this->nodeType,
    );
    $this->drupalPostForm(NULL, $edit, t('Create Flag'));

    $this->assertText(t('Flag @this_label has been added.', array('@this_label' => $this->label)));

    // Continue test process.
    $this->doTestCreateNodeAndFlagIt();
  }

  /**
   * Node creation and flagging.
   */
  public function doTestCreateNodeAndFlagIt() {
    $node = $this->drupalCreateNode(array('type' => $this->nodeType));
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

    $this->drupalGet('node/' . $node_id);
    $this->clickLink('Flag this item');
    $this->assertResponse(200);
    $this->assertLink('Unflag this item');

    // Switch user to check flagging link.
    $user_2 = $this->drupalCreateUser();
    $this->drupalLogin($user_2);
    $this->drupalGet('node/' . $node_id);
    $this->assertResponse(200);
    $this->assertLink('Flag this item');

    // Switch back to first user and unflag.
    $this->drupalLogin($user_1);
    $this->drupalGet('node/' . $node_id);

    $this->clickLink('Unflag this item');
    $this->assertResponse(200);
    $this->assertLink('Flag this item');
  }

  /**
   * Node creation and flag link.
   */
  public function doTestHideFlagLinkFromTeaser() {
    $this->drupalLogin($this->adminUser);

    $node = $this->drupalCreateNode(array(
      'type' => $this->nodeType,
      'promote' => TRUE,
    ));
    $node_id = $node->id();
    $node_title = $node->getTitle();

    $this->drupalGet('node');
    $this->assertText($node_title);
    $this->assertLink('Flag this item');

    // Set flag format to hidden for teaser display and post form.
    $edit = array(
      'fields[flag_' . $this->id . '][type]' => 'hidden',
    );

    $this->drupalPostForm('admin/structure/types/manage/' . $this->nodeType . '/display/teaser', $edit, t('Save'));

    // Check if form is saved successfully.
    $this->assertText('Your settings have been saved.');

    $this->drupalGet('node');
    $this->assertText($node_title);
    $this->assertNoLink('Flag this item');

    $this->drupalGet('node/' . $node_id);
  }

  /**
   * Flags a node using different user accounts and checks flag counts.
   */
  public function doTestFlagCounts() {
    $node = $this->drupalCreateNode(array('type' => $this->nodeType));
    $node_id = $node->id();

    // Create and login user 1.
    $user_1 = $this->drupalCreateUser();
    $this->drupalLogin($user_1);

    // Flag node (first count).
    $this->drupalGet('node/' . $node_id);
    $this->clickLink('Flag this item');
    $this->assertResponse(200);
    $this->assertLink('Unflag this item');

    // Check for 1 flag count.
    $count_flags_before = \Drupal::entityQuery('flag_counts')
      ->condition('fid', $this->id)
      ->condition('entity_type', $node->getEntityTypeId())
      ->condition('entity_id', $node_id)
      ->count()
      ->execute();
    $this->assertTrue(1, $count_flags_before);

    // Logout user 1, create and login user 2.
    $user_2 = $this->drupalCreateUser();
    $this->drupalLogin($user_2);

    // Flag node (second count).
    $this->drupalGet('node/' . $node_id);
    $this->clickLink('Flag this item');
    $this->assertResponse(200);
    $this->assertLink('Unflag this item');

    // Check for 2 flag counts.
    $count_flags_after = \Drupal::entityQuery('flag_counts')
      ->condition('fid', $this->id)
      ->condition('entity_type', $node->getEntityTypeId())
      ->condition('entity_id', $node_id)
      ->count()
      ->execute();
    $this->assertTrue(2, $count_flags_after);

    // Unflag the node again.
    $this->drupalGet('node/' . $node_id);
    $this->clickLink('Unflag this item');
    $this->assertResponse(200);
    $this->assertLink('Flag this item');

    // Check for 1 flag count.
    $count_flags_before = \Drupal::entityQuery('flag_counts')
      ->condition('fid', $this->id)
      ->condition('entity_type', $node->getEntityTypeId())
      ->condition('entity_id', $node_id)
      ->count()
      ->execute();
    $this->assertTrue(1, $count_flags_before);

    // Delete  user 1.
    $user_1->delete();

    // Check for 0 flag counts, user deletion should lead to count decrement
    // or row deletion.
    $count_flags_before = \Drupal::entityQuery('flag_counts')
      ->condition('fid', $this->id)
      ->condition('entity_type', $node->getEntityTypeId())
      ->condition('entity_id', $node_id)
      ->count()
      ->execute();
    $this->assertTrue(0, $count_flags_before);
  }
}
