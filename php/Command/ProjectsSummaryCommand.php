<?php

namespace Command;

use Command\Base\BaseCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Survos\Client\Resource\MemberResource;
use Survos\Client\Resource\ProjectResource;
use Survos\Client\Resource\UserResource;
use Symfony\Component\Console\Helper\Table;

class ProjectsSummaryCommand extends BaseCommand
{
    protected function configure()
    {
        $this
            ->setName('summary:projects')
            ->setDescription('Show basic summary of a project')
            ->addOption(
                'project-code',
                null,
                InputOption::VALUE_REQUIRED,
                'Project code'
            )
            ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // important don't remove
        parent::execute($input, $output);
        $projectResource = new ProjectResource($this->sourceClient);
        $memberResource = new MemberResource($this->sourceClient);
        $sur

        $result = $projectResource->getList();
        foreach ($result['items'] as $idx => $project) {
            $projectCode = $project['code'];
            $result = $memberResource->getList(1, 1000, ['project_id' => $project['id'], 'permission_type_code' => 'owner']);
            $owners = implode(
                ', ',
                array_map(
                    function ($member) { return $member['code']; },
                    $result['items']
                )
            );
            $data[] =
                [
                    'code' => $projectCode,
                    'owners' => $owners
                ];
        }
        $table = new Table($output);
        $table
            ->setHeaders(['code', 'owners'])
            ->setRows($data);
        $table->render();
    }
}
