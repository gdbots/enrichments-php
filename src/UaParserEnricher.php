<?php
declare(strict_types=1);

namespace Gdbots\Enrichments;

use Gdbots\Pbjx\Event\PbjxEvent;
use Gdbots\Pbjx\EventSubscriber;
use Gdbots\Schemas\Contexts\UserAgentV1;
use Gdbots\Schemas\Enrichments\Mixin\UaParser\UaParser;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use UAParser\Parser;

final class UaParserEnricher implements EventSubscriber
{
    /** @var LoggerInterface $logger */
    private $logger;

    /** @var Parser $parser */
    private $parser;

    /**
     * @param LoggerInterface $logger
     */
    public function __construct(?LoggerInterface $logger = null)
    {
        $this->logger = $logger ?: new NullLogger();
        $this->parser = Parser::create();
    }

    /**
     * @param PbjxEvent $pbjxEvent
     */
    public function enrich(PbjxEvent $pbjxEvent): void
    {
        /** @var UaParser $message */
        $message = $pbjxEvent->getMessage();
        if (!$message->has('ctx_ua') || $message->has('ctx_ua_parsed')) {
            return;
        }

        try {
            $result = $this->parser->parse($message->get('ctx_ua'));
        } catch (\Exception $e) {
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
                ->set('br_patch', (int)$result->ua->patch)
                ->set('os_family', $result->os->family)
                ->set('os_major', (int)$result->os->major)
                ->set('os_minor', (int)$result->os->minor)
                ->set('os_patch', (int)$result->os->patch)
                ->set('os_patch_minor', (int)$result->os->patchMinor)
                ->set('dvce_family', $result->device->family);
            $message->set('ctx_ua_parsed', $userAgent);
        } catch (\Exception $e) {
            $this->logger->warning(
                sprintf('Parsed user agent [%s] could not be added to message.', $result->toString()),
                ['exception' => $e, 'pbj' => $message->toArray()]
            );
        }
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            'gdbots:enrichments:mixin:ua-parser.enrich' => [['enrich', 5000]],
        ];
    }
}
