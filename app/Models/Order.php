<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Support\Facades\Storage;

class Order extends Model
{
    //

    protected $guarded = [];

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class, 'order_id');
    }

    public function generateTicket($ticket_type_id)
    {
        $ticketCode = $this->generateTicketCodeUnik();
        $qrCode = $this->generateQrCodeSvgBase64($ticketCode);

        $ticketData = [
            'order_id' => $this->id,
            'ticket_type_id' => $ticket_type_id,
            'ticket_code' => $ticketCode,
            'qr_code' => $qrCode,
            'attendee_name' => $this->user->name,
            'attendee_email' => $this->user->email,
            'attendee_phone' => $this->user->phone,
        ];


        return Ticket::create($ticketData);
    }

    public function generateQrCodeSvgBase64(string $ticketCode): string
    {
        $svg = QrCode::format('svg')->size(300)->generate($ticketCode);
        $base64 = base64_encode($svg);

        return 'data:image/svg+xml;base64,' . $base64;
    }




    public function generateTicketCodeUnik(): string
    {
        return 'T' . strtoupper(Str::uuid()->toString());
    }


    public function tickets()
    {
        return $this->hasMany(Ticket::class);
    }
}
