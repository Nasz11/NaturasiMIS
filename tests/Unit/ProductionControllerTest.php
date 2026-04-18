<?php
namespace Tests\Unit;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
class ProductionControllerTest extends TestCase
{
    use RefreshDatabase;
    protected $admin;
    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => 'admin']);
    }
    #[\PHPUnit\Framework\Attributes\Test]
    public function it_creates_a_new_production_batch()
    {
        $response = $this->actingAs($this->admin)->post('/production', [
            'batch_number'    => 'B-2026-001',
            'product_type'    => 'Cheddar',
            'quantity'        => 50,
            'production_date' => '2026-04-18',
            'status'          => 'In Production',
            'staff_id'        => null,
        ]);
        $response->assertStatus(302);
        $this->assertDatabaseHas('production_batches', [
            'batch_number' => 'B-2026-001',
            'product_type' => 'Cheddar',
            'status'       => 'In Production',
        ]);
    }
}