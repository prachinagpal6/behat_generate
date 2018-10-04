<?php

namespace Drupal\behat_generate\Generators;

use Drupal\field\Entity\FieldConfig;
use Drupal\node\Entity\NodeType;
use DrupalCodeGenerator\Command\BaseGenerator;
use DrupalCodeGenerator\Utils;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class BehatGenerateContentTest extends BaseGenerator {

  protected $name = 'behat-generator-content-test';

  protected $description = 'Generates Content Tests.';

  protected $alias = 'behat-ct';

  protected $templatePath = __DIR__;

  // We don't actually use this service. This illustrates how to inject a dependency into a Generator.
  protected $configFactory;

  protected $entityManager;

  public function __construct($configFactory = NULL, $entityManager, $name = NULL) {
    parent::__construct($name);
    $this->configFactory = $configFactory->get('behat_generate.settings');
    $this->entityManager = $entityManager;
  }

  /**
   * {@inheritdoc}
   */
  protected function interact(InputInterface $input, OutputInterface $output) {
    $questions = Utils::defaultQuestions();

    $node_types = NodeType::loadMultiple();
    foreach ($node_types as $node_type => $node) {
      if ($this->configFactory->get('installation_path')) {
        $this->setDirectory($this->configFactory->get('installation_path'));
      }
      else {
        $this->setDirectory('../tests/behat/features/');
      }

      $vars = $this->buildVars($node_type, $node);

      $this->addFile()
        ->vars($vars)
        ->path($node_type . '_AddContent.feature')
        ->template('content-test-generator.twig')
        ->action('replace');

    }
  }

  private function buildVars($node_type, $node) {

    $vars['label'] = $node->label();
    $vars['node_type'] = $node_type;


    $display = \Drupal::entityTypeManager()
      ->getStorage('entity_form_display')
      ->load('node' . '.' . $node_type . '.' . 'default');
    $components = $display->getComponents();
    foreach ($components as $field_name => $settings) {

      if (strpos($field_name, 'field_') !== FALSE || in_array($field_name, [
          'body',
          'title',
        ])) {
        $field_config = FieldConfig::loadByName('node', $node_type, $field_name);
        if (gettype($field_config) == 'object') {
          $field_type = $field_config->getType();
          $field_label = $field_config->getLabel();
          $field_settings = $field_config->getSettings();
        }
        $widget_type = $settings['type'];

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
                  $vars['test'] .= str_repeat(' ', 4) . 'And I fill in "' . $field_label . '" with "' . $node_type . '@' .
                    $node_type . '.com"' . PHP_EOL;
                }
                else {
                  if ($field_type == "float" && $widget_type == "number") {
                    $vars['test'] .= str_repeat(' ', 4) . 'And I fill in "' . $field_label . '" with "-6"' .
                      PHP_EOL;
                  }
                  else {
                    if ($field_type == "string" && $widget_type == "string_textfield") {
                      $vars['test'] .= str_repeat(' ', 4) . 'And I fill in "' . $field_label . '" with "' . $node_type . ' ' .
                        $field_label . '"' . PHP_EOL;
                    }
                    else {
                      if ($field_type == "string_long" && $widget_type == "string_textarea") {

                      }
                      else {
                        if ($field_type == "text_with_summary" && $widget_type == "text_textarea_with_summary") {

                          $vars['test'] .= str_repeat(' ', 4) . 'And I prepare "' . $field_name . '[0][format]" fields with "plain_text"' . PHP_EOL;
                          $vars['test'] .= str_repeat(' ', 4) . 'And I fill in "' .
                            $field_name . '[0][value]" with "' . $node_type . $field_label . '"' . PHP_EOL;
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

                                              $vars['test'] .= str_repeat(' ', 4) . 'And I select the first autocomplete option for "' . $val . '" on the "' . $field_name . '[0][target_id]" field';
                                              PHP_EOL;
                                            }
                                            else {
                                              if ($field_type == "entity_reference" && $widget_type == "options_select") {

                                              }
                                              else {
                                                if ($field_type == "entity_reference" && $widget_type == "options_buttons") {

                                                }
                                                else {
                                                  // @TODO: Handle more cases.

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