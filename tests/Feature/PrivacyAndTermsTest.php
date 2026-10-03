<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PrivacyAndTermsTest extends TestCase
{
    use RefreshDatabase;
    public function test_privacy_policy_page_loads_successfully(): void
    {
        $response = $this->get('/privacy');
        $response->assertStatus(200);
        $response->assertSee('Privacy Policy');
        $response->assertSee('Google API Services User Data Policy');
        $response->assertSee('paddlefieldsports@gmail.com');

        // Test alias
        $aliasResponse = $this->get('/privacy-policy');
        $aliasResponse->assertStatus(200);
    }

    public function test_terms_of_service_page_loads_successfully(): void
    {
        $response = $this->get('/terms');
        $response->assertStatus(200);
        $response->assertSee('Terms of Service');
        $response->assertSee('Reservations & Real-Time Slot Holds', false);
        $response->assertSee('paddlefieldsports@gmail.com');

        // Test alias
        $aliasResponse = $this->get('/terms-of-service');
        $aliasResponse->assertStatus(200);
    }

    public function test_footer_contains_links_to_privacy_and_terms(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);
        $response->assertSee(route('privacy'));
        $response->assertSee(route('terms'));
    }
}
