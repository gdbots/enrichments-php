<?php
declare(strict_types=1);

namespace Gdbots\Enrichments;

use Gdbots\Pbj\WellKnown\Microtime;
use Gdbots\Pbjx\DependencyInjection\PbjxEnricher;
use Gdbots\Pbjx\Event\PbjxEvent;
use Gdbots\Pbjx\EventSubscriber;
use Gdbots\Schemas\Enrichments\Mixin\TimeSampling\TimeSampling;

final class TimeSamplingEnricher implements EventSubscriber, PbjxEnricher
{
    /**
     * @param PbjxEvent $pbjxEvent
     */
    public function enrich(PbjxEvent $pbjxEvent): void
    {
        /** @var TimeSampling $message */
        $message = $pbjxEvent->getMessage();
        $date = $message->get('occurred_at');

        if ($date instanceof Microtime) {
            $date = $date->toDateTime();
        }

        if (!$date instanceof \DateTime) {
            // no "occurred_at" field to pull from.
            return;
        }

        $message
            ->set('ts_ymdh', (int)$date->format('YmdH'))
            ->set('ts_ymd', (int)$date->format('Ymd'))
            ->set('ts_ym', (int)$date->format('Ym'));
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            'gdbots:enrichments:mixin:time-sampling.enrich' => ['enrich', 5000],
        ];
    }
}
