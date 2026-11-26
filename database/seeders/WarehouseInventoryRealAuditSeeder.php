<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Factory;
use App\Models\Product;
use App\Models\RawMaterial;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class WarehouseInventoryRealAuditSeeder extends Seeder
{
    public function run(): void
    {
        $jsonPath = base_path('parsed_inventory.json');
        if (! file_exists($jsonPath)) {
            return;
        }

        $items = json_decode(file_get_contents($jsonPath), true);
        $couvertFactory = Factory::where('code', 'FAC-COUVERT')->first() ?? Factory::first();

        $rawIdx = RawMaterial::max('id') ?? 0;
        $prodIdx = Product::max('id') ?? 0;

        foreach ($items as $item) {
            $customer = null;
            if (! empty($item['customer'])) {
                $customer = Customer::where('name', 'like', '%'.$item['customer'].'%')->first();
                if (! $customer) {
                    $customer = Customer::create([
                        'name' => $item['customer'],
                        'code' => 'CUST-'.strtoupper(Str::random(4)),
                        'status' => true,
                    ]);
                }
            }

            if ($item['type'] === 'raw_material') {
                $rawIdx++;
                $code = 'RM-INV-'.str_pad($rawIdx, 3, '0', STR_PAD_LEFT);

                RawMaterial::updateOrCreate(
                    ['name' => $item['name']],
                    [
                        'code' => $code,
                        'category' => $item['category'],
                        'unit' => $item['unit'],
                        'unit_cost' => $item['unit_cost'] ?? 10.00,
                        'current_stock' => $item['stock'],
                        'reorder_level' => $item['reorder_level'] ?? 10.00,
                        'warehouse_location' => $item['location'],
                        'image' => $item['image'],
                        'package_info' => $item['package_info'],
                        'source_factory_id' => $couvertFactory?->id,
                        'status' => true,
                    ]
                );
            } elseif ($item['type'] === 'product') {
                $prodIdx++;
                $code = 'PROD-INV-'.str_pad($prodIdx, 3, '0', STR_PAD_LEFT);
                $costPrice = $item['unit_cost'] ?? 1.25;
                $sellingPrice = round($costPrice * 1.35, 2);

                Product::updateOrCreate(
                    ['name' => $item['name']],
                    [
                        'code' => $code,
                        'category' => 'كوڤير مطاعم متكامل',
                        'type' => 'finished_good',
                        'unit' => $item['unit'],
                        'factory_id' => $couvertFactory?->id,
                        'customer_id' => $customer?->id,
                        'cost_price' => $costPrice,
                        'selling_price' => $sellingPrice,
                        'min_selling_price' => round($costPrice * 1.10, 2),
                        'current_stock' => $item['stock'],
                        'reorder_level' => $item['reorder_level'] ?? 1000.00,
                        'image' => $item['image'],
                        'package_info' => $item['package_info'],
                        'description' => 'صنف تام معتمد من واقع الجرد الفعلي لمخزن الشركة المركزي ('.$item['package_info'].')',
                        'status' => true,
                    ]
                );
            }
        }
    }
}
