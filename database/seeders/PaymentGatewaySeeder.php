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
                'slug'         => 'khqr_aba',
                'name'         => 'KHQR and ABA Pay',
                'admin_status' => PaymentGateway::STATUS_ACTIVE,
            ],
            [
                'slug'         => 'bakong',
                'name'         => 'Bakong (KHQR)',
                'admin_status' => PaymentGateway::STATUS_ACTIVE,
            ],
            [
                'slug'         => 'aba_payway',
                'name'         => 'ABA PayWay',
                'admin_status' => PaymentGateway::STATUS_DISABLED,
            ],
            [
                'slug'         => 'aba_telegram',
                'name'         => 'ABA Transfer (Telegram)',
                'admin_status' => PaymentGateway::STATUS_DISABLED,
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
