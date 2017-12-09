<?php
declare(strict_types=1);

namespace Gdbots\Tests\Enrichments;

use Gdbots\Enrichments\UaParserEnricher;
use Gdbots\Pbjx\Event\PbjxEvent;
use Gdbots\Schemas\Contexts\UserAgent;
use PHPUnit\Framework\TestCase;

class UaParserEnricherTest extends TestCase
{
    public function testEnrich()
    {
        $command = Fixtures\FakeCommand::create();
        $command->set('ctx_ua', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.9; rv:45.0) Gecko/20100101 Firefox/45.0');
        $enricher = new UaParserEnricher();
        $pbjxEvent = new PbjxEvent($command);

        $enricher->enrich($pbjxEvent);
        $userAgent = $command->get('ctx_ua_parsed');

        $this->assertInstanceOf(UserAgent::class, $userAgent);
        $this->assertSame('Firefox', $userAgent->get('br_family'));
        $this->assertSame(45, $userAgent->get('br_major'));
        $this->assertSame(0, $userAgent->get('br_minor'));
        $this->assertSame(0, $userAgent->get('br_patch'));
        $this->assertSame('Mac OS X', $userAgent->get('os_family'));
        $this->assertSame(10, $userAgent->get('os_major'));
        $this->assertSame(9, $userAgent->get('os_minor'));
        $this->assertSame(0, $userAgent->get('os_patch'));
        $this->assertSame(0, $userAgent->get('os_patch_minor'));
        $this->assertSame('Other', $userAgent->get('dvce_family'));
    }
}
