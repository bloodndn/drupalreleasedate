<?php
namespace DrupalReleaseDate\Tests;

use DrupalReleaseDate\Sampling\Sample;
use DrupalReleaseDate\Sampling\SampleSet;
use DrupalReleaseDate\Sampling\SampleSetRandomSampleSelector;
use DrupalReleaseDate\MonteCarlo;

class MonteCarloTest extends \PHPUnit_Framework_TestCase {

    /**
     * Test a  MonteCarlo run returning the average value.
     *
     * Since we set up all of the samples to have equal variance, the result
     * is predictable within a single run.
     *
     * @covers \DrupalReleaseDate\MonteCarlo::__construct
     * @covers \DrupalReleaseDate\MonteCarlo::runAverage
     * @covers \DrupalReleaseDate\MonteCarlo::runDistribution
     * @covers \DrupalReleaseDate\MonteCarlo::iteration
     * @uses \DrupalReleaseDate\EstimateDistribution
     * @uses \DrupalReleaseDate\Random\Random
     * @uses \DrupalReleaseDate\Sampling\Sample
     * @uses \DrupalReleaseDate\Sampling\SampleSet
     * @uses \DrupalReleaseDate\Sampling\SampleSetRandomSampleSelector
     */
    function testSingleAverage() {
        $sampleset = new SampleSet();

        $sampleset->insert(new Sample(10, 10));
        $sampleset->insert(new Sample(20,  9));
        $sampleset->insert(new Sample(30,  8));
        $sampleset->insert(new Sample(40,  7));

        $sampleSelector = new SampleSetRandomSampleSelector($sampleset);

        $montecarlo = new MonteCarlo($sampleSelector);

        $result = $montecarlo->runAverage(1, 10);

        $this->assertEquals(70, $result);
    }

    /**
     * Test a  MonteCarlo run returning the average value.
     *
     * With varying time periods between samples, the result is not predictable
     * for a single run, but should converge on the same value after many
     * iterations.
     *
     * @covers \DrupalReleaseDate\MonteCarlo::__construct
     * @covers \DrupalReleaseDate\MonteCarlo::runAverage
     * @covers \DrupalReleaseDate\MonteCarlo::runDistribution
     * @covers \DrupalReleaseDate\MonteCarlo::iteration
     * @uses \DrupalReleaseDate\EstimateDistribution
     * @uses \DrupalReleaseDate\Random\Random
     * @uses \DrupalReleaseDate\Sampling\Sample
     * @uses \DrupalReleaseDate\Sampling\SampleSet
     * @uses \DrupalReleaseDate\Sampling\SampleSetRandomSampleSelector
     */
    function testAverage() {
        $sampleset = new SampleSet();

        $sampleset->insert(new Sample(10, 10));
        $sampleset->insert(new Sample(20,  9));
        $sampleset->insert(new Sample(40,  8));
        $sampleset->insert(new Sample(80,  7));

        $sampleSelector = new SampleSetRandomSampleSelector($sampleset);

        $montecarlo = new MonteCarlo($sampleSelector);

        $result = $montecarlo->runAverage(RANDOM_BASE_ITERATIONS, 10);

        $this->assertEquals(163.0, $result, '', 1);
    }

    /**
     * Test a  MonteCarlo run returning the median value.
     *
     * Since we set up all of the samples to have equal variance, the result
     * is predictable within a single run.
     *
     * @covers \DrupalReleaseDate\MonteCarlo::__construct
     * @covers \DrupalReleaseDate\MonteCarlo::runMedian
     * @covers \DrupalReleaseDate\MonteCarlo::runDistribution
     * @covers \DrupalReleaseDate\MonteCarlo::iteration
     * @uses \DrupalReleaseDate\EstimateDistribution
     * @uses \DrupalReleaseDate\Random\Random
     * @uses \DrupalReleaseDate\Sampling\Sample
     * @uses \DrupalReleaseDate\Sampling\SampleSet
     * @uses \DrupalReleaseDate\Sampling\SampleSetRandomSampleSelector
     */
    function testSingleMedian() {
        $sampleset = new SampleSet();

        $sampleset->insert(new Sample(10, 10));
        $sampleset->insert(new Sample(20,  9));
        $sampleset->insert(new Sample(30,  8));
        $sampleset->insert(new Sample(40,  7));

        $sampleSelector = new SampleSetRandomSampleSelector($sampleset);

        $montecarlo = new MonteCarlo($sampleSelector);

        // Run a single iteration, grouping value into buckets of size 10.
        $result = $montecarlo->runMedian(1, 10);

        $this->assertEquals(70, $result);
    }

