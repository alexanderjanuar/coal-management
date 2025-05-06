<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Client extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'email', 'logo'];

    public function projects()
    {
        return $this->hasMany(Project::class);
    }

    public function applications()
    {
        return $this->hasMany(ApplicationClient::class);
    }

    public function taxreports()
    {
        return $this->hasMany(TaxReport::class);
    }

    public function userClients()
    {
        return $this->hasMany(UserClient::class);
    }

    public function clientDocuments(): HasMany
    {
        return $this->hasMany(ClientDocument::class);
    }

    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class);
    }
}
