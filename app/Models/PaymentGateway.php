<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * PaymentGateway Model
 *
 * Represents an admin-configurable payment gateway entry.
 * The admin_status column is the manual control knob.
 * The PaymentGatewayManager further overrides the effective state
 * based on live credential and API health checks.
 *
 * @property int    $id
 * @property string $slug          e.g. 'bakong' | 'aba_payway'
 * @property string $name          e.g. 'Bakong Open API' | 'ABA PayWay'
 * @property string $admin_status  'active' | 'disabled' | 'hidden'
 */
class PaymentGateway extends Model
{
    public const STATUS_ACTIVE   = 'active';
    public const STATUS_DISABLED = 'disabled';
    public const STATUS_HIDDEN   = 'hidden';

    protected $fillable = [
        'slug',
        'name',
        'admin_status',
    ];
}
