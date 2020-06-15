<?php
declare(strict_types=1);

namespace Gdbots\Enrichments;

use Gdbots\Pbj\WellKnown\Microtime;
use Gdbots\Pbjx\DependencyInjection\PbjxEnricher;
use Gdbots\Pbjx\Event\PbjxEvent;
use Gdbots\Pbjx\EventSubscriber;
use Gdbots\Schemas\Enrichments\Mixin\TimeSampling\TimeSamplingV1Mixin;
use Gdbots\Schemas\Pbjx\Mixin\Event\EventV1Mixin;

final class TimeSamplingEnricher implements EventSubscriber, PbjxEnricher
{
    public static function getSubscribedEvents()
    {
        return [
            TimeSamplingV1Mixin::SCHEMA_CURIE . '.enrich' => ['enrich', 5000],
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

        $message
            ->set(TimeSamplingV1Mixin::TS_YMDH_FIELD, (int)$date->format('YmdH'))
            ->set(TimeSamplingV1Mixin::TS_YMD_FIELD, (int)$date->format('Ymd'))
            ->set(TimeSamplingV1Mixin::TS_YM_FIELD, (int)$date->format('Ym'));
    }
}
