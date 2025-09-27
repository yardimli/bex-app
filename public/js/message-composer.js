$(document).ready(function() {
    // DaisyUI modals are built on top of the native <dialog> element.
    const composeModal = document.getElementById('composeMessageModal');
    
    // Exit if the modal doesn't exist on the current page.
    if (!composeModal) {
        return;
    }
    
    const teamSelect = $('#compose-team');
    const recipientsSelect = $('#compose-recipients');
    const subjectInput = $('#compose-subject');
    const bodyTextarea = $('#compose-body');
    
    /**
     * Resets the form fields to their initial state.
     */
    function resetComposeForm() {
        $('#composeMessageForm')[0].reset();
        recipientsSelect.html('<option>-- Select a team first --</option>').prop('disabled', true);
    }
    
    /**
     * Opens the compose modal and populates the team selection dropdown.
     * @param {string|null} preselectedTeamId - An optional team ID to pre-select.
     */
    function openComposeModal(preselectedTeamId = null) {
        resetComposeForm();
        teamSelect.html('<option>Loading teams...</option>');
        
        $.get('/api/user/teams', function(response) {
            const teams = response.teams;
            teamSelect.html('<option value="" selected disabled>-- Select a Team --</option>');
            
            if (teams && Array.isArray(teams)) {
                teams.forEach(team => {
                    teamSelect.append(`<option value="${team.id}">${$('<div>').text(team.name).html()}</option>`);
                });
            } else {
                console.error("Expected an array of teams, but received:", teams);
                teamSelect.html('<option value="" selected disabled>-- Error loading teams --</option>');
            }
            
            if (preselectedTeamId) {
                teamSelect.val(preselectedTeamId).trigger('change');
            }
        });
        
        composeModal.showModal();
    }
    
    // --- Event listener for the main "Compose Message" button ---
    $('body').on('click', '#compose-message-btn', function() {
        openComposeModal();
    });
    
    // --- Event listener for contextual "Message Team" buttons ---
    $('body').on('click', '.message-team-btn', function() {
        const teamId = $(this).data('team-id');
        openComposeModal(teamId);
    });
    
    // --- Load team members when a team is selected ---
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
    
    // --- Handle sending the message ---
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
        
        button.prop('disabled', true).html('<span class="loading loading-spinner loading-sm"></span> Sending...');
        
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
                composeModal.close();
                alert('Message sent successfully!');
                // If on the inbox page, reload the message list.
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
    
    composeModal.addEventListener('close', function () {
        resetComposeForm();
    });
});
