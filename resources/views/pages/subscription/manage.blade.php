{{-- NEW: This file creates the subscription management page for the user. --}}
@extends('layouts.app')

@section('content')
	<div class="p-4 flex flex-col h-full gap-4">
		@include('partials.page_header')
		
		<div class="bg-base-100 rounded-box shadow-sm flex-grow p-4 overflow-y-auto">
			<div class="flex justify-center">
				<div class="w-full lg:w-8/12 space-y-6">
					
					{{-- Success/Error Alerts --}}
					@if (session('success'))
						<div role="alert" class="alert alert-success">
							<i class="bi bi-check-circle-fill"></i>
							<span>{{ session('success') }}</span>
						</div>
					@endif
					@if (session('error'))
						<div role="alert" class="alert alert-error">
							<i class="bi bi-x-circle-fill"></i>
							<span>{{ session('error') }}</span>
						</div>
					@endif
					
					{{-- Subscription Details Card --}}
					<div class="card bg-base-100 shadow-xl">
						<div class="card-body">
							<h2 class="card-title">Subscription Details</h2>
							<p class="text-base-content/70">View and manage your current subscription plan.</p>
							
							@if ($subscription)
								<div class="overflow-x-auto mt-4">
									<table class="table">
										<tbody>
										<tr>
											<th>Plan Name</th>
											<td>{{ config('app.name', 'Bex') }} Team Plan</td>
										</tr>
										<tr>
											<th>Users/Seats</th>
											<td>{{ $subscription->quantity }}</td>
										</tr>
										<tr>
											<th>Status</th>
											<td>
												@if ($subscription->active())
													<span class="badge badge-success">Active</span>
												@elseif ($subscription->onGracePeriod())
													<span class="badge badge-warning">Active (Cancels Soon)</span>
												@else
													<span class="badge badge-error">Inactive</span>
												@endif
											</td>
										</tr>
										@if ($subscription->onGracePeriod())
											<tr>
												<th>Subscription Ends</th>
												<td>{{ $subscription->ends_at->format('F j, Y') }}</td>
											</tr>
										@else
											<tr>
												<th>Next Billing Date</th>
												{{-- MODIFIED: Changed ->created to ->date() to get a Carbon instance. --}}
												<td>{{ $subscription->upcomingInvoice()->date()->toFormattedDateString() }}</td>
											</tr>
										@endif
										</tbody>
									</table>
								</div>
								
								<div class="card-actions justify-start mt-6 space-x-2">
									{{-- Manage Billing Button --}}
									<a href="{{ route('billing.portal') }}" class="btn btn-primary">
										<i class="bi bi-credit-card-fill"></i> Manage Billing
									</a>
									
									{{-- Cancel/Resume Buttons --}}
									@if ($subscription->onGracePeriod())
										<form action="{{ route('subscription.resume.post') }}" method="POST">
											@csrf
											<button type="submit" class="btn btn-success">
												<i class="bi bi-arrow-clockwise"></i> Resume Subscription
											</button>
										</form>
									@else
										<button class="btn btn-error" onclick="cancelSubscriptionModal.showModal()">
											<i class="bi bi-x-circle-fill"></i> Cancel Subscription
										</button>
									@endif
								</div>
							@else
								<p class="mt-4">You do not have an active subscription.</p>
								<div class="card-actions justify-start mt-4">
									<a href="{{ route('subscribe.index') }}" class="btn btn-primary">View Plans</a>
								</div>
							@endif
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	
	{{-- Cancellation Confirmation Modal --}}
	@if ($subscription && !$subscription->onGracePeriod())
		<dialog id="cancelSubscriptionModal" class="modal">
			<div class="modal-box">
				<h3 class="font-bold text-lg">Confirm Subscription Cancellation</h3>
				{{-- MODIFIED: Changed ->created to ->date() here as well for consistency and correctness. --}}
				<p class="py-4">Are you sure you want to cancel? Your subscription will remain active until the end of the current billing period on {{ $subscription->upcomingInvoice()->date()->toFormattedDateString() }}.</p>
				<div class="modal-action">
					<form method="dialog">
						<button class="btn">Nevermind</button>
					</form>
					<form action="{{ route('subscription.cancel.post') }}" method="POST">
						@csrf
						<button type="submit" class="btn btn-error">Yes, Cancel Subscription</button>
					</form>
				</div>
			</div>
			<form method="dialog" class="modal-backdrop"><button>close</button></form>
		</dialog>
	@endif
@endsection
