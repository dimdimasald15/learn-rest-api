<?php

namespace App\Models;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Model implements Authenticatable
{
    use HasFactory;

    protected $table = "users"; // Nama tabel di database
    protected $primaryKey = "id"; // Nama kolom primary key di tabel
    protected $keyType = "int"; // Tipe data dari primary key
    public $timestamps = true; // Apakah model menggunakan timestamp
    public $incrementing = true; // Apakah primary key auto-increment

    // Kolom yang dapat diisi secara massal
    protected $fillable = ['username', 'name', 'password'];

    // Definisi relasi one-to-many dengan model Contact
    public function contacts(): HasMany
    {
        return $this->hasMany(Contact::class, "user_id", "id");
    }

    // Metode untuk memenuhi kontrak Authenticatable
    public function getAuthIdentifierName()
    {
        return 'username'; // Nama kolom yang digunakan sebagai identifier
    }

    public function getAuthIdentifier()
    {
        return $this->username; // Nilai identifier yang digunakan
    }

    public function getAuthPassword()
    {
        return $this->password; // Nilai password yang digunakan untuk autentikasi
    }

    public function getRememberToken()
    {
        return $this->token; // Nilai token "remember me"
    }

    public function setRememberToken($value)
    {
        $this->token = $value; // Mengatur nilai token "remember me"
    }

    public function getRememberTokenName()
    {
        return 'token'; // Nama kolom yang digunakan untuk token "remember me"
    }
}
