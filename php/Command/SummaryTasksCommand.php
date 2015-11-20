<?php

namespace Command;

use Command\Base\BaseCommand;
use Survos\Client\Resource\SurveyResource;
use Survos\Client\Resource\TaskResource;
use Survos\Client\Resource\WaveResource;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Survos\Client\Resource\MemberResource;
use Survos\Client\Resource\ProjectResource;
use Survos\Client\Resource\UserResource;
use Symfony\Component\Console\Helper\Table;

class SummaryTasksCommand extends BaseCommand
{
    protected function configure()
    {
        $this
            ->setName('summary:tasks')
            ->setDescription('Show basic summary for tasks')
            ->addOption(
                'project-code',
                null,
                InputOption::VALUE_OPTIONAL,
                'Project code'
            )->addOption(
                'limit',
                null,
                InputOption::VALUE_OPTIONAL,
                '',
                100
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // important don't remove
        parent::execute($input, $output);
        $isVerbose = $output->getVerbosity() >= OutputInterface::VERBOSITY_VERBOSE;
        $projectCode = $input->getOption('project-code');
        $limit = $input->getOption('limit');

        $tasksResource = new TaskResource($this->sourceClient);

        $params = [];
        if ($projectCode) {
            $params['project_code'] = $projectCode;
        }

        $page = 0;
        $perPage = 10;
        $maxPages = 1;
        $criteria = [];
        $data = [];
        $no = 1;
        while ($page < $maxPages) {
            $tasks = $tasksResource->getList(++$page, $perPage, $criteria, [], [], $params);
            $maxPages = $tasks['pages'];
            // if no items, return
            if (!count($tasks['items']) || !$tasks['total'] || ($limit > 0 && $no > $limit)) {
                break;
            }

            foreach ($tasks['items'] as $key => $task) {

                $data[] = [
                    '#'                => $no,
                    'task_id'          => $task['id'],
                    'code'             => isset($task['code']) ? $task['code'] : '-',
                    'project_code'     => isset($task['project_code']) ? $task['project_code'] : '-',
                    'assignment_count' => isset($task['assignment_count']) ? $task['assignment_count'] : '-',
                    'task_status_code' => isset($task['task_status_code']) ? $task['task_status_code'] : '-',
                ];
                $no++;
            }
        }

        $table = new Table($output);
        $table
            ->setHeaders(
                [
                    '#',
                    'task_id',
                    'code',
                    'project_code',
                    'assignment_count',
                    'task_status_code',
                ]
            )
            ->setRows($data);
        $table->render();
    }
}
