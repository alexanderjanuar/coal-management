<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Application extends Model
{
    use HasFactory;

    public function applicationClients(): HasMany
    {
        return $this->hasMany(ApplicationClient::class);
    }

    public function activeClients(): HasMany
    {
        return $this->hasMany(ApplicationClient::class)
                    ->where('is_active', true);
    }
    
}
