<?php
declare(strict_types=1);

namespace Gdbots\Tests\Enrichments\Fixtures;

use Gdbots\Pbj\AbstractMessage;
use Gdbots\Pbj\MessageResolver;
use Gdbots\Pbj\Schema;
use Gdbots\Schemas\Enrichments\Mixin\TimeParting\TimePartingV1Mixin;
use Gdbots\Schemas\Enrichments\Mixin\TimeSampling\TimeSamplingV1Mixin;
use Gdbots\Schemas\Enrichments\Mixin\UaParser\UaParserV1Mixin;
use Gdbots\Schemas\Pbjx\Mixin\Command\CommandV1Mixin;
use Gdbots\Schemas\Pbjx\Mixin\Command\CommandV1Trait;

final class FakeCommand extends AbstractMessage
{
    use CommandV1Trait;

    protected static function defineSchema(): Schema
    {
        $fields = array_merge(
            CommandV1Mixin::getFields(),
            TimePartingV1Mixin::getFields(),
            TimeSamplingV1Mixin::getFields(),
            UaParserV1Mixin::getFields(),
        );
        $schema = new Schema('pbj:gdbots:tests.enrichments:fixtures:fake-command:1-0-0', __CLASS__, $fields);

        MessageResolver::registerSchema($schema);
        return $schema;
    }
}
