<?php

namespace App\Models;

use App\Events\OrderChangeStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'is_guest',
        'code',
        'user_name',
        'user_email',
        'cancel_reason',
        'other_reason',
        'canceled_by',
        'user_phone',
        'user_address',
        'user_note',
        'is_ship_user_same_user',
        'ship_user_name',
        'ship_user_email',
        'ship_user_phone',
        'ship_user_address',
        'shipping_province',
        'shipping_district',
        'shipping_ward',
        'ship_user_note',
        'status_order_id',
        'status_payment_id',
        'payment_method_id',
        'total_price',
        'voucher_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function statusOrder()
    {
        return $this->belongsTo(StatusOrder::class, 'status_order_id');
    }

    public function statusPayment()
    {
        return $this->belongsTo(StatusPayment::class);
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function paymentMethod()
    {
        return $this->belongsTo(PaymentMethod::class);
    }

    public function voucher()
    {
        return $this->belongsTo(Voucher::class);
    }
    protected static function boot()
    {
        parent::boot();

        static::updated(function ($order) {
            if ($order->isDirty('status_order_id')) {
                broadcast(new OrderChangeStatus($order->id, $order->status_order_id));
            }
        });
    }
}
