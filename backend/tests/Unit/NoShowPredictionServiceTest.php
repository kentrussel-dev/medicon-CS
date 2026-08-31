<?php

namespace Tests\Unit;

use App\Enums\RiskLevel;
use App\Services\NoShowPredictionService;
use Tests\TestCase;

class NoShowPredictionServiceTest extends TestCase
{
    public function test_heuristic_fallback_low_risk(): void
    {
        $service = new NoShowPredictionService();

        $result = $service->computeFallbackHeuristic(
            leadTimeDays: 1,
            priorAppointments: 5,
            priorNoShows: 0,
            smsReceived: 1,
            scholarship: 0,
            dayOfWeek: 1
        );

        $this->assertEquals(RiskLevel::LOW, $result['level']);
        $this->assertFalse($result['is_high_risk']);
        $this->assertLessThan(0.35, $result['score']);
        $this->assertEquals('heuristic_fallback', $result['source']);
    }

    public function test_heuristic_fallback_high_risk(): void
    {
        $service = new NoShowPredictionService();

        $result = $service->computeFallbackHeuristic(
            leadTimeDays: 25,
            priorAppointments: 4,
            priorNoShows: 4, // 100% no show history
            smsReceived: 0,
            scholarship: 1,
            dayOfWeek: 5 // Saturday
        );

        $this->assertEquals(RiskLevel::HIGH, $result['level']);
        $this->assertTrue($result['is_high_risk']);
        $this->assertGreaterThanOrEqual(0.65, $result['score']);
        $this->assertNotEmpty($result['factors']);
    }
}
