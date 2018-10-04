<?php

namespace Drupal\behat_generate\Generators;

use Drupal\node\Entity\NodeType;
use DrupalCodeGenerator\Command\BaseGenerator;
use DrupalCodeGenerator\Utils;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class BehatGenerateContentType extends BaseGenerator {

  protected $name = 'behat-generator-content-type-test';

  protected $description = 'Generates Content Type Tests.';

  protected $alias = 'behat-content';

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
    $vars = [];


    $node_types = NodeType::loadMultiple();
    foreach ($node_types as $key => $node) {
      $this->setDirectory('../tests/behat/features/');

      $vars['key'] = $key;
      $vars['test'] = '';
      $node_fields = \Drupal::entityManager()
        ->getFieldDefinitions('node', $key);

      foreach ($node_fields as $field => $value) {
        if (strpos($field, 'field_') !== FALSE) {
          $vars['test'] .= '| ' . $field . ' | ' . $value->getType() . ' |' . PHP_EOL . '    ';
        }
      }

      if (empty( $vars['test'])) {
        $this->addFile()
          ->vars($vars)
          ->path($key . '_contentType.feature')
          ->template('content-type-generator-empty.twig')
          ->action('replace');

      }
      else {
        $this->addFile()
          ->vars($vars)
          ->path($key . '_contentType.feature')
          ->template('content-type-generator.twig')
          ->action('replace');
      }

    }

  }
}