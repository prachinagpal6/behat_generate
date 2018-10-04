<?php

namespace Drupal\behat_generate\Generators;

use Drupal\user\Entity\Role;
use DrupalCodeGenerator\Command\BaseGenerator;
use DrupalCodeGenerator\Utils;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class BehatGeneratePermissionTest extends BaseGenerator {

  protected $name = 'behat-generator-permission-test';

  protected $description = 'Generates Permission Test Tests.';

  protected $alias = 'behat-perm';

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
    $vars = [];

    $roles = Role::loadMultiple();

    foreach ($roles as $key => $role) {
      $vars['key'] = $key;
      $vars['testcontent'] = $test = '';
      if ($this->configFactory->get('installation_path')) {
        $this->setDirectory($this->configFactory->get('installation_path'));
      }
      else {
        $this->setDirectory('../tests/behat/features/');
      }

      $userPerm = $role->getPermissions();

      if (!empty($userPerm)) {
        $vars['testcontent'] = 'Then the "' . $key . '" role has exactly the following permissions:' . PHP_EOL . str_repeat(' ', 6);;
      }

      foreach ($userPerm as $perm) {
        $test .= '| ' . $perm . ' |' . PHP_EOL . str_repeat(' ', 6);
      }

      $vars['testcontent'] .= $test;

      $this->addFile()
        ->vars($vars)
        ->path($key . '_RolePermissions.feature')
        ->template('permission-test-generator.twig')
        ->action('replace');
    }
  }
}