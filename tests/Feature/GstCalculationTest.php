<?php

namespace Tests\Feature;

use App\Services\GstCalculationService;
use App\Support\IndianCurrencyHelper;
use Tests\TestCase;

class GstCalculationTest extends TestCase
{
    public function test_assam_client_calculates_cgst_and_sgst_equally(): void
    {
        $items = [
            [
                'item_name' => 'Dell Desktop',
                'quantity' => 2,
                'unit_rate' => 50000.00,
                'discount_percent' => 0,
                'gst_rate' => 18.00,
            ],
        ];

        // Assam state code = 18
        $result = GstCalculationService::calculate($items, '18');

        $this->assertFalse($result['is_interstate']);
        $this->assertEquals(100000.00, $result['subtotal']);
        $this->assertEquals(100000.00, $result['taxable_amount']);
        $this->assertEquals(9000.00, $result['cgst_amount']); // 9% of 100,000
        $this->assertEquals(9000.00, $result['sgst_amount']); // 9% of 100,000
        $this->assertEquals(0.00, $result['igst_amount']);
        $this->assertEquals(18000.00, $result['total_tax']);
        $this->assertEquals(118000.00, $result['grand_total']);
    }

    public function test_other_state_calculates_igst_only(): void
    {
        $items = [
            [
                'item_name' => 'CCTV Network Setup',
                'quantity' => 1,
                'unit_rate' => 100000.00,
                'discount_percent' => 0,
                'gst_rate' => 18.00,
            ],
        ];

        // Meghalaya state code = 17
        $result = GstCalculationService::calculate($items, '17');

        $this->assertTrue($result['is_interstate']);
        $this->assertEquals(0.00, $result['cgst_amount']);
        $this->assertEquals(0.00, $result['sgst_amount']);
        $this->assertEquals(18000.00, $result['igst_amount']); // 18% of 100,000
        $this->assertEquals(18000.00, $result['total_tax']);
        $this->assertEquals(118000.00, $result['grand_total']);
    }

    public function test_discount_and_mixed_gst_rates(): void
    {
        $items = [
            [
                'item_name' => 'IT Hardware',
                'quantity' => 2,
                'unit_rate' => 20000.00, // 40,000 subtotal
                'discount_percent' => 10.00, // 4,000 discount -> 36,000 taxable
                'gst_rate' => 18.00, // 18% of 36,000 = 6,480
            ],
            [
                'item_name' => 'High-end Server Unit',
                'quantity' => 1,
                'unit_rate' => 100000.00,
                'discount_percent' => 5.00, // 5,000 discount -> 95,000 taxable
                'gst_rate' => 28.00, // 28% of 95,000 = 26,600
            ],
        ];

        $result = GstCalculationService::calculate($items, '18');

        $this->assertEquals(140000.00, $result['subtotal']);
        $this->assertEquals(9000.00, $result['discount_amount']);
        $this->assertEquals(131000.00, $result['taxable_amount']);

        // Total tax = 6,480 + 26,600 = 33,080 -> CGST = 16,540, SGST = 16,540
        $this->assertEquals(16540.00, $result['cgst_amount']);
        $this->assertEquals(16540.00, $result['sgst_amount']);
        $this->assertEquals(0.00, $result['igst_amount']);
        $this->assertEquals(33080.00, $result['total_tax']);
        $this->assertEquals(164080.00, $result['grand_total']);
    }

    public function test_rounding_to_nearest_rupee(): void
    {
        // 3 items @ 333.33 each with 18% tax
        // Subtotal = 999.99, Taxable = 999.99, Tax (18%) = 179.9982 -> Total = 1179.99
        // Rounded = 1180.00, Round off = +0.01
        $items = [
            [
                'item_name' => 'Component',
                'quantity' => 3,
                'unit_rate' => 333.33,
                'discount_percent' => 0,
                'gst_rate' => 18.00,
            ],
        ];

        $result = GstCalculationService::calculate($items, '18');

        $this->assertEquals(1180.00, $result['grand_total']);
        $this->assertNotEquals(0.00, $result['round_off']);
    }

    public function test_amount_in_words_for_specific_required_values(): void
    {
        // 0
        $this->assertEquals('Rupees Zero Only', IndianCurrencyHelper::convertToWords(0));

        // 1,000
        $this->assertEquals('Rupees One Thousand Only', IndianCurrencyHelper::convertToWords(1000));

        // 1,50,000
        $this->assertEquals('Rupees One Lakh Fifty Thousand Only', IndianCurrencyHelper::convertToWords(150000));

        // 12,45,600
        $this->assertEquals('Rupees Twelve Lakh Forty-Five Thousand Six Hundred Only', IndianCurrencyHelper::convertToWords(1245600));
    }
}
