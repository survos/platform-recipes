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
    /**
     * @type SurvosClient
     */
    protected $client;
    /**
     * @type SurvosClient
     */
    protected $sourceClient;

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

        // configure target client
        $this->client = new SurvosClient($this->parameters['target']['endpoint']);

        if (!$this->client->authorize(
            $this->parameters['target']['username'],
            $this->parameters['target']['password']
        )
        ) {
            $output->writeln(
                "<error>Wrong credentials for target endpoint: {$this->parameters['target']['endpoint']}</error>"
            );
            // die();
        }

        // configure source client (optional)
        $this->sourceClient = null;

        if ($this->parameters['source']) {
            $this->sourceClient = new SurvosClient($this->parameters['source']['endpoint']);

            if (!$this->sourceClient->authorize(
                $this->parameters['source']['username'],
                $this->parameters['source']['password']
            )
            ) {
                $output->writeln(
                    "<error>Wrong credentials for source endpoint: {$this->parameters['source']['endpoint']}</error>"
                );
                die();
            }
        }

    }

}
