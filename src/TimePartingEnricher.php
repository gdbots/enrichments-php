<?php
declare(strict_types=1);

namespace Gdbots\Enrichments;

use Gdbots\Pbj\WellKnown\Microtime;
use Gdbots\Pbjx\DependencyInjection\PbjxEnricher;
use Gdbots\Pbjx\Event\PbjxEvent;
use Gdbots\Pbjx\EventSubscriber;
use Gdbots\Schemas\Common\Enum\DayOfWeek;
use Gdbots\Schemas\Common\Enum\Month;
use Gdbots\Schemas\Enrichments\Mixin\TimeParting\TimePartingV1Mixin;
use Gdbots\Schemas\Pbjx\Mixin\Event\EventV1Mixin;

final class TimePartingEnricher implements EventSubscriber, PbjxEnricher
{
    public static function getSubscribedEvents()
    {
        return [
            TimePartingV1Mixin::SCHEMA_CURIE . '.enrich' => ['enrich', 5000],
        ];
    }

    public function enrich(PbjxEvent $pbjxEvent): void
    {
        $message = $pbjxEvent->getMessage();
        $date = $message->get(EventV1Mixin::OCCURRED_AT_FIELD);

        if ($date instanceof Microtime) {
            $date = $date->toDateTime();
        }

        if (!$date instanceof \DateTimeInterface) {
            // no "occurred_at" field to pull from.
            return;
        }

        $dayOfWeek = (int)$date->format('w');
        $message
            ->set(TimePartingV1Mixin::MONTH_OF_YEAR_FIELD, Month::create((int)$date->format('n')))
            ->set(TimePartingV1Mixin::DAY_OF_MONTH_FIELD, (int)$date->format('j'))
            ->set(TimePartingV1Mixin::DAY_OF_WEEK_FIELD, DayOfWeek::create($dayOfWeek))
            ->set(
                TimePartingV1Mixin::IS_WEEKEND_FIELD,
                $dayOfWeek === DayOfWeek::SUNDAY
                || $dayOfWeek === DayOfWeek::SATURDAY
                || $dayOfWeek === DayOfWeek::SUNDAY_TOO
            )
            ->set(TimePartingV1Mixin::HOUR_OF_DAY_FIELD, (int)$date->format('G'));
    }
}