    /**
     * Test a MonteCarlo run returning the median value.
     *
     * With varying time periods between samples, the result is not predictable
     * for a single run, but should converge on the same value after many
     * iterations.
     *
     * @covers \DrupalReleaseDate\MonteCarlo::__construct
     * @covers \DrupalReleaseDate\MonteCarlo::runMedian
     * @covers \DrupalReleaseDate\MonteCarlo::runDistribution
     * @covers \DrupalReleaseDate\MonteCarlo::iteration
     * @uses \DrupalReleaseDate\EstimateDistribution
     * @uses \DrupalReleaseDate\Random\Random
     * @uses \DrupalReleaseDate\Sampling\Sample
     * @uses \DrupalReleaseDate\Sampling\SampleSet
     * @uses \DrupalReleaseDate\Sampling\SampleSetRandomSampleSelector
     */
    function testMedian() {
        $sampleset = new SampleSet();

        $sampleset->insert(new Sample(10, 10));
        $sampleset->insert(new Sample(20,  9));
        $sampleset->insert(new Sample(40,  8));
        $sampleset->insert(new Sample(80,  7));

        $sampleSelector = new SampleSetRandomSampleSelector($sampleset);

        $montecarlo = new MonteCarlo($sampleSelector);

        // Run multiple iterations, grouping into buckets of size 10.
        $result = $montecarlo->runMedian(RANDOM_BASE_ITERATIONS, 10);

        $this->assertEquals(160, $result);
    }

    /**
     * Test that when sampled values increase, the run errors out as expected.
     *
     * @expectedException \DrupalReleaseDate\MonteCarloIncreasingRunException
     *
     * Since all iterations will fail, the first run after the threshold is met
     * will cause the run to fail.
     *
     * @expectedExceptionMessage Run aborted after iteration 11
     *
     * @covers \DrupalReleaseDate\MonteCarlo::__construct
     * @covers \DrupalReleaseDate\MonteCarlo::runAverage
     * @covers \DrupalReleaseDate\MonteCarlo::runDistribution
     * @covers \DrupalReleaseDate\MonteCarlo::iteration
     * @uses \DrupalReleaseDate\EstimateDistribution
     * @uses \DrupalReleaseDate\Random\Random
     * @uses \DrupalReleaseDate\Sampling\Sample
     * @uses \DrupalReleaseDate\Sampling\SampleSet
     * @uses \DrupalReleaseDate\Sampling\SampleSetRandomSampleSelector
     */
    function testIncreasingAverageRun() {
        $sampleset = new SampleSet();

        $sampleset->insert(new Sample(10, 10));
        $sampleset->insert(new Sample(20, 11));
        $sampleset->insert(new Sample(30, 12));
        $sampleset->insert(new Sample(40, 13));

        $sampleSelector = new SampleSetRandomSampleSelector($sampleset);

        $montecarlo = new MonteCarlo($sampleSelector);

        $average = $montecarlo->runAverage(100);
    }

    /**
     * Test that when sampled values increase, the run errors out as expected.
     *
     * @expectedException \DrupalReleaseDate\MonteCarloIncreasingRunException
     *
     * Since all iterations will fail, the first run after the threshold is met
     * will cause the run to fail.
     *
     * @expectedExceptionMessage Run aborted after iteration 11
     *
     * @covers \DrupalReleaseDate\MonteCarlo::__construct
     * @covers \DrupalReleaseDate\MonteCarlo::runMedian
     * @covers \DrupalReleaseDate\MonteCarlo::runDistribution
     * @covers \DrupalReleaseDate\MonteCarlo::iteration
     * @uses \DrupalReleaseDate\EstimateDistribution
     * @uses \DrupalReleaseDate\Random\Random
     * @uses \DrupalReleaseDate\Sampling\Sample
     * @uses \DrupalReleaseDate\Sampling\SampleSet
     * @uses \DrupalReleaseDate\Sampling\SampleSetRandomSampleSelector
     */
    function testIncreasingMedianRun() {
        $sampleset = new SampleSet();

        $sampleset->insert(new Sample(10, 10));
        $sampleset->insert(new Sample(20, 11));
        $sampleset->insert(new Sample(30, 12));
        $sampleset->insert(new Sample(40, 13));

        $sampleSelector = new SampleSetRandomSampleSelector($sampleset);

        $montecarlo = new MonteCarlo($sampleSelector);

        $median = $montecarlo->runMedian(100, 10);
        $this->assertEquals(0, $median);
    }
}
