<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    protected $table = 'user';
    protected $primaryKey = 'user_id';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;
    
    // Disable remember token functionality
    protected $rememberTokenName = null;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'user_name',
        'user_password',
        'user_kat',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'user_password',
    ];

    /**
     * Get the password for the user.
     *
     * @return string
     */
    public function getAuthPassword()
    {
        return $this->user_password;
    }

    /**
     * Get the name of the unique identifier for the user.
     *
     * @return string
     */
    public function getAuthIdentifierName()
    {
        return 'user_id';
    }

    /**
     * Get the password attribute (maps to user_password).
     */
    public function getPasswordAttribute()
    {
        return $this->user_password;
    }

    /**
     * Set the password attribute (maps to user_password).
     */
    public function setPasswordAttribute($value)
    {
        $this->attributes['user_password'] = $value;
    }

    /**
     * Get the name attribute (maps to user_name).
     */
    public function getNameAttribute()
    {
        return $this->user_name;
    }

    /**
     * Set the name attribute (maps to user_name).
     */
    public function setNameAttribute($value)
    {
        $this->attributes['user_name'] = $value;
    }

    /**
     * Get the email attribute (maps to user_id for display purposes).
     */
    public function getEmailAttribute()
    {
        return $this->user_id;
    }

    /**
     * Get the token value for the "remember me" session.
     *
     * @return string|null
     */
    public function getRememberToken()
    {
        return null;
    }

    /**
     * Set the token value for the "remember me" session.
     *
     * @param  string  $value
     * @return void
     */
    public function setRememberToken($value)
    {
        // Do nothing - we don't store remember tokens
    }

    /**
     * Get the column name for the "remember me" token.
     *
     * @return string
     */
    public function getRememberTokenName()
    {
        return null;
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'user_password' => 'hashed',
        ];
    }
}
