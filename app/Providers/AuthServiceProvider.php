<?php

	namespace App\Providers;

	// use Illuminate\Support\Facades\Gate;
	use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

	use App\Models\ActionItem;

	// Add model
	use App\Policies\ActionItemPolicy;

	// Add policy

	class AuthServiceProvider extends ServiceProvider
	{
		/**
		 * The model to policy mappings for the application.
		 *
		 * @var array<class-string, class-string>
		 */
		protected $policies = [
			ActionItem::class => ActionItemPolicy::class, // Register the policy
		];

		/**
		 * Register any authentication / authorization services.
		 */
		public function boot(): void
		{
			//
		}
	}
