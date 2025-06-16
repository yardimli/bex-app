$(document).ready(function() {
    const composeModal = new bootstrap.Modal(document.getElementById('composeMessageModal'));
    const teamSelect = $('#compose-team');
    const recipientsSelect = $('#compose-recipients');
    const subjectInput = $('#compose-subject');
    const bodyTextarea = $('#compose-body');

    function resetComposeForm() {
        $('#composeMessageForm')[0].reset();
        recipientsSelect.html('<option>-- Select a team first --</option>').prop('disabled', true);
    }

    // --- Open Modal and Populate Teams ---
    function openComposeModal(preselectedTeamId = null) {
        resetComposeForm();
        teamSelect.html('<option>Loading teams...</option>');

        $.get('/api/user/teams', function(teams) {
            teamSelect.html('<option value="" selected disabled>-- Select a Team --</option>');
            teams.forEach(team => {
                teamSelect.append(`<option value="${team.id}">${$('<div>').text(team.name).html()}</option>`);
            });

            if (preselectedTeamId) {
                teamSelect.val(preselectedTeamId).trigger('change');
            }
        });

        composeModal.show();
    }

    // --- Event listener for generic compose button ---
    $('body').on('click', '#compose-message-btn', function() {
        openComposeModal();
    });

    // --- Event listener for contextual "message team" button ---
    $('body').on('click', '.message-team-btn', function() {
        const teamId = $(this).data('team-id');
        openComposeModal(teamId);
    });

    // --- Load Recipients when Team is Selected ---
    teamSelect.on('change', function() {
        const teamId = $(this).val();
        if (!teamId) {
            recipientsSelect.html('<option>-- Select a team first --</option>').prop('disabled', true);
            return;
        }

        recipientsSelect.html('<option>Loading members...</option>').prop('disabled', true);

        $.get(`/api/teams/${teamId}/members`, function(members) {
            recipientsSelect.empty().prop('disabled', false);
            members.forEach(member => {
                recipientsSelect.append(`<option value="${member.id}">${$('<div>').text(member.name).html()}</option>`);
            });
        }).fail(function() {
            recipientsSelect.html('<option>Error loading members</option>').prop('disabled', true);
        });
    });

    // --- Send Message ---
    $('#sendMessageButton').on('click', function() {
        const button = $(this);
        const teamId = teamSelect.val();
        const recipientIds = recipientsSelect.val();
        const subject = subjectInput.val();
        const body = bodyTextarea.val();

        if (!teamId || !recipientIds || recipientIds.length === 0 || !subject.trim() || !body.trim()) {
            alert('Please fill out all fields and select at least one recipient.');
            return;
        }

        button.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Sending...');

        $.ajax({
            url: `/api/teams/${teamId}/messages`,
            method: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({
                recipient_ids: recipientIds,
                subject: subject,
                body: body
            }),
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            success: function() {
                composeModal.hide();
                alert('Message sent successfully!');
                // If on inbox page, maybe reload the list
                if (typeof loadInbox === 'function') {
                    loadInbox();
                }
            },
            error: function(jqXHR) {
                alert('Error sending message: ' + (jqXHR.responseJSON?.error || 'Please try again.'));
            },
            complete: function() {
                button.prop('disabled', false).html('Send Message');
            }
        });
    });

    $('#composeMessageModal').on('hidden.bs.modal', function () {
        resetComposeForm();
    });
});
