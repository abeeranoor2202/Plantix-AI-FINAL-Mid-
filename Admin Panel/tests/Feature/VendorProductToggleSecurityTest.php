<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class VendorProductToggleSecurityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        if (! Schema::hasColumns('products', ['is_returnable', 'is_refundable'])) {
            $this->markTestSkipped('Product return/refund flags are not present in current test schema.');
        }
    }

    /** @test */
    public function vendor_can_toggle_flags_for_own_product(): void
    {
        [$vendorUser, $vendor] = $this->createVendorUserAndStore('vendor-a@example.com', 'Vendor A Store');

        $product = Product::create([
            'vendor_id' => $vendor->id,
            'name' => 'Vendor A Product',
            'price' => 100,
            'is_active' => true,
            'is_returnable' => true,
            'is_refundable' => true,
        ]);

        $this->actingAs($vendorUser, 'vendor')
            ->post(route('vendor.products.toggle-active', $product->id))
            ->assertRedirect();

        $this->actingAs($vendorUser, 'vendor')
            ->post(route('vendor.products.toggle-returnable', $product->id))
            ->assertRedirect();

        $this->actingAs($vendorUser, 'vendor')
            ->post(route('vendor.products.toggle-refundable', $product->id))
            ->assertRedirect();

        $product->refresh();

        $this->assertFalse($product->is_active);
        $this->assertFalse($product->is_returnable);
        $this->assertFalse($product->is_refundable);
    }

    /** @test */
    public function vendor_cannot_toggle_another_vendors_product(): void
    {
        [$vendorAUser, $vendorA] = $this->createVendorUserAndStore('vendor-a@example.com', 'Vendor A Store');
        [, $vendorB] = $this->createVendorUserAndStore('vendor-b@example.com', 'Vendor B Store');

        $productB = Product::create([
            'vendor_id' => $vendorB->id,
            'name' => 'Vendor B Product',
            'price' => 150,
            'is_active' => true,
            'is_returnable' => true,
            'is_refundable' => true,
        ]);

        $this->actingAs($vendorAUser, 'vendor')
            ->post(route('vendor.products.toggle-active', $productB->id))
            ->assertNotFound();

        $this->actingAs($vendorAUser, 'vendor')
            ->post(route('vendor.products.toggle-returnable', $productB->id))
            ->assertNotFound();

        $this->actingAs($vendorAUser, 'vendor')
            ->post(route('vendor.products.toggle-refundable', $productB->id))
            ->assertNotFound();

        $productB->refresh();

        $this->assertTrue($productB->is_active);
        $this->assertTrue($productB->is_returnable);
        $this->assertTrue($productB->is_refundable);
    }

    /**
     * @return array{0: User, 1: Vendor}
     */
    private function createVendorUserAndStore(string $email, string $title): array
    {
        $user = User::create([
            'name' => $title . ' Owner',
            'email' => $email,
            'password' => Hash::make('password'),
            'role' => 'vendor',
            'active' => true,
            'email_verified_at' => now(),
        ]);

        $vendor = Vendor::create([
            'author_id' => $user->id,
            'title' => $title,
            'is_active' => true,
            'is_approved' => true,
        ]);

        return [$user, $vendor];
    }
}
