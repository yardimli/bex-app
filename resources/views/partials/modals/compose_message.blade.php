{{-- resources/views/partials/modals/compose_message.blade.php --}}

{{-- NEW: This partial contains the modal for composing a new message. --}}
<dialog id="composeMessageModal" class="modal modal-bottom sm:modal-middle">
	<div class="modal-box">
		<h3 class="font-bold text-lg">Compose New Message</h3>
		{{-- The form is separate from the action buttons to prevent the "Send" button from being in a form with method="dialog" --}}
		<form id="composeMessageForm" class="py-4 space-y-4">
			<div>
				<label for="compose-team" class="label"><span class="label-text">Team</span></label>
				<select id="compose-team" class="select select-bordered w-full">
					<option>-- Select a Team --</option>
				</select>
			</div>
			<div>
				<label for="compose-recipients" class="label"><span class="label-text">Recipients</span></label>
				<select id="compose-recipients" class="select select-bordered w-full" multiple disabled>
					<option>-- Select a team first --</option>
				</select>
			</div>
			<div>
				<label for="compose-subject" class="label"><span class="label-text">Subject</span></label>
				<input type="text" id="compose-subject" placeholder="Message Subject" class="input input-bordered w-full" />
			</div>
			<div>
				<label for="compose-body" class="label"><span class="label-text">Body</span></label>
				<textarea id="compose-body" class="textarea textarea-bordered w-full" rows="6" placeholder="Your message..."></textarea>
			</div>
		</form>
		<div class="modal-action">
			{{-- This button is handled by JS and should not close the modal on its own. type="button" is important. --}}
			<button type="button" class="btn btn-primary" id="sendMessageButton">Send Message</button>
			{{-- This form wrapper is the DaisyUI way to create a close button --}}
			<form method="dialog">
				<button class="btn btn-ghost">Cancel</button>
			</form>
		</div>
	</div>
	<form method="dialog" class="modal-backdrop"><button>close</button></form>
</dialog>
