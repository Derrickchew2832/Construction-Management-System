<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile; // Import UploadedFile class
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
    }

    public function test_new_users_can_register(): void
    {
        $file = UploadedFile::fake()->create('document.pdf'); // Use UploadedFile class

        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'phone' => '123456789', // Add a valid phone number for validation
            'role' => 'client', // Add a role selection
            'document' => $file, // Use the created fake file
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard', [], false)); // Ensure route is correctly specified
    }
}
