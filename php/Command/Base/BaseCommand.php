<?php

namespace Command\Base;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Survos\Client\SurvosClient;
use Survos\Client\Resource\MemberResource;
use Survos\Client\Resource\ProjectResource;
use Survos\Client\Resource\UserResource;
use Symfony\Component\Yaml\Parser;


class BaseCommand extends Command
{
    protected $parameters;

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $yaml = new Parser();

        if (!file_exists('config.yml')) {
            $output->writeln('<error>Config file could not be found. Please run "composer install"</error>');
            die();
        }
        $params = $yaml->parse(file_get_contents('config.yml'));
        $this->parameters = $params['parameters'];
        if (!is_array($this->parameters) || !count($this->parameters)) {
            $output->writeln('<error>Config file could not be found or is not correct</error>');
            die();
        }
    }

}
