<?php

use Robo\Tasks;

class RoboFile extends Tasks {

  public function phpcsdrupal() {
    $extensions = 'php,module,inc,install,test,profile,theme,css,info,txt,md';
    $standard = 'Drupal';
    $phpcs_path = '../../vendor/bin/phpcs';

    // Get an array of files.
    exec('git diff --name-only --cached --diff-filter=ACM', $files);

    // Step over files.
    foreach ($files as $file) {
      // Check for Drupal standards.
      $result = $this->taskExec("{$phpcs_path} --standard={$standard} --extensions={$extensions} {$file}")->run();

      // If Drupal standards passed,
      // Check for DrupalPractice.
      if ($result->wasSuccessful()) {
        $standard = 'DrupalPractice';
        $result = $this->taskExec("{$phpcs_path} --standard={$standard} --extensions={$extensions} {$file}")->run();
      }

      return $result;
    }

  }
}
