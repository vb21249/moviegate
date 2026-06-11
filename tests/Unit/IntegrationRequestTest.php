<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Modules\Integration\Requests\IntegrationRequest;
use PHPUnit\Framework\TestCase;

final class IntegrationRequestTest extends TestCase
{
    public function testTmdbScenarioRequiresTmdbIdOrQuery(): void
    {
        $request = new IntegrationRequest(['scenario' => IntegrationRequest::SCENARIO_TMDB_SYNC]);
        $request->load(['imdb_id' => 'tt1856101'], '');

        self::assertFalse($request->validate());
        self::assertSame('Either tmdb_id or query is required.', $request->firstErrorMessage());
    }

    public function testOmdbScenarioRequiresImdbIdOrQuery(): void
    {
        $request = new IntegrationRequest(['scenario' => IntegrationRequest::SCENARIO_OMDB_SYNC]);
        $request->load(['tmdb_id' => 335984], '');

        self::assertFalse($request->validate());
        self::assertSame('Either imdb_id or query is required.', $request->firstErrorMessage());
    }

    public function testOmdbScenarioAcceptsImdbId(): void
    {
        $request = new IntegrationRequest(['scenario' => IntegrationRequest::SCENARIO_OMDB_SYNC]);
        $request->load(['imdb_id' => 'TT1856101'], '');

        self::assertTrue($request->validate());
        self::assertSame('tt1856101', $request->imdbId());
    }
}
