<?php

	namespace App\Models;

	// use Illuminate\Contracts\Auth\MustVerifyEmail; // <-- REMOVE or COMMENT OUT this line
	use Illuminate\Database\Eloquent\Factories\HasFactory;
	use Illuminate\Foundation\Auth\User as Authenticatable;
	use Illuminate\Notifications\Notifiable;
	use Laravel\Sanctum\HasApiTokens; // If using Sanctum

// class User extends Authenticatable implements MustVerifyEmail // <-- Original if it existed
	class User extends Authenticatable // <-- CHANGE TO THIS
	{
		use HasApiTokens, HasFactory, Notifiable; // Adapt traits as needed

		/**
		 * The attributes that are mass assignable.
		 *
		 * @var array<int, string>
		 */
		protected $fillable = [
			'name',
			'email',
			'password',
		];

		/**
		 * The attributes that should be hidden for serialization.
		 *
		 * @var array<int, string>
		 */
		protected $hidden = [
			'password',
			'remember_token',
		];

		/**
		 * The attributes that should be cast.
		 *
		 * @var array<string, string>
		 */
		protected $casts = [
			'email_verified_at' => 'datetime',
			'password' => 'hashed', // Use 'hashed' for Laravel 10+
		];

		public function chatHeaders()
		{
			return $this->hasMany(ChatHeader::class)->orderBy('updated_at', 'desc'); // Order by most recently updated
		}

	}
