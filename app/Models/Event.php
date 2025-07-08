<?php

namespace App\Models;

use Carbon\Carbon;
use Dom\Attr;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    //

    protected $guarded = [];

    public function organizer()
    {
        return $this->belongsTo(User::class, 'organizer_id');
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function image(): Attribute
    {
        return Attribute::make(
            get: fn($value) => asset('storage/' . $value),
        );
    }

    public function gallery(): Attribute
    {
        return Attribute::make(
            get: fn($value) => collect(json_decode($value))
                ->map(fn($image) => asset('storage/' . $image))
                ->toArray(),
        );
    }

    public function ticketTypes()
    {
        return $this->hasMany(TicketType::class);
    }

    public function isSaleActive(): bool
    {
        $now = now();

        return $this->start_datetime >= $now
            && $this->end_datetime >= $now
            && $this->status === 'published';
    }


    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function tags(): Attribute
    {
        return Attribute::make(
            get: fn($value) => collect(
                // Cek jika nilai kosong atau 'null' (jika disimpan sebagai string)
                in_array(strtolower((string)$value), [null, '', 'null'], true)
                    ? []
                    : json_decode($value, true) // <--- UBAH INI: gunakan json_decode()
            )
                ->map(fn($tag) => trim($tag)) // Mungkin masih berguna untuk membersihkan spasi
                ->filter() // Hapus elemen kosong setelah trim
                ->values() // Re-index array
                ->toArray(),
            set: fn($value) => is_array($value) ? json_encode($value) : $value // <--- UBAH INI: pastikan selalu json_encode jika array
        );
    }
}
