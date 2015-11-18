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

class ImportProjectsCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('import:projects')
            ->setDescription('Import projects from CSV file')
            ->addArgument(
                'filename',
                InputArgument::REQUIRED,
                'path to the CSV file'
            )
            ->addOption(
                'server-code',
                null,
                InputOption::VALUE_REQUIRED,
                'Server code'
            )
            ->addOption(
                'timezone-id',
                null,
                InputOption::VALUE_OPTIONAL,
                'Timezone ID from Survos (default 295 / America/New_York)',
                295
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $filename = $input->getArgument('filename');
        $serverCode = $input->getOption('server-code');
        $timezoneId = $input->getOption('timezone-id');


        $configPath = __DIR__.'/../config.json';
        if (!file_exists($configPath)) {
            $output->writeln('<error>Configuration file not found</error>');

            return;
        }
        $config = json_decode(file_get_contents($configPath), true);

        $client = new SurvosClient($config['endpoint']);
        if (!$client->authorize($config['username'], $config['password'])) {
            $output->writeln('<error>Wrong credentials</error>');
            print_r($config);

            return;
        }

        $userResource = new UserResource($client);
        /** @type MemberResource $memberResource */
        $memberResource = new MemberResource($client);
        $projectResource = new ProjectResource($client);


        /** @type ProjectResource $resource */
        $resource = new ProjectResource($client);

        $reader = new \EasyCSV\Reader($filename, 'r+', true);

        while ($row = $reader->getRow()) {
            //code,name,description,timezone_id

            $code = $row['code'];
            $params = [];
            if ($project = $resource->getOneBy(['code' => $code])) {
                $params['id'] = $project['id'];
            }

            try {
                $res = $resource->save(
                    array_merge(
                        $params,
                        [
                            'title'                  => $name = $row['name'],
                            'code'                   => $code,
                            'timezone_id'            => $timezoneId,
                            'description'            => $name." Project",
                            'background_server_code' => $serverCode,
                        ]
                    )
                );
            } catch (\Exception $e) {
                printf("Error saving project: %s\n", $e->getMessage());
                printf("Project $code already exists\n");
            }

            $project = $projectResource->getByCode($code);

            $res = $resource->addModule($code, 'turk');

            try {
                foreach ([$code, 'tac', 'ho449'] as $idx => $username) {
                    $user = $userResource->getOneBy(['username' => $username]);

                    if (!$user) {
                        print "user '$username' not found\n";
                        continue;
                    }
                    
                    $params = [
                        'code'                 => $username,
                        'project_id'           => $project['id'],
                        'user_id'              => $user['id'],
                        'permission_type_code' => 'owner',
                    ];
                    $response = $memberResource->getList(1, 1, ['code' => $username, 'project_id' => $project['id']]);
                    $members = $response['items'];
                    if ($members) {
                        print "Member '$username' already exists for project ".$project['id']."\n";
                        continue;
//                $params['id'] = $members[0]['id'];
                    }
                    print "Saving member '$username' for project ".$project['id']."\n";
                    $res = $memberResource->save($params);
                }
            } catch (Exception $e) {
                var_dump($params);
                print "Error importing member: ".$e->getMessage()."\n";
            }


        }
    }
}
