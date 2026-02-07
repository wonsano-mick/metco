<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Application\Services\AuthService;
use App\Infrastructure\Services\AuthenticationService;
use App\Domain\Repositories\UserRepositoryInterface;

class ServiceBindingTest extends TestCase
{
    public function test_service_bindings_work(): void
    {
        $this->assertTrue($this->app->bound(AuthService::class));
        $this->assertTrue($this->app->bound(AuthenticationService::class));
        $this->assertTrue($this->app->bound(UserRepositoryInterface::class));
    }
}
