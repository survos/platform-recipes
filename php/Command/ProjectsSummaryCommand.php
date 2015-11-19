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

        foreach ($projectResource->getList() as $idx=>$project) {
            var_dump($project); die();
            $projectCode = $project['code'];
            $members = $memberResource->getList();
            $data[] =
                [
                    'code' => $projectCode,
                    'memberCount' => count($members)
                ];
        }
        $table = new Table($output);
        $table
            ->setHeaders(['code','memberCount'])
            ->setRows($data);
        $table->render();

    }

}