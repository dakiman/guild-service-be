<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use App\User;

class AuthenticationTest extends TestCase
{
    use DatabaseTransactions;
    private $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = factory(User::class)->create();
    }

    /** @test */
    public function it_will_register_a_user()
    {
        $user = factory(User::class)->make();
        $response = $this->post('api/register', $user->toArray() + ['password' => 'password']);
        $response->assertJsonStructure([
            'access_token',
            'token_type',
            'expires_in'
        ]);
    }

    /** @test */
    public function it_will_log_a_user_in()
    {
        $response = $this->post('api/login', [
            'email' => $this->user->email,
            'password' => 'password'
        ]);

        $response->assertJsonStructure([
            'access_token',
            'token_type',
            'expires_in'
        ]);
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
        $response->assertStatus(401);
    }

    /** @test */
    public function it_allows_user_with_token()
    {
        $responseToken = $this->post('/api/login', [
            'email' => $this->user->email,
            'password' => 'password'
        ]);

        $token =
            $responseToken->getContent();

        $response = $this->get('/api/user', [
            'token' => $token
        ]);

        $response->assertStatus(200);
    }


}
