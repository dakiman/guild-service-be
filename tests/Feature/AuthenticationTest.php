<?php

namespace Tests\Feature;

use App\Notifications\CustomResetPassword;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;
use App\User;

class AuthenticationTest extends TestCase
{
    use DatabaseTransactions, WithFaker;
    private $user;
    private $tokenJsonStructure;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = factory(User::class)->create();
        $this->tokenJsonStructure = [
            'accessToken',
            'tokenType',
            'expiresIn'
        ];
    }

    /** @test */
    public function it_will_register_a_user()
    {
        $user = factory(User::class)->make();
        $response = $this->post('api/register', $user->toArray() + ['password' => 'password']);
        $response->assertJsonStructure($this->tokenJsonStructure);
    }

    /** @test */
    public function it_will_log_a_user_in()
    {
        $response = $this->post('api/login', [
            'email' => $this->user->email,
            'password' => 'password'
        ]);

        $response->assertJsonStructure($this->tokenJsonStructure);
    }

    /** @test */
    public function it_will_not_log_an_invalid_user_in()
    {
        $response = $this->post('api/login', [
            'email' => $this->user->email,
            'password' => 'notlegitpassword'
        ]);

        $response->assertJsonStructure([
            'error',
        ]);
    }

    /** @test */
    public function it_wont_allow_user_without_token()
    {
        $response = $this->get('/api/user');
        $response
            ->assertJson(['error' => 'You are not authenticated to access this resource.'])
            ->assertStatus(401);
    }

    /** @test */
    public function it_allows_user_with_token()
    {
        $responseToken = $this->post('/api/login', [
            'email' => $this->user->email,
            'password' => 'password'
        ]);

        $token = $responseToken->getContent();

        $response = $this
            ->get('/api/user', ['Authorization' => 'Bearer ' . $token]);

        $response
            ->assertJson(['user' => $this->user->toArray()])
            ->assertStatus(200);
    }

    /** @test */
    public function it_sends_reset_password_email()
    {
        $user = factory(User::class)->create();
        $this->expectsNotification($user, CustomResetPassword::class);

        $response = $this
            ->post('/api/password/forgot', ['email' => $user->email]);

        $response
            ->assertStatus(200)
            ->assertJson(['message' => 'Reset link sent to your email.']);
    }

    /** @test */
    public function it_doesnt_send_link_for_invalid_user()
    {
        $email = $this->faker->safeEmail;

        $response = $this
            ->post('/api/password/forgot', ['email' => $email]);

        $response
            ->assertStatus(400)
            ->assertJson(['message' => 'Unable to send reset link']);
    }

    /** @test */
    public function it_resets_passwords()
    {
        Notification::fake();
        $user = factory(User::class)->create();

        $this->post('api/password/forgot', ['email' => $user->email])
            ->assertStatus(200);

        $token = '';

        Notification::assertSentTo($user, CustomResetPassword::class,
            function ($notification) use (&$token) {
                $token = $notification->token;
                return true;
            }
        );

        $response = $this->post('/api/password/reset', [
            'email' => $user->email,
            'token' => $token,
            'password' => 'password',
            'password_confirmation' => 'password'
        ]);

        $response
            ->assertJsonStructure(['status'])
            ->assertStatus(200);
    }


}
