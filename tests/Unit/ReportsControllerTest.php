<?php
namespace Tests\Unit;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\InventoryItem;
use App\Models\ProductionBatch;
use App\Models\Order;
use App\Models\ActivityLog;
class ReportsControllerTest extends TestCase
{
    use RefreshDatabase;
    protected $admin;
    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => 'admin']);
    }
    #[\PHPUnit\Framework\Attributes\Test]
    public function it_filters_report_by_inventory_type()
    {
        InventoryItem::factory()->create(['category' => 'Raw Materials', 'is_archived' => false]);
        $response = $this->actingAs($this->admin)->get('/reports?report_type=inventory');
        $response->assertStatus(200);
        $response->assertViewHas('reportData');
    }
    #[\PHPUnit\Framework\Attributes\Test]
    public function it_filters_report_by_production_type()
    {
        ProductionBatch::factory()->create();
        $response = $this->actingAs($this->admin)->get('/reports?report_type=production');
        $response->assertStatus(200);
        $response->assertViewHas('reportData');
    }
    #[\PHPUnit\Framework\Attributes\Test]
    public function it_filters_report_by_orders_type()
    {
        Order::factory()->create();
        $response = $this->actingAs($this->admin)->get('/reports?report_type=orders');
        $response->assertStatus(200);
        $response->assertViewHas('reportData');
    }
    #[\PHPUnit\Framework\Attributes\Test]
    public function it_filters_report_by_activity_type()
    {
        ActivityLog::factory()->create();
        $response = $this->actingAs($this->admin)->get('/reports?report_type=activity');
        $response->assertStatus(200);
        $response->assertViewHas('reportData');
    }
}
