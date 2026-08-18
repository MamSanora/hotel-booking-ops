<?php

namespace Database\Seeders;

use App\Models\IncidentalItem;
use Illuminate\Database\Seeder;

/**
 * IncidentalItemsSeeder
 *
 * Seeds the incidental_items table with the hotel's standard list of
 * chargeable damages and penalties for a 2-star boutique hotel in Phnom Penh.
 *
 * Prices reflect typical replacement costs sourced from local Phnom Penh
 * electronics markets (Sovannaphum, Central Market) and hospitality norms.
 *
 * These can be edited by an admin through the admin panel later.
 * This seeder is safe to run multiple times (uses firstOrCreate).
 */
class IncidentalItemsSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            // ── Electronics ──────────────────────────────────────────────────
            [
                'name'           => 'Broken Flat-screen TV',
                'default_amount' => 300.00,
                'charge_policy'  => 'Charge only for physical screen damage (cracked, shattered). Do not charge for software or remote issues.',
            ],
            [
                'name'           => 'Broken/Lost TV Remote',
                'default_amount' => 15.00,
                'charge_policy'  => 'Charge if remote is physically broken or missing after check-out. Do not charge for dead batteries.',
            ],
            [
                'name'           => 'Broken/Lost AC Remote',
                'default_amount' => 15.00,
                'charge_policy'  => 'Charge if remote is physically broken or missing after check-out. Do not charge for dead batteries.',
            ],
            [
                'name'           => 'Broken Hairdryer',
                'default_amount' => 25.00,
                'charge_policy'  => 'Charge only if casing is cracked or unit is missing. Do not charge if motor failed normally.',
            ],
            [
                'name'           => 'Damaged Electric Kettle',
                'default_amount' => 20.00,
                'charge_policy'  => 'Charge for physical damage (dented, cracked lid, burnt base). Do not charge for normal scale buildup.',
            ],

            // ── Glassware & Room Items ────────────────────────────────────────
            [
                'name'           => 'Broken Drinking Glass',
                'default_amount' => 5.00,
                'charge_policy'  => null,
            ],
            [
                'name'           => 'Broken Coffee Mug',
                'default_amount' => 5.00,
                'charge_policy'  => null,
            ],

            // ── Linens & Bedding ──────────────────────────────────────────────
            [
                'name'           => 'Stained/Damaged Bath Towel',
                'default_amount' => 8.00,
                'charge_policy'  => 'Charge for permanent stains (makeup, dye, burn marks) or tears. Normal use stains are not chargeable.',
            ],
            [
                'name'           => 'Stained/Damaged Bed Sheet',
                'default_amount' => 12.00,
                'charge_policy'  => 'Charge for permanent stains or tears. Normal laundering stains are not chargeable.',
            ],
            [
                'name'           => 'Lost/Damaged Pillow',
                'default_amount' => 10.00,
                'charge_policy'  => null,
            ],

            // ── Keys & Security ───────────────────────────────────────────────
            [
                'name'           => 'Lost Room Key',
                'default_amount' => 15.00,
                'charge_policy'  => 'Charge to cover lock cylinder replacement if physical key is not returned at check-out.',
            ],

            // ── Penalties ─────────────────────────────────────────────────────
            [
                'name'           => 'Smoking in Room Penalty',
                'default_amount' => 50.00,
                'charge_policy'  => 'Charge when evidence of smoking is found inside the room (smell, ash, burn marks). Document with photos if possible.',
            ],
            [
                'name'           => 'Late Check-out Fee',
                'default_amount' => 20.00,
                'charge_policy'  => 'Charge per half-day if guest checks out after 12:00 PM without prior approval.',
            ],
        ];

        foreach ($items as $item) {
            IncidentalItem::firstOrCreate(
                ['name' => $item['name']],
                [
                    'default_amount' => $item['default_amount'],
                    'charge_policy'  => $item['charge_policy'],
                    'is_active'      => true,
                ]
            );
        }

        $this->command->info('IncidentalItemsSeeder: ' . count($items) . ' items seeded.');
    }
}
