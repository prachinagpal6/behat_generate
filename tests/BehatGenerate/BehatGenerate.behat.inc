<?php

namespace BehatGenerate;

use Drupal\DrupalExtension\Context\RawDrupalContext;
use Behat\Behat\Context\SnippetAcceptingContext;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;
use Behat\Behat\Tester\Exception\PendingException;
use Behat\Behat\Context\BehatContext;
use Behat\Mink;
use Behat\MinkExtension\Context;
use Behat\MinkExtension\Context\MinkContext;
use Behat\Mink\Element\Element;
use Behat\Mink\WebAssert;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Behat\Mink\Exception\ElementNotFoundException;
use Behat\Mink\Exception\ExpectationException;
use Behat\Mink\Exception\UnsupportedDriverActionException;
use Behat\Behat\Hook\Scope\AfterScenarioScope;
use Behat\Mink\Driver\Selenium2Driver;
use Behat\Behat\Hook\Scope\AfterStepScope;
use Behat\Mink\Driver\GoutteDriver;
use Behat\Testwork\Hook\Scope\BeforeSuiteScope;
use Drupal\DrupalExtension\Context\MinkExtension;
use Drupal\DrupalExtension\Context\DrupalContext;
use Behat\Mink\Element\DocumentElement;
use Drupal\DrupalExtension\Context\DrupalSubContextBase;
use Drupal\DrupalExtension\Context\DrupalSubContextInterface;
use Drupal\user\Entity\Role;

/**
 * FeatureContext class defines custom step definitions for Behat.
 */
class BehatGenerateSubContext extends DrupalSubContextBase implements DrupalSubContextInterface {

  /**
   * Test that a role exists.
   *
   * @Given the :role role exists
   */
  public function theRoleExists($role) {

    $loaded_role_entity = Role::load($role);

    // Verify that user role exists.
    if (!$loaded_role_entity) {
      throw new \Exception(sprintf("The %s role does not exist.", $role));
    }
  }

  /**
   * Test that a role doesn't have a given permission.
   *
   * @Then the :role role does not have the :permission permission
   */
  public function theRoleDoesNotHaveThePermission($role, $permission) {

    $loaded_role_entity = Role::load($role);

    // Verify that loaded user role does not have the permission provided.
    if ($loaded_role_entity->hasPermission($permission)) {
      throw new \Exception(sprintf("The %s role should not have the '%s' permission.", $role, $permission));
    }
  }

  /**
   * Test that a role does have a given permission.
   *
   * @Then the :role role has the :permission permission
   */
  public function theRoleHasThePermission($role, $permission) {

    $loaded_role_entity = Role::load($role);

    // Verify that loaded user role has the permission provided.
    if (!$loaded_role_entity->hasPermission($permission)) {
      throw new \Exception(sprintf("The %s role does not have the '%s' permission.", $role, $permission));
    }
  }

  /**
   * Test that a role has exactly the permissions.
   *
   * @Then the :role role has exactly the following permissions:
   */
  public function theRoleHasExactlyTheFollowingPermissions($role, TableNode $table) {
    $expected_permissions = $table->getColumn(0);
    asort($expected_permissions);
    $actual_permissions = Role::load($role)->getPermissions();
    asort($actual_permissions);

    $errors = [];
    $unexpected_permissions = array_diff($actual_permissions, $expected_permissions);
    if ($unexpected_permissions) {
      $errors[] = sprintf("The %s role contains the following unexpected permissions:\n- %s", $role, implode("\n- ", $unexpected_permissions));
    }
    $missing_permissions = array_diff($expected_permissions, $actual_permissions);
    if ($missing_permissions) {
      $errors[] = sprintf("The %s role is missing the following expected permissions:\n- %s", $role, implode("\n- ", $missing_permissions));
    }
    if ($errors) {
      $message = implode("\n", $errors);
      throw new \Exception($message);
    }
  }

  /**
   * Returns fixed step argument (with \\" replaced back to ")
   */
  public function fixStepsArguments($argument) {
    return str_replace('\\"', '"', $argument);
  }

  /**
   * @When I select the first autocomplete option for :prefix on the :field field
   */
  public function iSelectFirstAutocomplete($prefix, $field) {
    $field = $this->fixStepsArguments($field);
    $session = $this->getSession();
    $page = $session->getPage();
    $element = $page->findField($field);
    if (!$element) {
      throw new ElementNotFoundException($session, NULL, 'named', $field);
    }

    $page->fillField($field, $prefix);

    $xpath = $element->getXpath();
    $driver = $session->getDriver();
    $prefix = $this->fixStepsArguments($prefix);

    $chars = str_split($prefix);

    $last_char = array_pop($chars);

    // autocomplete.js uses key down/up events directly.
    $driver->keyDown($xpath, 8);
    $driver->keyUp($xpath, 8);

    // Key down/up events for last character.
    $driver->keyDown($xpath, $last_char);
    $driver->keyUp($xpath, $last_char);

    // Wait for AJAX to finish.
    $this->getSession()
      ->wait(100, '(typeof(jQuery)=="undefined" || (0 === jQuery.active && 0 === jQuery(\':animated\').length))');

    // And make sure the autocomplete is showing.
    $this->getSession()
      ->wait(1000, 'jQuery("#autocomplete").show().length > 0');

    // And wait for 1 second just to be sure.
    sleep(1);

    // Press the down arrow to select the first option.
    $driver->keyDown($xpath, 40);
    $driver->keyUp($xpath, 40);

    // Press the Enter key to confirm selection, copying the value into the field.
    $driver->keyDown($xpath, 13);
    $driver->keyUp($xpath, 13);

    // Wait for AJAX to finish.
    $this->getSession()
      ->wait(100, '(typeof(jQuery)=="undefined" || (0 === jQuery.active && 0 === jQuery(\':animated\').length))');
  }

  /**
   * Rebuild Cache.
   *
   * @Then I rebuild cache
   */
  public function iRebuildCache() {
    drupal_flush_all_caches();
  }

  /**
   * Validate the content type exists.
   *
   * @Given the Content Type :content_type exists
   */
  public function theContentTypeExists($content_type) {
    $node_types = NodeType::loadMultiple();
    if (!array_key_exists($content_type, $node_types)) {
      throw new \Exception(sprintf("The content type %s does not exist.", $content_type));
    }
  }

  /**
   * Validate the content type has field.
   *
   * @Given the Content Type :content_type has :field field
   */
  public function theContentTypeHasField($content_type, $field) {
    $node_types = \Drupal::entityManager()
      ->getFieldDefinitions('node', $content_type);
    if (!array_key_exists($field, $node_types)) {
      throw new \Exception(sprintf("The field %s does not exist in content type %s.", $field, $content_type));
    }
  }

  /**
   * Validate the field type.
   *
   * @Then the field :field has type :type
   */
  public function theFieldHasType($field_name, $field_type) {
    $field = \Drupal::entityManager()
      ->getStorage('field_storage_config')
      ->load('node.' . $field_name);
    if ($field->getType() != $field_type) {
      throw new \Exception(sprintf("The field %s is not of type %s.", $field_name, $field_type));
    }
  }

  /**
   * @Given /^I check the "([^"]*)" radio button with "([^"]*)" value$/
   */
  public function iCheckTheRadioButtonWithValue($element, $value)
  {
    foreach ($this->getMainContext()->getSession()->getPage()->findAll('css', 'input[type="radio"][name="'. $element .'"]') as $radio) {
      if ($radio->getAttribute('value') == $value) {
        $radio->check();
        return true;
      }
    }
    return false;
  }
}