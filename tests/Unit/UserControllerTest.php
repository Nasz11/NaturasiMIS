<?php
namespace Tests\Unit;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
class UserControllerTest extends TestCase
{
    use RefreshDatabase;
    protected $admin;
    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => 'admin']);
    }
    #[\PHPUnit\Framework\Attributes\Test]
    public function it_creates_a_new_user()
    {
        $response = $this->actingAs($this->admin)->post('/users', [
            'username' => 'newuser',
            'email'    => 'newuser@email.com',
            'role'     => 'inventory',
            'password' => 'Password1@',
            'status'   => 'Active',
        ]);
        $response->assertStatus(302);
        $this->assertDatabaseHas('users', ['username' => 'newuser']);
    }
}