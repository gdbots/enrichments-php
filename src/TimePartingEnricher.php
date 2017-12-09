<?php
declare(strict_types=1);

namespace Gdbots\Enrichments;

use Gdbots\Pbj\WellKnown\Microtime;
use Gdbots\Pbjx\Event\PbjxEvent;
use Gdbots\Pbjx\EventSubscriber;
use Gdbots\Schemas\Common\Enum\DayOfWeek;
use Gdbots\Schemas\Common\Enum\Month;
use Gdbots\Schemas\Enrichments\Mixin\TimeParting\TimeParting;

final class TimePartingEnricher implements EventSubscriber
{
    /**
     * @param PbjxEvent $pbjxEvent
     */
    public function enrich(PbjxEvent $pbjxEvent): void
    {
        /** @var TimeParting $message */
        $message = $pbjxEvent->getMessage();
        $date = $message->get('occurred_at');

        if ($date instanceof Microtime) {
            $date = $date->toDateTime();
        }

        if (!$date instanceof \DateTime) {
            // no "occurred_at" field to pull from.
            return;
        }

        $dayOfWeek = (int)$date->format('w');
        $message
            ->set('month_of_year', Month::create((int)$date->format('n')))
            ->set('day_of_month', (int)$date->format('j'))
            ->set('day_of_week', DayOfWeek::create($dayOfWeek))
            ->set(
                'is_weekend',
                $dayOfWeek === DayOfWeek::SUNDAY
                || $dayOfWeek === DayOfWeek::SATURDAY
                || $dayOfWeek === DayOfWeek::SUNDAY_TOO
            )
            ->set('hour_of_day', (int)$date->format('G'));
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            'gdbots:enrichments:mixin:time-parting.enrich' => [['enrich', 5000]],
        ];
    }
}
