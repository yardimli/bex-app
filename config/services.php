<?php

	return [

		/*
		|--------------------------------------------------------------------------
		| Third Party Services
		|--------------------------------------------------------------------------
		|
		| This file is for storing the credentials for third party services such
		| as Mailgun, Postmark, AWS and more. This file provides the de facto
		| location for this type of information, allowing packages to have
		| a conventional file to locate the various service credentials.
		|
		*/

		'mailgun' => [
			'domain' => env('MAILGUN_DOMAIN'),
			'secret' => env('MAILGUN_SECRET'),
			'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
			'scheme' => 'https',
		],

		'postmark' => [
			'token' => env('POSTMARK_TOKEN'),
		],

		'ses' => [
			'key' => env('AWS_ACCESS_KEY_ID'),
			'secret' => env('AWS_SECRET_ACCESS_KEY'),
			'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
		],

		'stripe' => [
			'model' => App\Models\User::class,
			'key' => env('STRIPE_KEY'),
			'secret' => env('STRIPE_SECRET'),
			'webhook' => [
				'secret' => env('STRIPE_WEBHOOK_SECRET'),
				'tolerance' => env('STRIPE_WEBHOOK_TOLERANCE', 300),
				'events' => [
					'customer.subscription.created',
					'customer.subscription.updated',
					'customer.subscription.deleted',
					// ... other events handled by Cashier
				],
			],
			// ADDED: Price IDs for easy access in the controller
			'individual_monthly_price_id' => env('STRIPE_INDIVIDUAL_MONTHLY_PRICE_ID'),
			'individual_yearly_price_id' => env('STRIPE_INDIVIDUAL_YEARLY_PRICE_ID'),
			'team_monthly_price_id' => env('STRIPE_TEAM_MONTHLY_PRICE_ID'),
			'team_yearly_price_id' => env('STRIPE_TEAM_YEARLY_PRICE_ID'),
		],

	];
