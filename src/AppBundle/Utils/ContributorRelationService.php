<?php

namespace AppBundle\Utils;

// Allows setting and retrieving contributor relations. Wraps the Redis
// storage.
class ContributorRelationService
{

	private $container_;

	public function __construct($container)
    {
        $this->container_ = $container;
    }

    public function save($user1, $user2, $repositoryId) {
        $redis = $this->container_->get('snc_redis.default');

        // The key has the format "relation:<userId1>:<userId2> with userId1 < userId2
        $u1 = min($user1, $user2);
        $u2 = max($user1, $user2);
        $redis->set('relation:' . $u1 . ':' . $u2, json_encode(array(
            'distance' => 1,
            'time' => time(),
            'repositoryId' => $repositoryId,
            'user1' => $u1,
            'user2' => $u2,
        )));
    }

    public function delete($user1, $user2) {
        $redis = $this->container_->get('snc_redis.default');

        $u1 = min($user1, $user2);
        $u2 = max($user1, $user2);
        $redis->del('relation:' . $u1 . ':' . $u2);
    }

    public function getRelationKeys() {
        $redis = $this->container_->get('snc_redis.default');
        return $redis->keys('relation:*');
    }

    public function getRelationByKey($key) {
        $redis = $this->container_->get('snc_redis.default');
        return json_decode($redis->get($key), true);
    }

    // Creates graph of nodes in the format required by the
    // Dijkstra algorithm.
    private function createDijkstraGraph() {
        $keys = $this->getRelationKeys();
        $t = array();
        foreach ($keys as $k) {
            $s = explode(':', $k);
            $t[$s[1]][$s[2]] = 1;
            $t[$s[2]][$s[1]] = 1;
        }
        return $t;
    }

    public function getShortestDistance($user1, $user2) {
        // Compute the shortest distance using the Dijkstra algorithm
        require_once __DIR__ . '/Dijkstra.php';
        $graph = $this->createDijkstraGraph();
        $dijkstra = new \Dijkstra($graph);
        $shortestPath = $dijkstra->shortestPaths($user1, $user2);
        return isset($shortestPath[0]) ? count($shortestPath[0]) - 1 : 0;
    }

}