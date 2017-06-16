<?php
namespace Trendix\AdminBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Style\SymfonyStyle;

class InstallCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            // the name of the command (the part after "app/console")
            ->setName('trendix:admin:install')

            // the short description shown while running "php app/console list"
            ->setDescription('Installs the files needed to user the TrendixAdminBundle.')

            // the full command description shown when running the command with
            // the "--help" option
            ->setHelp('This command allows you to install in your project the files needed for the use of the 
            admin bundle');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $io->note(["You're about to install a bower.json, package.json and install some bower and npm packages with them in the root of your project.",
            "If you have some of these two files or a bower_components directory in the root of your project, please make a backup of them.",
            "Once the bundle is installed, you can add to VCS and modify as you wish any of these files."]);
        $io->caution('Be sure you have installed node and npm on your system before going on.');

        if (!$io->confirm('Are you sure you want to continue?')) {
            return;
        }

        // Copying files...
        $rootPath = $this->getContainer()->get('kernel')->getRootDir() . '/../';
        $output->writeln('Copying files...');
        $success = null;
        system('cp ' . $rootPath . 'vendor/trendix/admin-bundle/Command/files/* ' . $rootPath, $success);
        $output->writeln('Installing gulp and bower globally... You may be asked for root password.');
        system('sudo npm install -g gulp && sudo npm install -g bower');
        $this->checkCommand($success, $io, $success);
        if(!$success) {
            return 0;
        }
        $output->writeln('Installing bower and npm packages locally...');
        system('bower install', $success);
        /*$this->checkCommand($success, $io);
        if(!$success) {
            return 0;
        }*/
        system('npm install', $success);
        $this->checkCommand($success, $io);
        /*if(!$success) {
            return 0;
        }*/
        $output->writeln('Generating symbolic links...');
        system('ln -s ../bower_components ' . $rootPath . 'web/components', $success);
        $this->checkCommand(true, $io, true);
    }

    /**
     * @param $success
     * @param $io
     * @param bool $final
     */
    protected function checkCommand($success, $io, $final = false)
    {
        if (!$success) {
            $io->caution('Something has gone wrong! Please read the errors above, take measures and execute 
            again this command');
        } elseif($final) {
            $io->success("Now you're free to go! Make the changes you consider necessary to gulpfile.js in your project root and and the generated files to VCS, so you don't have to repeat all this process in production.");
            $io->note("In order to reload the CSS or JS after applying changes, execute 'gulp' or just keep 'gulp watch' command active.");
        }
    }
}