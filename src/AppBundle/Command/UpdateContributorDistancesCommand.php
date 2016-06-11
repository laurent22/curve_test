<?php

namespace AppBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class UpdateContributorDistancesCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('app:update_contributor_distances')
            ->setDescription('Update existing contributor distances');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // In some cases, for example, after a rebase followed by a push, two
        // contributors that were previously in the same repository may no longer
        // be. This command utility will handle this case, and also handle the case
        // where the repository no longer exist.

        $api = $this->getContainer()->get('app.github_api');
        $contributorRelationService = $this->getContainer()->get('app.contributor_relation_service');

        $relationKeys = $contributorRelationService->getRelationKeys();

        foreach ($relationKeys as $relationKey) {
            $relation = $contributorRelationService->getRelationByKey($relationKey);
            // Only process relations that are more than 7 days old
            if (time() - $relation['time'] < 86400 * 7) continue;

            $contributors = $api->repositoryContributors($relation['repositoryId']);
            $found1 = false;
            $found2 = false;
            foreach ($contributors as $c) {
                if ($c['id'] == $relation['user1']) $found1 = true;
                if ($c['id'] == $relation['user2']) $found2 = true;
                if ($found1 && $found2) break;
            }

            if (!$found1 || !$found2) {
                // Clear the relation since they are no longer found in the same repository
                $contributorRelationService->delete($relation['user1'], $relation['user2']);
            }
        }
    }
}