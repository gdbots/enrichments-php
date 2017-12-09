<?php

namespace Gdbots\Tests\Enrichments\Fixtures;

use Gdbots\Pbj\AbstractMessage;
use Gdbots\Pbj\MessageResolver;
use Gdbots\Pbj\Schema;
use Gdbots\Schemas\Enrichments\Mixin\TimeParting\TimePartingV1;
use Gdbots\Schemas\Enrichments\Mixin\TimeParting\TimePartingV1Mixin;
use Gdbots\Schemas\Enrichments\Mixin\TimeSampling\TimeSamplingV1;
use Gdbots\Schemas\Enrichments\Mixin\TimeSampling\TimeSamplingV1Mixin;
use Gdbots\Schemas\Enrichments\Mixin\UaParser\UaParserV1;
use Gdbots\Schemas\Enrichments\Mixin\UaParser\UaParserV1Mixin;
use Gdbots\Schemas\Pbjx\Mixin\Command\CommandV1;
use Gdbots\Schemas\Pbjx\Mixin\Command\CommandV1Mixin;
use Gdbots\Schemas\Pbjx\Mixin\Command\CommandV1Trait;

final class FakeCommand extends AbstractMessage implements
    CommandV1,
    TimePartingV1,
    TimeSamplingV1,
    UaParserV1
{
    use CommandV1Trait;

    /**
     * @return Schema
     */
    protected static function defineSchema()
    {
        $schema = new Schema('pbj:gdbots:tests.enrichments:fixtures:fake-command:1-0-0', __CLASS__, [],
            [
                CommandV1Mixin::create(),
                TimePartingV1Mixin::create(),
                TimeSamplingV1Mixin::create(),
                UaParserV1Mixin::create(),
            ]
        );

        MessageResolver::registerSchema($schema);
        return $schema;
    }
}
