<?php
declare(strict_types=1);

namespace Gdbots\Tests\Enrichments;

use Gdbots\Enrichments\TimePartingEnricher;
use Gdbots\Pbj\WellKnown\Microtime;
use Gdbots\Pbjx\Event\PbjxEvent;
use Gdbots\Schemas\Common\Enum\DayOfWeek;
use Gdbots\Schemas\Common\Enum\Month;
use PHPUnit\Framework\TestCase;

class TimePartingEnricherTest extends TestCase
{
    public function testEnrich()
    {
        $command = Fixtures\FakeCommand::create();
        $command->set('occurred_at', Microtime::fromFloat((new \DateTime('2015-12-25T01:15:30.123456Z'))->format('U.u')));
        $enricher = new TimePartingEnricher();
        $pbjxEvent = new PbjxEvent($command);

        $enricher->enrich($pbjxEvent);

        $this->assertSame(Month::DECEMBER(), $command->get('month_of_year'));
        $this->assertSame(25, $command->get('day_of_month'));
        $this->assertSame(DayOfWeek::FRIDAY(), $command->get('day_of_week'));
        $this->assertSame(false, $command->get('is_weekend'));
        $this->assertSame(1, $command->get('hour_of_day'));
    }
}
