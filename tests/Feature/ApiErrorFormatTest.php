<?php

namespace Tests\Feature;

use App\Models\ApiKey;
use App\Models\Invoice;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class ApiErrorFormatTest extends TestCase
{
    use DatabaseTransactions;

    private string $plain;

    protected function setUp(): void
    {
        parent::setUp();
        Route::middleware(['api', 'api.key'])->group(function () {
            Route::post('/api/v1/_validate', fn (Request $request) => $request->validate(['lines.0.rate_usd' => 'required']));
            Route::get('/api/v1/_missing', fn () => Invoice::where('api_id', 'inv_nope')->firstOrFail());
            Route::get('/api/v1/_boom', fn () => throw new \RuntimeException('boom'));
        });
        [, $this->plain] = ApiKey::issue(User::factory()->create(), 'Shop');
    }

    public function test_a_failed_field_names_itself(): void
    {
        $this->withToken($this->plain)->postJson('/api/v1/_validate', ['lines' => [[]]])
            ->assertStatus(422)
            ->assertJsonPath('error.code', 'validation')
            ->assertJsonPath('error.field', 'lines.0.rate_usd')
            ->assertJsonStructure(['error' => ['code', 'message', 'field']]);
    }

    public function test_a_missing_invoice_or_route_is_not_found(): void
    {
        $this->withToken($this->plain)->getJson('/api/v1/_missing')
            ->assertStatus(404)->assertJsonPath('error.code', 'not_found');

        $this->withToken($this->plain)->getJson('/api/v1/nowhere')
            ->assertStatus(404)->assertJsonPath('error.code', 'not_found')->assertJsonPath('error.message', 'No such endpoint.');
    }

    public function test_a_wrong_method_and_a_server_fault_keep_the_format(): void
    {
        $this->withToken($this->plain)->getJson('/api/v1/invoices')
            ->assertStatus(405)->assertJsonPath('error.code', 'method_not_allowed');

        $this->withToken($this->plain)->getJson('/api/v1/_boom')
            ->assertStatus(500)->assertJsonPath('error.code', 'server_error')->assertDontSee('boom');
    }

    public function test_web_errors_keep_their_own_format(): void
    {
        $this->get('/no-such-page')->assertStatus(404)->assertDontSee('not_found');
    }
}
