<?php
declare(strict_types=1);

namespace Gdbots\Tests\Enrichments;

use Gdbots\Enrichments\TimeSamplingEnricher;
use Gdbots\Pbj\WellKnown\Microtime;
use Gdbots\Pbj\WellKnown\NodeRef;
use Gdbots\Pbjx\Event\PbjxEvent;
use Gdbots\Schemas\Ncr\Event\NodePublishedV1;
use PHPUnit\Framework\TestCase;

class TimeSamplingEnricherTest extends TestCase
{
    public function testEnrich()
    {
        $command = NodePublishedV1::create()->set('node_ref', NodeRef::fromString('acme:article:123'));
        $command->set('occurred_at', Microtime::fromDateTime(new \DateTime('2015-12-25T01:15:30.123456Z')));
        $enricher = new TimeSamplingEnricher();
        $pbjxEvent = new PbjxEvent($command);

        $enricher->enrich($pbjxEvent);

        $this->assertSame(2015122501, $command->get('ts_ymdh'));
        $this->assertSame(20151225, $command->get('ts_ymd'));
        $this->assertSame(201512, $command->get('ts_ym'));
    }
}
