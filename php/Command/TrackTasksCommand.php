<?php

namespace Command;

use Command\Base\BaseCommand;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Survos\Client\SurvosClient;
use Survos\Client\Resource\MemberResource;
use Survos\Client\Resource\ProjectResource;
use Survos\Client\Resource\UserResource;

class TrackTasksCommand extends BaseCommand
{
    protected function configure()
    {
        parent::configure();
        $this
            ->setName('track:tasks')
            ->setDescription('Process Tracking Tasks')
            ->addOption(
                'project-code',
                null,
                InputOption::VALUE_REQUIRED,
                'Source project code'
            )
            ->addOption(
                'enrollment-status-code',
                null,
                InputOption::VALUE_OPTIONAL,
                'Enrollment status code'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $isVerbose = $output->getVerbosity() >= OutputInterface::VERBOSITY_VERBOSE;
        $projectCode = $input->getOption('project-code');
        $enrollmentStatusCode = $input->getOption('enrollment-status-code');

        $memberResource = new MemberResource($this->sourceClient);


        $project = 'behattest';
        $memberCode = 'otest';
        $date = '2016-01-31';

        $assignments = $this->getTrackingAssignments($client = $this->sourceClient, $project, $memberCode, $date);

        foreach ($assignments['items'] as $assignment) {
            // perhaps the assignment should return the survey id, so we can get it?  Or even better, an option to include the task and survey JSON when calling getAssignments
            $tracks = $this->getTracks($client, $assignment['scheduled_time'], $assignment['scheduled_end_time']);
            if (false !== $center = $this->getTracksCenter($tracks)) {
                $assignment['center_lat_lng'] = $center;
                $this->saveAssignment($client, $assignment);
            }
        }
    }

        function getTrackingTasks($client)
        {
            $resource = new \Survos\Client\Resource\TaskResource($client);
            return $resource->getList(null, null, ['task_type_code' => 'device']);
        }

        function getTrackingAssignments($client, $project = null, $memberCode = null, $date = null)
        {
            $resource = new \Survos\Client\Resource\AssignmentResource($client, $params = []);
            $filter = ['score' => 0];
            $comparison = ['score' => \Survos\Client\SurvosCriteria::GREATER_THAN];
            $params = ['task_type_code' => 'device'];
            if (null !== $project) {
                $params['project_code'] = $project;
            }
            if (null !== $memberCode) {
                $params['member_code'] = $memberCode;
            }
            if (null !== $date) {
                $filter['scheduled_time'] = $date;
                $filter['scheduled_end_time'] = $date;
                $comparison['scheduled_time'] = \Survos\Client\SurvosCriteria::LESS_EQUAL;
                $comparison['scheduled_end_time'] = \Survos\Client\SurvosCriteria::GREATER_EQUAL;

            }
            return $resource->getList(null, null, $filter=[], $comparison, null, $params);
        }

        function saveAssignment($client, $data)
        {
            $resource = new \Survos\Client\Resource\AssignmentResource($client);
            $response = $resource->save($data);
        }

        function getTracks($client, $fromTime, $toTime)
        {
            $filter = ['timestamp' => [$fromTime, $toTime]];
            $comparison = ['timestamp' => \Survos\Client\SurvosCriteria::BETWEEN];
            $orderBy = [['column' => 'timestamp', 'dir' => \Survos\Client\SurvosCriteria::ASC]];
            $resource = new \Survos\Client\Resource\TrackResource($client);
            return $resource->getList(null, null, $filter, $comparison, $orderBy);
        }

        function getTracksCenter(array $tracks)
        {
            $points = [];
            foreach ($tracks['items'] as $track) {
                $points[] = [$track['latitude'], $track['longitude']];
            }
            return $this->GetCenterFromDegrees($points);
        }

        /**
         * Get a center latitude,longitude from an array of like geopoints
         * Taken from here http://stackoverflow.com/a/18623672
         * Eventually can be used https://github.com/bdelespierre/php-kmeans
         * @param array $data
         * @return array|bool
         */
        function GetCenterFromDegrees($data)
        {
            if (!is_array($data)) return FALSE;

            $num_coords = count($data);

            $X = 0.0;
            $Y = 0.0;
            $Z = 0.0;

            foreach ($data as $coord) {
                $lat = $coord[0] * pi() / 180;
                $lon = $coord[1] * pi() / 180;

                $a = cos($lat) * cos($lon);
                $b = cos($lat) * sin($lon);
                $c = sin($lat);

                $X += $a;
                $Y += $b;
                $Z += $c;
            }

            $X /= $num_coords;
            $Y /= $num_coords;
            $Z /= $num_coords;

            $lon = atan2($Y, $X);
            $hyp = sqrt($X * $X + $Y * $Y);
            $lat = atan2($Z, $hyp);

            return array($lat * 180 / pi(), $lon * 180 / pi());
        }
    }