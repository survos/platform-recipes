<?php

namespace Command;

use Command\Base\BaseCommand;
use Survos\Client\Resource\AssignmentResource;
use Survos\Client\Resource\TaskResource;
use Survos\Client\SurvosCriteria;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Survos\Client\SurvosClient;
use Survos\Client\Resource\MemberResource;
use Survos\Client\Resource\ProjectResource;
use Survos\Client\Resource\UserResource;


class AssignmentProcessCommand extends BaseCommand
{
    protected function configure()
    {
        parent::configure();
        $this
            ->setName('assignment:process')
            ->setDescription('Process API assignments')
            ->addOption(
                'project-code',
                null,
                InputOption::VALUE_REQUIRED,
                'Source project code'
            );
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface   $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $isVerbose = $output->getVerbosity() >= OutputInterface::VERBOSITY_VERBOSE;
        $projectCode = $input->getOption('project-code');

        /** @type AssignmentResource $assignmentResource */
        $assignmentResource = new AssignmentResource($this->sourceClient);

        $page = 0;
        $perPage = 10;
        $maxPages = 1;
        $criteria = [];
        $data = [];
        $assignments = $assignmentResource->getList(
            1,
            1,
            [
                'survey_response_status_code' => 'initiated',
            ],
            null,
            null,
            ['project_code' => 'behattest']
        );

        // if no items, return
        if (!count($assignments['items']) || !$assignments['total']) {
            return;
        }
        $tasksIds = array_map(function($item){ return $item['task_id']; }, $assignments['items']);
        $taskResource = new TaskResource($this->sourceClient);
        $tasks = $taskResource->getList(null, null, ['id' => array_unique($tasksIds)], ['id' => SurvosCriteria::IN]);
        $surveyByTask = [];
        foreach ($tasks['items'] as $task) {
            $surveyByTask[$task['id']] = isset($task['survey_json']) ? json_decode($task['survey_json'], true) : null;
        }
        foreach ($assignments['items'] as $key => $assignment) {
//            $taskId = $assignment['task_id'];
//            $survey = $surveyByTask[$taskId];
            if ($isVerbose) {
                $no = ($page - 1) * $perPage + $key + 1;
                $output->writeln("{$no} - Reading assignment #{$assignment['id']}");
            }

            var_dump($assignment);

            if ($isVerbose) {
                $output->writeln("Accepting member #{$assignment['id']}");
            }
        }


    }


    /**
     * @param $member
     * @return array
     */
    private function getMemberCriteria()
    {
        return [
            'enrollment_status_code' => 'applicant',
        ];
    }

}
