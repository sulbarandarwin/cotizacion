<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRoles; // Agrega HasRoles

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function adminlte_profile_url()
    {
        // Asumiendo que estás usando la ruta de perfil de Laravel Breeze
        // que se llama 'profile.edit'
        return route('profile.edit');
    }

    public function adminlte_image()
    {
        // Si tienes un campo en tu tabla 'users' para la ruta del avatar, úsalo.
        // Ejemplo: return asset('storage/' . $this->avatar_path);

        // Si no, puedes devolver una imagen por defecto o Gravatar.
        // Ejemplo de imagen por defecto de AdminLTE:
        return 'https://picsum.photos/300/300'; // Placeholder de una imagen aleatoria
        // O una imagen que tengas en tu carpeta public:
        // return asset('img/default-avatar.png');
    }

    public function adminlte_desc()
    {
        // Puedes mostrar el rol del usuario si estás usando Spatie/laravel-permission
        if ($this->roles->isNotEmpty()) {
            return $this->roles->first()->name; // Muestra el primer rol del usuario
        }

        // O puedes mostrar su correo electrónico
        // return $this->email;

        // O un texto genérico
        // return 'Miembro del Equipo';

        return 'Usuario Registrado'; // Un valor por defecto
    }


}
