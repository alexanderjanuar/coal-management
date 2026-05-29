<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Pivot;

class ApplicationClient extends Pivot
{

    use HasFactory;

    protected $table = 'application_clients';

    protected $fillable = [
        'application_id',
        'client_id',
        'username',
        'password',
        'activation_code',
        'account_period',
        'additional_data',
        'notes',
        'last_used_at',
        'is_active',
    ];

    protected $casts = [
        'additional_data' => 'array',
        'account_period'  => 'date',
        'last_used_at'    => 'datetime',
        'is_active'       => 'boolean',
    ];

    public function application()
    {
        return $this->belongsTo(Application::class);
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

}
