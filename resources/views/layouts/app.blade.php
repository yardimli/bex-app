<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Bex Assistant</title>
	
	<!-- Bootstrap CSS -->
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
	<!-- Bootstrap Icons -->
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
	<!-- Custom CSS -->
	<link href="{{ asset('css/app.css') }}" rel="stylesheet">
	
	<style>
      /* Basic styling to mimic layout */
      body {
          background-color: #f8f9fa; /* Light background */
      }
      .main-wrapper {
          display: flex;
          height: 100vh;
          overflow: hidden;
      }
      .sidebar {
          width: 280px;
          background-color: #fff; /* White sidebar */
          padding: 1.5rem 1rem;
          border-right: 1px solid #dee2e6;
          display: flex;
          flex-direction: column;
          overflow-y: auto;
      }
      .main-content {
          flex-grow: 1;
          padding: 1.5rem;
          overflow-y: auto;
          background-color: #e9ecef; /* Slightly darker content background */
          display: flex;
          flex-direction: column;
      }
      .content-header {
          display: flex;
          justify-content: space-between;
          align-items: center;
          margin-bottom: 1.5rem;
          background-color: #fff; /* White header bar */
          padding: 0.75rem 1.5rem;
          border-radius: 0.375rem; /* Rounded corners */
          box-shadow: 0 1px 3px rgba(0,0,0,.1);
      }
      .chat-area {
          flex-grow: 1;
          display: flex;
          flex-direction: column;
          justify-content: center; /* Center initial prompt */
          align-items: center;
          background-color: #fff; /* White chat area */
          border-radius: 0.375rem;
          padding: 2rem;
          box-shadow: 0 1px 3px rgba(0,0,0,.1);
      }
      .message-input-wrapper {
          margin-top: auto; /* Pushes input to bottom when chat history exists */
          width: 100%;
          padding-top: 1rem; /* Space above input */
      }
      .message-input {
          position: relative;
      }
      .message-input .form-control {
          padding-right: 7rem; /* Space for icons */
      }
      .message-input-icons {
          position: absolute;
          right: 10px;
          top: 50%;
          transform: translateY(-50%);
      }
      .message-input-icons i {
          margin-left: 10px;
          cursor: pointer;
          color: #6c757d;
      }
      .action-buttons {
          text-align: center;
          margin-top: 1rem;
          margin-bottom: 1rem; /* Space below buttons */
      }
      .action-buttons .btn {
          margin: 0 5px;
          font-size: 0.875rem;
      }

      /* Modal specific styles */
      .modal-body .row .list-group {
          max-height: 400px; /* Limit height and make scrollable */
          overflow-y: auto;
          border-right: 1px solid #dee2e6;
      }
      .modal-body .row .list-group-item {
          cursor: pointer;
      }
      .modal-body .row .list-group-item.active {
          background-color: #e9ecef;
          border-color: #dee2e6;
          color: #212529;
      }
      .details-pane {
          padding-left: 1.5rem;
          display: flex;
          align-items: center;
          justify-content: center;
          min-height: 200px; /* Ensure pane has some height */
          color: #6c757d;
      }
      .team-files-sidebar {
          padding-right: 1.5rem;
          border-right: 1px solid #dee2e6;
      }
      .team-files-list {
          max-height: 400px;
          overflow-y: auto;
      }
      #summarizeContentModal .nav-tabs .nav-link {
          cursor: pointer;
      }
      #summarizeContentModal .tab-pane {
          padding-top: 1rem;
      }

      /* Sidebar Specific */
      .sidebar .btn-success { /* Green buttons */
          background-color: #198754; /* Standard Bootstrap green */
          border-color: #198754;
      }
      .sidebar .nav-link {
          color: #495057;
      }
      .sidebar h6 { /* History/Features titles */
          color: #6c757d;
          font-size: 0.8rem;
          text-transform: uppercase;
          margin-top: 1.5rem;
          margin-bottom: 0.5rem;
      }
      /* Ensure modals appear above backdrop */
      .modal { z-index: 1055; }
      .modal-backdrop { z-index: 1050; }
	</style>

</head>
<body>
<div class="main-wrapper">
	<!-- Sidebar -->
	@include('partials.sidebar')
	
	<!-- Main Content -->
	<main class="main-content">
		@yield('content')
	</main>
</div>

<!-- Modals -->
@include('partials.modals.recent_meetings')
@include('partials.modals.my_notes')
@include('partials.modals.team_files')
@include('partials.modals.my_recordings')
@include('partials.modals.my_action_items')
@include('partials.modals.settings')
@include('partials.modals.summarize_content')
@include('partials.modals.transcribe')

<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
<!-- Bootstrap Bundle JS (includes Popper) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous"></script>
<!-- Custom JS -->
<script src="{{ asset('js/app.js') }}"></script>

</body>
</html>
