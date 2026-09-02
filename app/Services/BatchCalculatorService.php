<?php

namespace App\Services;

class BatchCalculatorService
{
    /**
     * Standard artisanal batch yield sizes (in kilograms).
     */
    public const BATCH_SIZES = [
        1.25, 2.50, 3.75, 5.00, 6.25, 7.50, 8.75, 10.00,
        11.25, 12.50, 13.75, 15.00, 16.25, 17.50,
        18.75, 20.00, 21.25, 22.50
    ];

    /**
     * Normalizes a requested weight (kg) to the closest valid standard batch size <= 22.5 kg.
     */
    public function normalizeToBatchSize(float $kg): float
    {
        if ($kg <= 22.5) {
            foreach (self::BATCH_SIZES as $size) {
                if ($kg <= $size + 0.0001) {
                    return $size;
                }
            }
        }

        return $kg;
    }

    /**
     * Splits a total yield into the fewest number of standard artisanal batch sizes.
     */
    public function splitIntoBatches(float $totalYield): array
    {
        $batchSizes = array_reverse(self::BATCH_SIZES);
        $batches = [];

        while ($totalYield > 0.0001) {
            $matched = false;
            foreach ($batchSizes as $size) {
                if ($totalYield >= $size - 0.0001) {
                    $batches[] = $size;
                    $totalYield -= $size;
                    $matched = true;
                    break;
                }
            }
            if (!$matched) {
                break;
            }
        }

        return $batches;
    }

    /**
     * Milk required (in liters) for a given batch yield in kilograms.
     */
    public function getMilkRequired(float $yieldKg): int
    {
        $map = [
            '1.25'  => 0,
            '2.50'  => 0,
            '3.75'  => 0,
            '5.00'  => 1,
            '6.25'  => 1,
            '7.50'  => 1,
            '8.75'  => 1,
            '10.00' => 1,
            '11.25' => 1,
            '12.50' => 1,
            '13.75' => 2,
            '15.00' => 2,
            '16.25' => 2,
            '17.50' => 2,
            '18.75' => 2,
            '20.00' => 2,
            '21.25' => 2,
            '22.50' => 3,
        ];

        $key = number_format($yieldKg, 2, '.', '');

        return $map[$key] ?? 0;
    }

    /**
     * Cream required (in pieces/cups) for a given batch yield in kilograms.
     */
    public function getCreamRequired(float $yieldKg): int
    {
        $map = [
            '1.25'  => 3,  '2.50'  => 6,  '3.75'  => 5,
            '5.00'  => 8,  '6.25'  => 11, '7.50'  => 14,
            '8.75'  => 17, '10.00' => 20, '11.25' => 23,
            '12.50' => 26, '13.75' => 25, '15.00' => 28,
            '16.25' => 31, '17.50' => 34, '18.75' => 37,
            '20.00' => 40, '21.25' => 43, '22.50' => 42,
        ];

        $key = number_format($yieldKg, 2, '.', '');

        return $map[$key] ?? 0;
    }

    /**
     * Iodized salt required (in scoops) for a given batch yield in kilograms.
     */
    public function getSaltRequired(float $yieldKg): float
    {
        $map = [
            '1.25'  => 1.0,   '2.50'  => 2.5,   '3.75'  => 3.25,
            '5.00'  => 4.0,   '6.25'  => 4.75,  '7.50'  => 5.5,
            '8.75'  => 6.25,  '10.00' => 7.0,   '11.25' => 7.75,
            '12.50' => 8.5,   '13.75' => 9.25,  '15.00' => 10.5,
            '16.25' => 11.0,  '17.50' => 12.0,  '18.75' => 13.0,
            '20.00' => 13.5,  '21.25' => 14.25, '22.50' => 15.5,
        ];

        $key = number_format($yieldKg, 2, '.', '');

        return $map[$key] ?? 0.0;
    }

    /**
     * Computes production ingredient totals for batch-processed cheeses.
     */
    public function computeProductionTotals(float $totalKg, string $product): array
    {
        $totalCream = 0;
        $totalMilk  = 0;
        $totalSalt  = 0;

        if (!in_array($product, ['Burrata', 'Stracciatella'])) {
            return compact('totalCream', 'totalMilk', 'totalSalt');
        }

        // Normalize total to valid 1.25kg multiple first, then split
        $normalizedTotal = ceil($totalKg / 1.25) * 1.25;
        $batches         = $this->splitIntoBatches($normalizedTotal);

        foreach ($batches as $batch) {
            $totalCream += $this->getCreamRequired($batch);
            $totalMilk  += $this->getMilkRequired($batch);
            $totalSalt  += $this->getSaltRequired($batch);
        }

        return compact('totalCream', 'totalMilk', 'totalSalt');
    }
}
