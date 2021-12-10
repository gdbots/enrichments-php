<?php
declare(strict_types=1);

namespace Gdbots\Enrichments;

use Gdbots\Pbj\Util\NumberUtil;
use Gdbots\Pbjx\DependencyInjection\PbjxEnricher;
use Gdbots\Pbjx\Event\PbjxEvent;
use Gdbots\Pbjx\EventSubscriber;
use Gdbots\Schemas\Contexts\UserAgentV1;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use UAParser\Parser;

final class UaParserEnricher implements EventSubscriber, PbjxEnricher
{
    private LoggerInterface $logger;
    private Parser $parser;

    public static function getSubscribedEvents(): array
    {
        return [
            'gdbots:enrichments:mixin:ua-parser.enrich' => ['enrich', 5000],
        ];
    }

    public function __construct(?LoggerInterface $logger = null)
    {
        $this->logger = $logger ?: new NullLogger();
        $this->parser = Parser::create();
    }

    public function enrich(PbjxEvent $pbjxEvent): void
    {
        $message = $pbjxEvent->getMessage();
        if (!$message->has('ctx_ua') || $message->has('ctx_ua_parsed')) {
            return;
        }

        try {
            $result = $this->parser->parse($message->get('ctx_ua'));
        } catch (\Throwable $e) {
            $this->logger->warning('User agent could not be parsed from message [{pbj_schema}].', [
                'exception'  => $e,
                'pbj_schema' => $message::schema()->getId()->toString(),
                'pbj'        => $message->toArray(),
            ]);
            return;
        }

        try {
            $userAgent = UserAgentV1::create()
                ->set('br_family', $result->ua->family)
                ->set('br_major', (int)$result->ua->major)
                ->set('br_minor', (int)$result->ua->minor)
                ->set('br_patch', NumberUtil::bound((int)$result->ua->patch, 0, 65535))
                ->set('os_family', $result->os->family)
                ->set('os_major', (int)$result->os->major)
                ->set('os_minor', (int)$result->os->minor)
                ->set('os_patch', NumberUtil::bound((int)$result->os->patch, 0, 65535))
                ->set('os_patch_minor', NumberUtil::bound((int)$result->os->patchMinor, 0, 65535))
                ->set('dvce_family', $result->device->family);
            $message->set('ctx_ua_parsed', $userAgent);
        } catch (\Throwable $e) {
            $this->logger->warning(
                sprintf('Parsed user agent [%s] could not be added to message.', $result->toString()),
                ['exception' => $e, 'pbj' => $message->toArray()]
            );
        }
    }
}
