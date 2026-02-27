<?php

namespace App\Mail;

use App\Models\Sell;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OrderStatusMail extends Mailable
{
    use Queueable, SerializesModels;

    public Sell $sell;
    public string $status;

    public function __construct(Sell $sell, string $status)
    {
        $this->sell = $sell->load(['sellDetail.productInfo', 'orderAddress', 'payment']);
        $this->status = $status; // paid|canceled|failed|pending
    }

    public function build()
    {
        return $this->subject('Mise à jour de votre commande #'.$this->sell->invoice_id)
            ->view('emails.order_status')
            ->with([
                'sell' => $this->sell,
                'status' => $this->status,
            ]);
    }
}


