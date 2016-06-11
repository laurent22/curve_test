<?php

namespace AppBundle\Utils;

// Wrapper around some calls of the GitHub API.
class GitHubApi
{

	private $container_;

	public function __construct($container)
    {
        $this->container_ = $container;
    }

	// Calls https://api.github.com/repositories?since=<ID> to retrieve the list of repositories.
	// Caller must keep track of the latest repository that was processed and pass this as the $since parameter.
	// Currently returns mockup data.
    public function repositories($since)
    {
        return array(
            array('id' => 1),
            array('id' => 2),
            array('id' => 3),
            array('id' => 4),
            array('id' => 5),
            array('id' => 6),
        );
    }

    // Calls https://api.github.com/repos/:user/:repo/contributors to retrieve the list of contributors
    // Currently returns mockup data
    public function repositoryContributors($repositoryId) {
    	$data = file_get_contents(dirname($this->container_->get('kernel')->getRootDir()) . '/data/repo_contributors_' . $repositoryId . '.json');
    	return json_decode($data, true);
    }
}