<?php
declare(strict_types=1);

namespace Gdbots\Enrichments;

use Gdbots\Pbj\WellKnown\Microtime;
use Gdbots\Pbjx\DependencyInjection\PbjxEnricher;
use Gdbots\Pbjx\Event\PbjxEvent;
use Gdbots\Pbjx\EventSubscriber;
use Gdbots\Schemas\Common\Enum\DayOfWeek;
use Gdbots\Schemas\Common\Enum\Month;

final class TimePartingEnricher implements EventSubscriber, PbjxEnricher
{
    public static function getSubscribedEvents(): array
    {
        return [
            'gdbots:enrichments:mixin:time-parting.enrich' => ['enrich', 5000],
        ];
    }

    public function enrich(PbjxEvent $pbjxEvent): void
    {
        $message = $pbjxEvent->getMessage();
        $date = $message->get('occurred_at') ?: $message->get('created_at');

        if ($date instanceof Microtime) {
            $date = $date->toDateTime();
        }

        if (!$date instanceof \DateTimeInterface) {
            // no "occurred_at" field to pull from.
            return;
        }

        $dayOfWeek = DayOfWeek::from((int)$date->format('w'));
        $message
            ->set('month_of_year', Month::from((int)$date->format('n')))
            ->set('day_of_month', (int)$date->format('j'))
            ->set('day_of_week', $dayOfWeek)
            ->set(
                'is_weekend',
                $dayOfWeek === DayOfWeek::SUNDAY || $dayOfWeek === DayOfWeek::SATURDAY
            )
            ->set('hour_of_day', (int)$date->format('G'));
    }
}
