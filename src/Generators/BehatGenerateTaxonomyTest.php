<?php

namespace Drupal\behat_generate\Generators;

use Drupal\node\Entity\NodeType;
use Drupal\taxonomy\Entity\Vocabulary;
use DrupalCodeGenerator\Command\BaseGenerator;
use DrupalCodeGenerator\Utils;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class BehatGenerateTaxonomyTest extends BaseGenerator {

  protected $name = 'behat-generator-taxonomy-test';

  protected $description = 'Generates Taxonomy Tests.';

  protected $alias = 'behat-tax';

  protected $templatePath = __DIR__;

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


    $vocabularies = Vocabulary::loadMultiple();
    foreach ($vocabularies as $vocabulary_name => $vocabulary) {
      $this->setDirectory('../tests/behat/features/');

      $vars = $this->buildVars($vocabulary_name, $vocabulary);
      //print_r($vocabulary); exit();

      $this->addFile()
        ->vars($vars)
        ->path($vocabulary_name . '_Terms.feature')
        ->template('taxonomy-test-generator.twig')
        ->action('replace');

    }

  }

  private function buildVars($vocabulary_name, $vocabulary) {
    $vars['vocab'] = $vocabulary->label();
    $vars['taxonomy'] = $vocabulary_name;
    $vars['test'] = '';
    $terms = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadTree($vocabulary_name);
    foreach($terms as $term) {
      $vars['test'] .= '| ' . $term->name . ' |' . PHP_EOL;
    }

    return $vars;
  }
}