<?php

namespace Command;

use Command\Base\BaseCommand;
use Survos\Client\Resource\SurveyResource;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Survos\Client\SurvosClient;
use Survos\Client\Resource\MemberResource;
use Survos\Client\Resource\ProjectResource;
use Survos\Client\Resource\UserResource;


class SurveysXferCommand extends BaseCommand
{
    protected function configure()
    {
        parent::configure();
        $this
            ->setName('surveys:xfer')
            ->setDescription('Transfer surveys from source server')
            ->addOption(
                'source-project-code',
                null,
                InputOption::VALUE_REQUIRED,
                'Source project code'
            )
            ->addOption(
                'target-project-code',
                null,
                InputOption::VALUE_REQUIRED,
                'Target project code'
            )
            ->addOption(
                'source-survey-code',
                null,
                InputOption::VALUE_REQUIRED,
                'Source survey code'
            )
            ->addOption(
                'target-survey-code',
                null,
                InputOption::VALUE_REQUIRED,
                'Target survey code'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // important don't remove
        parent::execute($input, $output);
        $isVerbose = $output->getVerbosity() >= OutputInterface::VERBOSITY_VERBOSE;

        $sourceProject = $input->getOption('source-project-code');
        $targetProject = $input->getOption('target-project-code');

        $sourceSurvey = $input->getOption('source-survey-code');
        $targetSurvey = $input->getOption('target-survey-code');

        /** @type SurveyResource $fromSurveyResource */
        $fromSurveyResource = new SurveyResource($this->sourceClient);
        $toSurveyResource = new SurveyResource($this->client);
        $projectResource = new ProjectResource($this->client);
        $project = $projectResource->getByCode($targetProject);

        $survey = $fromSurveyResource->getByCode($sourceSurvey, ['project_code' => $sourceProject]);
        $surveyJson = $fromSurveyResource->getExportJson($survey['id']);

        $toSurveyResource->import($surveyJson);
//        if (!$project) {
//            $output->writeln(
//                "<error>Target project {$targetProject} not found</error>"
//            );
//            die();
//        }



    }

    /**
     * prepare member array to be imported
     *
     * @param $memberFields
     * @param $newProject
     */
    private function processMemberFields(&$memberFields, $newProject)
    {
        unset($memberFields['id']);
        unset($memberFields['created_at']);
        unset($memberFields['updated_at']);
        unset($memberFields['member_type_code']);
        unset($memberFields['task_count']);
        unset($memberFields['assignment_count']);
        unset($memberFields['device_point_count']);
        unset($memberFields['fields_from_project']);
        $memberFields['project_id'] = $newProject['id'];
    }
}
