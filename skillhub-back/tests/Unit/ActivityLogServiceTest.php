<?php

namespace Tests\Unit;

use App\Services\ActivityLogService;
use Tests\TestCase;

class ActivityLogServiceTest extends TestCase
{
    private ActivityLogService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new ActivityLogService();
    }

    public function test_is_available_retourne_bool(): void
    {
        $this->assertIsBool($this->service->isAvailable());
    }

    public function test_log_event_retourne_false_si_db_indisponible(): void
    {
        // En test, MongoDB n'est pas dispo donc db = null
        $result = $this->service->logEvent('test_event', ['foo' => 'bar']);
        $this->assertFalse($result);
    }

    public function test_log_event_retourne_false_si_event_vide(): void
    {
        $result = $this->service->logEvent('');
        $this->assertFalse($result);
    }

    public function test_log_retourne_false_si_db_indisponible(): void
    {
        $result = $this->service->log(1, 'apprenant', 'click', []);
        $this->assertFalse($result);
    }

    public function test_log_retourne_false_si_action_vide(): void
    {
        $result = $this->service->log(1, 'apprenant', '');
        $this->assertFalse($result);
    }

    public function test_get_by_user_retourne_tableau_vide_si_db_indisponible(): void
    {
        $result = $this->service->getByUser(1);
        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }
}