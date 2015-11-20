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
use Symfony\Component\OptionsResolver\OptionsResolver;

class SummaryTasksCommand extends BaseCommand
{
    protected function configure()
    {
        $this
            ->setName('task:summary')
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

            )->addOption(
                'task_type_code',
                null,
                InputOption::VALUE_OPTIONAL
            )->addOption(
                'deployment',
                null,
                InputOption::VALUE_OPTIONAL
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
        $criteria = [];
        if ($taskTypeCode = $input->getOption('task_type_code')) {
            $criteria['task_type_code'] = $taskTypeCode;
        }

        $page = 0;
        $perPage = 100;
        $maxPages = 1;
        $data = [];
        $no = 1;

        $resolver = new OptionsResolver();
        $resolver->setDefaults($keys=[
            'no' => null,
            'code' => '',
            'wave_id' => null,
            'project_code' => null,
            'assignment_count' => null,
            'task_type_code' => null,
            'task_status_code' => null,
            'expiration_time' => null,
            'reward' => null,
            'max_assignments' => null
        ]);
        $table = new Table($output);
        $table
            ->setHeaders(
                array_keys($keys)
            );

        while ($page < $maxPages) {
            $tasks = $tasksResource->getList(++$page, $perPage, $criteria, [], [], $params);
            $maxPages = $tasks['pages'];
            // if no items, return
            printf("Items: %d of Total: %d, Limit: $limit, No: $no\n", count($tasks['items']), $tasks['total']);
            if (!count($tasks['items']) || !$tasks['total'] || ($limit > 0 && $no > $limit)) {
                break;
            }

            foreach ($tasks['items'] as $key => $task) {
                foreach ($keys as $key=>$default)
                {
                    $taskData[$key] = isset($task[$key]) ? $task[$key] : $default;
                }
                // $taskData['#'] = $no;
                // $taskData = $resolver->resolve($task);


                $data[] = [
                    '#' => $no,
                    'task_id' => $task['id'],
                    'code' => isset($task['code']) ? $task['code'] : '-',
                    'wave_id' => isset($task['wave_id']) ? $task['wave_id'] : '-',
                    'project_code' => isset($task['project_code']) ? $task['project_code'] : '-',
                    'assignment_count' => isset($task['assignment_count']) ? $task['assignment_count'] : '-',
                    'task_type_code' => isset($task['task_type_code']) ? $task['task_type_code'] : '-',
                    'task_status_code' => isset($task['task_status_code']) ? $task['task_status_code'] : '-',
                    'expiration_time' => isset($task['expiration_time']) ? $task['expiration_time'] : '-',
                    'reward' => isset($task['reward']) ? $task['reward'] : '-',
                    'max_assignments' => isset($task['max_assignments']) ? $task['max_assignments'] : '-',
                ];
                $no++;
            }
            $table
                ->addRow($taskData);

            $table->render();
        }
    }
}
