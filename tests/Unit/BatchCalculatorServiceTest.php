<?php

namespace Tests\Unit;

use App\Services\BatchCalculatorService;
use PHPUnit\Framework\TestCase;

class BatchCalculatorServiceTest extends TestCase
{
    protected BatchCalculatorService $calculator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->calculator = new BatchCalculatorService();
    }

    public function test_normalizes_to_standard_batch_sizes(): void
    {
        // 1.0 kg should round up to the minimum standard size 1.25 kg
        $this->assertEquals(1.25, $this->calculator->normalizeToBatchSize(1.0));

        // Exact match remains untouched
        $this->assertEquals(2.5, $this->calculator->normalizeToBatchSize(2.5));

        // 6.0 kg should normalize to 6.25 kg
        $this->assertEquals(6.25, $this->calculator->normalizeToBatchSize(6.0));

        // 22.5 kg is maximum standard batch size
        $this->assertEquals(22.5, $this->calculator->normalizeToBatchSize(22.5));

        // Quantities above 22.5 are handled by multi-batch splitting
        $this->assertEquals(35.0, $this->calculator->normalizeToBatchSize(35.0));
    }

    public function test_splits_large_orders_into_valid_batches(): void
    {
        // 22.5 kg yields 1 full batch of 22.5 kg
        $batches = $this->calculator->splitIntoBatches(22.5);
        $this->assertEquals([22.5], $batches);

        // 35.0 kg splits into 22.5 kg + 12.5 kg
        $batches = $this->calculator->splitIntoBatches(35.0);
        $this->assertEquals([22.5, 12.5], $batches);
        $this->assertEquals(35.0, array_sum($batches));

        // 45.0 kg splits into 22.5 kg + 22.5 kg
        $batches = $this->calculator->splitIntoBatches(45.0);
        $this->assertEquals([22.5, 22.5], $batches);
        $this->assertEquals(45.0, array_sum($batches));
    }

    public function test_milk_required_lookup(): void
    {
        $this->assertEquals(0, $this->calculator->getMilkRequired(1.25));
        $this->assertEquals(1, $this->calculator->getMilkRequired(5.00));
        $this->assertEquals(2, $this->calculator->getMilkRequired(15.00));
        $this->assertEquals(3, $this->calculator->getMilkRequired(22.50));
    }

    public function test_cream_and_salt_required_lookup(): void
    {
        // 5.0 kg batch
        $this->assertEquals(8, $this->calculator->getCreamRequired(5.0));
        $this->assertEquals(4.0, $this->calculator->getSaltRequired(5.0));

        // 22.5 kg batch
        $this->assertEquals(42, $this->calculator->getCreamRequired(22.5));
        $this->assertEquals(15.5, $this->calculator->getSaltRequired(22.5));
    }

    public function test_computes_production_totals_for_burrata(): void
    {
        $totals = $this->calculator->computeProductionTotals(22.5, 'Burrata');

        $this->assertEquals(42, $totals['totalCream']);
        $this->assertEquals(3, $totals['totalMilk']);
        $this->assertEquals(15.5, $totals['totalSalt']);
    }

    public function test_returns_zero_totals_for_non_batch_cheeses(): void
    {
        $totals = $this->calculator->computeProductionTotals(20.0, 'Mozzarella');

        $this->assertEquals(0, $totals['totalCream']);
        $this->assertEquals(0, $totals['totalMilk']);
        $this->assertEquals(0, $totals['totalSalt']);
    }
}
