<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\DatabaseMessage;
use App\Models\ProductColorVariant;

class LowStockProductNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $variant;

    public function __construct(ProductColorVariant $variant)
    {
        $this->variant = $variant;
    }

    /**
     * Notify all admins about a low stock product color variant.
     * You can call this from stock update flows.
     */
    public static function notifyAdmins(ProductColorVariant $variant)
    {
        // You may want to filter only admin users if you have a role system
        $admins = \App\Models\User::all();
        foreach ($admins as $admin) {
            $admin->notify(new self($variant));
        }
    }

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
    $threshold = $this->variant->minimum_threshold ?? 0;
        return (new MailMessage)
            ->subject('Low Stock Alert: ' . $this->variant->product->name)
            ->line('The product "' . $this->variant->product->name . '" (Color: ' . $this->variant->color . ') is below its minimum threshold.')
            ->line('Current Quantity: ' . $this->variant->quantity)
            ->line('Minimum Threshold: ' . $threshold)
            ->action('View Product', url(route('products.show', $this->variant->product)))
            ->line('Please restock soon.');
    }

    public function toArray($notifiable)
    {
        $threshold = $this->variant->minimum_threshold ?? $this->variant->product->minimum_threshold;
        return [
            'product_id' => $this->variant->product->id,
            'product_name' => $this->variant->product->name,
            'color' => $this->variant->color,
            'quantity' => $this->variant->quantity,
            'minimum_threshold' => $threshold,
        ];
    }
}
