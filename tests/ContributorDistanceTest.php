<?php
use phpunit\framework\TestCase;

// Note: the data in /data must be imported via the build_contributor_distances command
// before running the tests. In a real environment, the mock up data would be setup
// in the `setup()` handler and cleaned up in `teardown()`.

class ContributorDistanceTest extends TestCase
{

    private function makeApiCall($user1, $user2) {
        $r = file_get_contents('http://127.0.0.1:8000/contributor_distances/' . $user1 . '_' . $user2);
        return json_decode($r, true);
    }

    public function testRelationDoesntExist()
    {
        $r = $this->makeApiCall(1,2);
        $this->assertEquals(0, $r['distance']);
    }

    public function testShortestDistanceEqualInBothDirections()
    {
        $r1 = $this->makeApiCall(1285584, 1435635);
        $r2 = $this->makeApiCall(1435635, 1285584);
        $this->assertEquals($r1['distance'], $r2['distance']);
    }

    public function testShortestDistanceIsCorrect()
    {
        // Check some distances that we know to be correct
        $r = $this->makeApiCall(1285584, 109270);
        $this->assertEquals(2, $r['distance']);

        $r = $this->makeApiCall(1285584, 10111);
        $this->assertEquals(1, $r['distance']);
    }

}