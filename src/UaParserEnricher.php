<?php
declare(strict_types=1);

namespace Gdbots\Enrichments;

use Gdbots\Pbj\Util\NumberUtil;
use Gdbots\Pbjx\DependencyInjection\PbjxEnricher;
use Gdbots\Pbjx\Event\PbjxEvent;
use Gdbots\Pbjx\EventSubscriber;
use Gdbots\Schemas\Contexts\UserAgentV1;
use Gdbots\Schemas\Enrichments\Mixin\UaParser\UaParserV1Mixin;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use UAParser\Parser;

final class UaParserEnricher implements EventSubscriber, PbjxEnricher
{
    private LoggerInterface $logger;
    private Parser $parser;

    public static function getSubscribedEvents()
    {
        return [
            UaParserV1Mixin::SCHEMA_CURIE . '.enrich' => ['enrich', 5000],
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
        if (!$message->has(UaParserV1Mixin::CTX_UA_FIELD)
            || $message->has(UaParserV1Mixin::CTX_UA_PARSED_FIELD)
        ) {
            return;
        }

        try {
            $result = $this->parser->parse($message->get(UaParserV1Mixin::CTX_UA_FIELD));
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
                ->set(UserAgentV1::BR_FAMILY_FIELD, $result->ua->family)
                ->set(UserAgentV1::BR_MAJOR_FIELD, (int)$result->ua->major)
                ->set(UserAgentV1::BR_MINOR_FIELD, (int)$result->ua->minor)
                ->set(UserAgentV1::BR_PATCH_FIELD, NumberUtil::bound((int)$result->ua->patch, 0, 65535))
                ->set(UserAgentV1::OS_FAMILY_FIELD, $result->os->family)
                ->set(UserAgentV1::OS_MAJOR_FIELD, (int)$result->os->major)
                ->set(UserAgentV1::OS_MINOR_FIELD, (int)$result->os->minor)
                ->set(UserAgentV1::OS_PATCH_FIELD, NumberUtil::bound((int)$result->os->patch, 0, 65535))
                ->set(UserAgentV1::OS_PATCH_MINOR_FIELD, NumberUtil::bound((int)$result->os->patchMinor, 0, 65535))
                ->set(UserAgentV1::DVCE_FAMILY_FIELD, $result->device->family);
            $message->set(UaParserV1Mixin::CTX_UA_PARSED_FIELD, $userAgent);
        } catch (\Throwable $e) {
            $this->logger->warning(
                sprintf('Parsed user agent [%s] could not be added to message.', $result->toString()),
                ['exception' => $e, 'pbj' => $message->toArray()]
            );
        }
    }
}
