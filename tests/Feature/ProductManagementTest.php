<?php

namespace Tests\Feature;

use App\Enums\ProductCategory;
use App\Enums\UserRole;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_products_seeded_properly(): void
    {
        $this->assertEquals(25, Product::count());
        $this->assertTrue(Product::where('category', ProductCategory::Hardware)->exists());
        $this->assertTrue(Product::where('category', ProductCategory::CctvSecurity)->exists());
        $this->assertTrue(Product::where('category', ProductCategory::Networking)->exists());
        $this->assertTrue(Product::where('category', ProductCategory::Software)->exists());
        $this->assertTrue(Product::where('category', ProductCategory::Service)->exists());
    }

    public function test_admin_can_manage_products(): void
    {
        $admin = User::where('role', UserRole::Admin)->first();
        $product = Product::first();

        $this->assertTrue($admin->can('create', Product::class));
        $this->assertTrue($admin->can('update', $product));
        $this->assertTrue($admin->can('delete', $product));
    }

    public function test_sales_executive_can_view_but_not_manage_products(): void
    {
        $rahul = User::where('email', 'rahul@marutisales.in')->first();
        $product = Product::first();

        $this->assertTrue($rahul->can('viewAny', Product::class));
        $this->assertTrue($rahul->can('view', $product));
        $this->assertFalse($rahul->can('create', Product::class));
        $this->assertFalse($rahul->can('update', $product));
        $this->assertFalse($rahul->can('delete', $product));
    }
}
