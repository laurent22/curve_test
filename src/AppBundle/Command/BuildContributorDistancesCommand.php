<?php

namespace AppBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class BuildContributorDistancesCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('app:build_contributor_distances')
            ->setDescription('Build contributor distances by parsing GitHub repositories');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $api = $this->getContainer()->get('app.github_api');
        $contributorRelationService = $this->getContainer()->get('app.contributor_relation_service');
        $repositories = $api->repositories(1);

        foreach ($repositories as $repo) {
            $output->writeln('Processing repository ' . $repo['id']);

            // The API currently returns mock data. In a live environment, we would track
            // the last repository that has been processed so that we don't have to repeat
            // the complete process every time.
            $contributors = $api->repositoryContributors($repo['id']);
            for ($i = 0; $i < count($contributors) - 1; $i++) {
                for ($j = $i + 1; $j < count($contributors); $j++) {
                    $c1 = $contributors[$i];
                    $c2 = $contributors[$j];
                    $contributorRelationService->save($c1['id'], $c2['id'], $repo['id']);
                }
            }
        }
    }
}