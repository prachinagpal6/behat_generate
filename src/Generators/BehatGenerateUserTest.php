<?php

namespace Drupal\behat_generate\Generators;

use Drupal\field\Entity\FieldConfig;
use Drupal\user\Entity\Role;
use DrupalCodeGenerator\Command\BaseGenerator;
use DrupalCodeGenerator\Utils;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class BehatGenerateUserTest extends BaseGenerator {

  protected $name = 'behat-generator-user-test';

  protected $description = 'Generates User Tests.';

  protected $alias = 'behat-usr ';

  protected $templatePath = __DIR__;

  // We don't actually use this service. This illustrates how to inject a dependency into a Generator.
  protected $configFactory;

  protected $entityManager;

  public function __construct($configFactory = NULL, $entityManager, $name = NULL) {
    parent::__construct($name);
    $this->configFactory = $configFactory;
    $this->entityManager = $entityManager;
  }

  /**
   * {@inheritdoc}
   */
  protected function interact(InputInterface $input, OutputInterface $output) {
    $questions = Utils::defaultQuestions();

    $role_types = Role::loadMultiple();
    foreach ($role_types as $role_type => $role) {
      $this->setDirectory('../tests/behat/features/');

      $vars = $this->buildVars($role_type, $role);

      $this->addFile()
        ->vars($vars)
        ->path($role_type . '_AddUser.feature')
        ->template('user-test-generator.twig')
        ->action('replace');

    }
  }

  private function buildVars($role_type, $role) {
    $vars = [];

    $vars['label'] = $role->label();

    if ($role_type !== 'anonymous') {


      $display = \Drupal::entityTypeManager()
        ->getStorage('entity_form_display')
        ->load('user' . '.' . $role_type . '.' . 'default');

      $components = $display->getComponents();
      foreach ($components as $field_name => $settings) {

        if (strpos($field_name, 'field_') !== FALSE || in_array($field_name, [
            'user_picture',
            'name',
            'mail',
            'edit-pass-pass2',
            'edit-pass-pass1',
            'edit-roles',
            'title'
          ])) {
          $field_config = FieldConfig::loadByName('user', $role_type, $field_name);
          // $file_storage_config = FieldStorageConfig::loadByName('node',$field_name);
          // $field_type = $field_config->getType();
          if (gettype($field_config) == 'object') {
            $field_type = $field_config->getType();
            $field_label = $field_config->getLabel();
            $field_settings = $field_config->getSettings();
          }
          $widget_type = $settings['type'];

          print "\n Field Name: " . $field_name;
          print "\n Field Label: " . $field_label;
          print "\n Field Type: " . $field_type;
          print "\n Widget Type: " . $widget_type;

          if (!empty($field_type) || !empty($field_label) || !empty($widget_type)) {
            if ($field_type == "boolean" && $widget_type == "options_buttons") {
              $vars['test'] .= str_repeat(' ', 4) . 'And I check the box "0"' .
                PHP_EOL;
            }
            else {
              if ($field_type == "boolean" && $widget_type == "boolean_checkbox") {
                $vars['test'] .= str_repeat(' ', 4) . 'And I check the box "' . $field_name . '[value]"' .
                  PHP_EOL;
              }
              else {
                if ($field_type == "image" && $widget_type == "image_image") {
                  $vars['test'] .= str_repeat(' ', 4) . 'And I attach the file "default.png" to "' . $field_label . '"' .
                    PHP_EOL;
                }
                else {
                  if ($field_type == "email" && $widget_type == "email_default") {
                    $vars['test'] .= str_repeat(' ', 4) . 'And I fill in "' . $field_label . '" with "' . $role_type . '@' .
                      $role_type . '.com"' . PHP_EOL;
                  }
                  else {
                    if ($field_type == "float" && $widget_type == "number") {
                      $vars['test'] .= str_repeat(' ', 4) . 'And I fill in "' . $field_label . '" with "-6"' .
                        PHP_EOL;
                    }
                    else {
                      if ($field_type == "string" && $widget_type == "string_textfield") {
                        $vars['test'] .= str_repeat(' ', 4) . 'And I fill in "' . $field_label . '" with "' . $role_type . ' ' .
                          $field_label . '"' . PHP_EOL;
                      }
                      else {
                        if ($field_type == "string_long" && $widget_type == "string_textarea") {

                        }
                        else {
                          if ($field_type == "text_with_summary" && $widget_type == "text_textarea_with_summary") {

                            $vars['test'] .= str_repeat(' ', 4) . 'And I prepare "' . $field_name . '" [0][format] fields with "plain_text"' . PHP_EOL;
                            $vars['test'] .= str_repeat(' ', 4) . 'And I fill in "' .
                              $field_name . '" [0][value] with "' . $role_type . $field_label . '"' . PHP_EOL;
                          }
                          else {
                            if ($field_type == "text_with_summary" && $widget_type == "string_textfield") {

                            }
                            else {
                              if ($field_type == "list_string" && $widget_type == "options_select") {
                                $allowed_values = $field_settings['allowed_values'];
                                $option = reset($allowed_values);
                                $vars['test'] .= str_repeat(' ', 4) . 'And I select "' . $option . '" from "' . $field_name .
                                  '"' . PHP_EOL;
                              }
                              else {
                                if ($field_type == "list_string" && $widget_type == "options_buttons") {
                                  $allowed_values = $field_config->getSetting('allowed_values');
                                  $option = reset($allowed_values);
                                  $vars['test'] .= str_repeat(' ', 4) . 'I check the "' . $field_name . '" radio button with "' . $option .
                                    '" value' . PHP_EOL;
                                }
                                else {
                                  if ($field_type == "datetime" && $widget_type == "datetime_default") {
                                    $vars['test'] .= str_repeat(' ', 4) . 'And I fill in "' . $field_name . '[0][value][date]"' .
                                      'with "' . date('m/d/Y') . '"' . PHP_EOL;
                                    $vars['test'] .= str_repeat(' ', 4) . 'And I fill in "' . $field_name . '[0][value][time]"' .
                                      'with "' . date('h:i:s A') . '"' . PHP_EOL;
                                  }
                                  else {
                                    if ($field_type == "datetime" && $widget_type == "datetime_datelist") {
                                      $vars['test'] .= str_repeat(' ', 4) . 'And I select "' . date('Y') . '" from "' . $field_name .
                                        '[0][value][year]"' . PHP_EOL;
                                      $vars['test'] .= str_repeat(' ', 4) . 'And I select "' . date('M') . '" from "' . $field_name .
                                        '[0][value][month]"' . PHP_EOL;
                                      $vars['test'] .= str_repeat(' ', 4) . 'And I select "' . date('j') . '" from "' . $field_name .
                                        '[0][value][day]"' . PHP_EOL;
                                    }
                                    else {
                                      if ($field_type == "decimal" && $widget_type == "number") {
                                        $vars['test'] .= str_repeat(' ', 4) . 'And I fill in "' . $field_label . '" with "00.80"' .
                                          PHP_EOL;
                                      }
                                      else {
                                        if ($field_type == "list_float" && $widget_type == "options_select") {
                                          $allowed_values = $field_settings['allowed_values'];
                                          $option = reset($allowed_values);
                                          $vars['test'] .= str_repeat(' ', 4) . 'And I select "' . $option . '" from "' . $field_name .
                                            '"' . PHP_EOL;
                                        }
                                        else {
                                          if ($field_type == "list_float" && $widget_type == "options_buttons") {
                                            $allowed_values = $field_config->getSetting('allowed_values');
                                            $option = reset($allowed_values);
                                            $vars['test'] .= str_repeat(' ', 4) . 'I check the "' . $field_name . '" radio button with "' . $option .
                                              '" value' . PHP_EOL;
                                          }
                                          else {
                                            if ($field_type == "entity_reference" && $widget_type == "entity_reference_autocomplete_tags") {

                                            }
                                            else {
                                              if ($field_type == "entity_reference" && $widget_type == "entity_reference_autocomplete") {
                                                $target_type = $field_settings['target_type'];
                                                $target_bundles = $field_settings['handler_settings']['handler_settings'];

                                                $query = db_select('node_field_data', 'n')
                                                  ->fields('n', ['nid', 'title'])
                                                  ->condition('n.status', 1)
                                                  ->range(0, 1)
                                                  ->orderRandom();

                                                $target_node = $query->execute()
                                                  ->fetchAssoc();
                                                $val = $target_node['title'] . ' (' . $target_node['nid'] . ')';

                                                $vars['test'] .= str_repeat(' ', 4) . 'And I select the first autocomplete option for ' . $val . ' on the ' . $field_label . '[0][target_id] field';
                                                PHP_EOL;
                                              }
                                              else {
                                                if ($field_type == "entity_reference" && $widget_type == "options_select") {

                                                }
                                                else {
                                                  if ($field_type == "entity_reference" && $widget_type == "options_buttons") {

                                                  }
                                                  else {

                                                  }
                                                }
                                              }
                                            }
                                          }
                                        }
                                      }
                                    }
                                  }
                                }
                              }
                            }
                          }
                        }
                      }
                    }
                  }
                }
              }
            }
          }
        }
      }
    }
    return $vars;
  }
}