<?php

namespace Command;

use Command\Base\BaseCommand;
use Survos\Client\Resource\JobResource;
use Survos\Client\Resource\SurveyResource;
use Survos\Client\Resource\WaveResource;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Survos\Client\SurvosClient;
use Survos\Client\Resource\MemberResource;
use Survos\Client\Resource\ProjectResource;
use Survos\Client\Resource\UserResource;


class WavesXferCommand extends BaseCommand
{
    protected function configure()
    {
        parent::configure();
        $this
            ->setName('waves:xfer')
            ->setDescription('Transfer wavse from source server')
            ->addOption(
                'source-project-code',
                null,
                InputOption::VALUE_REQUIRED,
                'Source project code'
            )
            ->addOption(
                'target-project-code',
                null,
                InputOption::VALUE_OPTIONAL,
                'Target project code'
            )
            ->addOption(
                'source-wave-id',
                null,
                InputOption::VALUE_REQUIRED,
                'Source survey ID'
            )
            ->addOption(
                'target-job-code',
                null,
                InputOption::VALUE_OPTIONAL,
                'Target survey code'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // important don't remove
        parent::execute($input, $output);
        $isVerbose = $output->getVerbosity() >= OutputInterface::VERBOSITY_VERBOSE;

        $sourceProjectCode = $input->getOption('source-project-code');
        $targetProjectCode = $input->getOption('target-project-code');
        if (!$targetProjectCode) {
            $targetProjectCode = $sourceProjectCode;
        }
        $sourceWaveId = $input->getOption('source-wave-id');

        $targetJobCode = $input->getOption('target-job-code');
        $jobResource = new JobResource($this->client);


        /** @type WaveResource $fromWaveResource */
        $fromWaveResource = new WaveResource($this->sourceClient);
        /** @type WaveResource $toWaveResource */
        $toWaveResource = new WaveResource($this->client);
        $projectResource = new ProjectResource($this->client);

        $sourceProject = $projectResource->getByCode($sourceProjectCode);
        $targetProject = $projectResource->getByCode($targetProjectCode);

        $wave = $fromWaveResource->getById($sourceWaveId);

        // if no target job code set - use current job
        if (!$targetJobCode) {
            $targetJobCode = $wave['job_code'];
        }

        $job = $jobResource->getOneBy(
            ['code' => $targetJobCode],
            [
                'project_code' => $targetProject['code'],
            ]
        );
        if (!$job) {
            throw new \Exception("$job '{$targetJobCode}' not found in project '{$targetProject['code']}'");
        }
        // update target job ID
        // find job ID, then update wave data
        $this->processWaveFields($wave, $job);
        $response = $toWaveResource->save($wave);

        $output->writeln("Wave {$response['code']} #{$response['id']} transferred");
    }

    /**
     * prepare member array to be imported
     *
     * @param $memberFields
     * @param $newProject
     */
    private function processWaveFields(&$wave, $job)
    {
        $wave['job_id'] = $job['id'];
        unset($wave['id']);
        unset($wave['created_at']);
        unset($wave['updated_at']);
        unset($wave['job_code']);
        unset($wave['survey_code']);
        unset($wave['project_code']);
        $wave['weekend_start_time'] = (new \DateTime($wave['weekend_start_time']))->format('h:ma');
        $wave['weekday_start_time'] = (new \DateTime($wave['weekday_start_time']))->format('h:ma');

    }
}
