<?php

namespace Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Survos\Client\SurvosClient;
use Survos\Client\Resource\MemberResource;
use Survos\Client\Resource\ProjectResource;
use Survos\Client\Resource\UserResource;


class ImportMembersCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('import:members')
            ->setDescription('Import members from CSV file')
            ->addArgument(
                'filename',
                InputArgument::REQUIRED,
                'path to the CSV file'
            )
//            ->addOption(
//                'yell',
//                null,
//                InputOption::VALUE_NONE,
//                'If set, the task will yell in uppercase letters'
//            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $name = $input->getArgument('filename');

        $config = json_decode(file_get_contents(__DIR__.'/../config.json'), true);

        $client = new SurvosClient($config['endpoint']);
        if (!$client->authorize($config['username'], $config['password'])) {
            $output->writeln('<err>Wrong credentials!</err>');
        }

        $projectResource = new ProjectResource($client);
        $userResource = new UserResource($client);
        $memberResource = new MemberResource($client);

        $reader = new \EasyCSV\Reader('new_members.csv');

        while ($row = $reader->getRow()) {
            $project = $projectResource->getByCode($row['project_code']);
            // we ned that user for admins, maybe it should be separate field
            $user = $userResource->getOneBy(['username' => $row['username']]);

            if (!$user) {
                print "user '{$row['username']}' not found\n";
            }
            try {
                $res = $memberResource->save(
                    [
                        'code'                 => $row['code'],
                        'project_id'           => $project['id'],
                        'user_id'              => $user['id'],
                        'permission_type_code' => $row['permission_type_code'],
                    ]
                );
            } catch (Exception $e) {
                print "Error importing member {$row['code']}:".$e->getMessage()."\n";
            }

        }

        $output->writeln($text);
    }
}
