<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use App\User;

class AuthenticationTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        $user = new User([
            'name' => 'daki',
            'email'    => 'test@email.com',
            'password' => '12345678'
        ]);

        $user->save();
    }

    /** @test */
    public function it_will_register_a_user()
    {
        $response = $this->post('api/register', [
            'name' => 'new name',
            'email'    => 'test2@email.com',
            'password' => '12345678'
        ]);

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
            'email'    => 'test@email.com',
            'password' => '12345678'
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
            'email'    => 'test@email.com',
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
            'email' => 'test@email.com',
            'password' => '12345678'
        ]);

        $token =
//            $responseToken->getContent();
            'trash';

        $response = $this->get('/api/user', [
            'token' => $token
        ]);

        $response->assertStatus(200);
    }



}
