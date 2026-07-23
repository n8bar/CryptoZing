<?php

namespace Tests\Feature;

use Tests\TestCase;

class HelpPageTest extends TestCase
{
    public function test_help_page_documents_the_two_factor_recovery_story(): void
    {
        $response = $this->get(route('help'));

        $response->assertOk();
        $response->assertSee('your email account is your recovery path', false);
        $response->assertSee('account recreation will be required', false);
    }
}
