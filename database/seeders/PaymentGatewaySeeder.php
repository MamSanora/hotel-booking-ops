<?php

namespace Database\Seeders;

use App\Models\PaymentGateway;
use Illuminate\Database\Seeder;

class PaymentGatewaySeeder extends Seeder
{
    public function run(): void
    {
        $gateways = [
            [
                'slug'         => 'bakong',
                'name'         => 'Bakong (KHQR)',
                'admin_status' => PaymentGateway::STATUS_ACTIVE,
            ],
            [
                'slug'         => 'aba_payway',
                'name'         => 'ABA PayWay',
                'admin_status' => PaymentGateway::STATUS_ACTIVE,
            ],
        ];

        foreach ($gateways as $data) {
            PaymentGateway::updateOrCreate(
                ['slug' => $data['slug']],
                $data
            );
        }
    }
}
