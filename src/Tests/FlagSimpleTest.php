<?php

/**
 * @file
 * Contains \Drupal\flag\FlagSimpleTest.
 */

namespace Drupal\flag\Tests;

use Drupal\simpletest\WebTestBase;


/**
 * Tests the Flag forms (add/edit/delete).
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
   * @var string
   */
  protected $flaggableTypes = 'article';

  /**
   * @var string
   */
  protected $flag_confirmation = 'Are you sure you want to flag this content?';

  /**
   * @var string
   */
  protected $unflag_confirmation = 'Are you sure you want to unflag this content?';

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = array('flag', 'node');

  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return array(
      'name' => 'Flag form/s',
      'description' => 'Creates a flag, adds flag to node.',
      'group' => 'Flag',
    );
  }

  /**
   * Configures test base and executes test cases.
   */
  public function testFlagForm() {
    // Create and log in our user.
    $admin_user = $this->drupalCreateUser(array(
      'administer flags',
    ));
    $this->drupalLogin($admin_user);
    $this->drupalCreateContentType(array('type' => 'article'));
    $this->doTestFlagAdd();
  }

  /**
   * Flag creation.
   */
  public function doTestFlagAdd() {
    // First, test with minimal value requirement.
    $edit = array(
      'label' => $this->label,
      'id' => $this->id,
    );
    $this->drupalPostForm('/admin/structure/flags/add', $edit, t('Continue'));
    // Check for fieldset titles.
    $this->assertText(t('Messages'));
    $this->assertText(t('Flag access'));
    $this->assertText(t('Display options'));

    $edit = array(
      'types[' . $this->flaggableTypes . ']' => $this->flaggableTypes,
    );
    $this->drupalPostForm(NULL, $edit, t('Create Flag'));

    $this->assertText(t('Flag @this_label has been added.', array('@this_label' => $this->label)));

  }

  /**
   * Node creation and flagging.
   */

}
