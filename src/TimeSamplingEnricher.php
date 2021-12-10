<?php
declare(strict_types=1);

namespace Gdbots\Enrichments;

use Gdbots\Pbj\WellKnown\Microtime;
use Gdbots\Pbjx\DependencyInjection\PbjxEnricher;
use Gdbots\Pbjx\Event\PbjxEvent;
use Gdbots\Pbjx\EventSubscriber;

final class TimeSamplingEnricher implements EventSubscriber, PbjxEnricher
{
    public static function getSubscribedEvents(): array
    {
        return [
            'gdbots:enrichments:mixin:time-sampling.enrich' => ['enrich', 5000],
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

        $message
            ->set('ts_ymdh', (int)$date->format('YmdH'))
            ->set('ts_ymd', (int)$date->format('Ymd'))
            ->set('ts_ym', (int)$date->format('Ym'));
    }
}
