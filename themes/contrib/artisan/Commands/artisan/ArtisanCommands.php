<?php

namespace Drush\Commands\artisan;

use Drush\Attributes as CLI;
use Drush\Commands\DrushCommands;
use Drush\Drush;
use Symfony\Component\Process\Process;
use Symfony\Component\String\UnicodeString;

/**
 * Artisan theme drush commands.
 */
final class ArtisanCommands extends DrushCommands {

  /**
   * Default Artisan command to create subtheme.
   */
  #[CLI\Command(name: 'artisan:create', aliases: ['artisan'])]
  #[CLI\Argument(name: 'name', description: 'Theme name to generate.')]
  #[CLI\Usage(name: 'artisan:create "Artisan Mysite" -y', description: 'Generate "artisan_mysite" subtheme without asking anything.')]
  public function commandName(string $name = 'Artisan subtheme') {
    $name_ask = $this->io()->ask('Please enter/confirm theme name for your new theme (' . $name . ')');
    if (!empty($name_ask)) {
      $name = $name_ask;
    }
    $machine_name = (string) (new UnicodeString($name))->snake();
    if ($this->io()->confirm('New theme will be created "' . $name . '" (themes/custom/' . $machine_name . '), please confirm')) {
      $process = new Process([
        'php', Drush::bootstrapManager()->getRoot() . '/core/scripts/drupal', 'generate-theme', $machine_name, '--name', $name, '--starterkit', 'artisan_starterkit', '--path', 'themes/custom',
      ]);
      $process->run();

      if (!$process->isSuccessful()) {
        $this->logger()->error($process->getErrorOutput());
      }
      else {
        $this->logger()->success($process->getOutput());
      }

      if ($this->io()->confirm('Would you like to compile new theme? (npm dependencies & build)')) {
        $pwd = Drush::bootstrapManager()->getRoot() . '/themes/custom/' . $machine_name;
        $this->logger()->notice('Compiling theme, please wait...');
        $install_process = Process::fromShellCommandline('npm install', $pwd, NULL, NULL, 60);
        $install_process->run();
        if ($install_process->getExitCode()) {
          $this->logger()->error('Ups, something failed, please try it manually "cd ' . $pwd . '" and "npm install"');
        }
        else {
          $this->logger()->notice('Almost done...');
          $compile_process = Process::fromShellCommandline('npm run production', $pwd, NULL, NULL, 60);
          $compile_process->run();

          if ($compile_process->getExitCode()) {
            $this->logger()->error('Ups, something failed, please try it manually "cd ' . $pwd . '" and "npm run production"');
          }
          else {
            if ($this->io()->confirm('Would you like to install & use your new theme? Also install Serialization dependency if needed')) {
              $install_process = Process::fromShellCommandline('drush en serialization -y');
              $install_process->run();
              $install_process = Process::fromShellCommandline('drush theme:enable ' . $machine_name . ' -y');
              $install_process->run();
              $default_process = Process::fromShellCommandline('drush cset system.theme default ' . $machine_name . ' -y');
              $default_process->run();
            }
            $this->logger()->success('Everything ready, please check your new theme files & enjoy!');
          }
        }
      }
    }
  }

}
