<?php

use Robo\Tasks;

class RoboFile extends Tasks {

  /**
   * Run PHPCS.
   *
   * @return \Robo\Result
   */
  public function phpcsdrupal() {
    $extensions = 'php,module,inc,install,test,profile,theme,css,info,txt,md';
    $standard = 'Drupal';
    $phpcs_path = '../../vendor/bin/phpcs';

    // Get an array of files.
    exec('git diff --name-only --cached --diff-filter=ACM', $files);

    // Step over files.
    foreach ($files as $file) {
      // Check for Drupal standards.
      $result = $this->taskExec("{$phpcs_path} --standard={$standard} --extensions={$extensions} {$file}")->run()->stopOnFail();

      // If Drupal standards passed,
      // Check for DrupalPractice.
      if ($result->wasSuccessful()) {
        $standard = 'DrupalPractice';
        $result = $this->taskExec("{$phpcs_path} --standard={$standard} --extensions={$extensions} {$file}")->run();
      }

      return $result;
    }

  }

  public function buildbranch($branch_name) {
    $build_branch_name = $this->buildbranchname();

    $pull = $this->pullbranch($build_branch_name);
    if (!$pull->wasSuccessful()) {
      return FALSE;
    }

    $composer_update = $this->taskComposerUpdate()->run();
    if (!$composer_update->wasSuccessful()) {
      return FALSE;
    }

    $this->cleanup_git();

  }

  private function pullbranch($branch_name) {
    $build_branch_name = $this->buildbranchname();
    return $this->taskGitStack()
      ->pull('origin ' . $build_branch_name)
      ->run();
  }

  private function cleanup_git() {
    exec("find 'vendor' -type d | grep '\.git' | xargs rm -rfv");
    exec("find 'vendor' -type d | grep '\.git' | xargs rm -rfv");
  }

  private function buildbranchname($branch_name = 'master') {
    $build_branch_name = $branch_name . '-build';
    return $build_branch_name;
  }
}
