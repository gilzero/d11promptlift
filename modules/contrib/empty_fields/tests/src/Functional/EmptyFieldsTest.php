<?php

namespace Drupal\Tests\empty_fields\Functional;

use Drupal\Tests\BrowserTestBase;
use Drupal\Tests\field_ui\Traits\FieldUiTestTrait;
use Drupal\user\Entity\User;

/**
 * Tests the empty fields functionality.
 *
 * @group empty_fields
 */
class EmptyFieldsTest extends BrowserTestBase {
  use FieldUiTestTrait;

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['block', 'empty_fields', 'field_ui', 'user'];

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * The user to use in tests.
   *
   * @var \Drupal\user\UserInterface
   */
  protected $webUser;

  /**
   * {@inheritdoc}
   */
  protected function setUp():void {
    parent::setUp();
    $this->drupalPlaceBlock('system_breadcrumb_block');
    $this->webUser = $this->createUser([
      'administer user fields',
      'administer user display',
    ]);
    $this->drupalLogin($this->webUser);
  }

  /**
   * Tests that the module actually works.
   */
  public function testEmptyFieldsOutput() {
    $url = $this->webUser->toUrl('canonical');
    // Create a field.
    $field_name = mb_strtolower($this->randomMachineName());
    $field_label = $this->randomMachineName() . '_label';
    $this->fieldUIAddNewField('admin/config/people/accounts', $field_name, $field_label, 'string');
    $field_name = 'field_' . $field_name;
    \Drupal::service('entity_field.manager')->clearCachedFieldDefinitions();

    $field_value = $this->randomMachineName();
    $edit = [$field_name . '[0][value]' => $field_value];
    $this->drupalGet($this->webUser->toUrl('edit-form'));
    $this->submitForm($edit, 'Save');
    // Verify that field is displayed when has value.
    $this->drupalGet($url);
    $this->assertSession()->responseContains($field_label);
    $this->assertSession()->responseContains($field_value);

    $edit = [$field_name . '[0][value]' => ''];
    $this->drupalGet($this->webUser->toUrl('edit-form'));
    $this->submitForm($edit, 'Save');
    // Make sure empty field is hidden.
    $this->drupalGet($url);
    $this->assertSession()->responseNotContains($field_label);
    $this->assertSession()->responseNotContains($field_value);

    /** @var \Drupal\Core\Entity\EntityDisplayRepositoryInterface $repository */
    $repository = \Drupal::service('entity_display.repository');
    $display = $repository->getViewDisplay('user', 'user', 'default');
    $component = $display->getComponent($field_name);

    // Tests 'nbsp' plugin.
    $component['third_party_settings']['empty_fields'] = ['handler' => 'nbsp'];
    $display->setComponent($field_name, $component)->save();
    $this->drupalGet($url);
    $this->assertSession()->responseContains($field_label);
    $elements = $this->cssSelect('.empty-fields__nbsp div');
    $this->assertCount(2, $elements);
    $this->assertSame(' ', $elements[1]->getHtml());

    // Tests 'text' plugin.
    $text = 'This field is empty';
    $component['third_party_settings']['empty_fields'] = [
      'handler' => 'text',
      'settings' => ['empty_text' => $text],
    ];
    $display->setComponent($field_name, $component)->save();
    $this->drupalGet($url);
    $this->assertSession()->responseContains($field_label);
    $elements = $this->cssSelect('.empty-fields__text div');
    $this->assertCount(2, $elements);
    $this->assertSame($text, $elements[1]->getHtml());

    // Tests 'broken' plugin as fallback for unknown.
    $text = 'This plugin broken or missing.';
    $component['third_party_settings']['empty_fields'] = ['handler' => 'unknown'];
    $display->setComponent($field_name, $component)->save();
    $this->drupalGet($url);
    $this->assertSession()->responseContains($field_label);
    $elements = $this->cssSelect('.empty-fields__unknown div');
    $this->assertCount(2, $elements);
    $this->assertSame($text, $elements[1]->getHtml());
    $this->drupalGet('admin/config/people/accounts/display');
    $this->assertSession()->responseContains($text);

    // Tests field access using core test module.
    \Drupal::service('module_installer')->install(['field_test_boolean_access_denied']);
    $user = User::load($this->webUser->id());
    $this->assertTrue($user->{$field_name}->access('view'));
    \Drupal::state()->set('field.test_boolean_field_access_field', $field_name);
    $this->assertFalse($user->{$field_name}->access('view'));
    // Update setting to invalidate cache.
    $component['third_party_settings']['empty_fields'] = ['handler' => 'nbsp'];
    $display->setComponent($field_name, $component)->save();
    $this->drupalGet($url);
    $this->assertSession()->responseNotContains($field_label);
  }

}
