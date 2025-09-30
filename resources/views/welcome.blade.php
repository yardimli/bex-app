<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Welcome to {{ config('app.name', 'Bex') }}</title>

    <!-- Fonts & Icons -->
    <link rel="dns-prefetch" href="//fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=Nunito" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <!-- Favicons -->
    <link rel="apple-touch-icon" sizes="180x180" href="/images/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/images/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/images/favicon-16x16.png">
    <link rel="manifest" href="/images/site.webmanifest">

    @vite('resources/css/app.css')
    <style>
        /* Simple transition for showing the second page */
        #page2 {
            transition: opacity 0.5s ease-in-out;
        }
        /* Add this rule for a smooth background color change */
        #main-container {
            transition: background-color 0.5s ease-in-out;
            /* Make this a positioning context for the background SVG */
            position: relative;
            overflow: hidden; /* Hide parts of the curves that go outside */
        }

        /* START: Styles for Background Curves */
        #background-curves {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 0; /* Place it behind the content */
        }
        /* END: Styles for Background Curves */

        /* Ensure content is on top of the curves */
        #page1, #page2 {
            position: relative;
            z-index: 1;
        }
    </style>
</head>
<body class="bg-base-200 font-sans antialiased">
<div id="main-container" class="container mx-auto min-h-screen flex flex-col items-center justify-center p-4" style="background-color: #a2d2ff; padding-bottom: 2rem;">

    <!-- START: Background Curves SVG -->
    <div id="background-curves">
        <svg width="100%" height="100%" viewBox="0 0 1440 800" preserveAspectRatio="xMidYMid slice" xmlns="http://www.w3.org/2000/svg">
            <!-- Curve 1: A gentle S-curve -->
            <path d="M-200 200 C 400 0, 1000 800, 1600 600" stroke="rgba(255, 255, 255, 0.2)" stroke-width="5" fill="none" />
            <!-- Curve 2: A different, more subtle curve -->
            <path d="M-100 700 C 500 900, 900 100, 1500 200" stroke="rgba(255, 255, 255, 0.15)" stroke-width="4" fill="none" />
        </svg>
    </div>
    <!-- END: Background Curves SVG -->


    <!-- Page 1: Introduction -->
    <div id="page1" class="text-center w-full max-w-7xl">

        <!-- This is the canvas for the floating elements on large screens -->
        <!-- On mobile, it will just be a container for a grid -->
        <div class="relative w-full lg:h-[600px] flex flex-col justify-center">

            <!-- Central Title: Positioned absolutely on large screens -->
            <h1 class="text-4xl md:text-5xl font-bold lg:my-0 lg:absolute lg:top-1/4 lg:left-1/2 lg:-translate-x-1/2 lg:-translate-y-1/2 lg:z-10">
                Accomplish more<br> with Bex
            </h1>

            <!-- Images Container: A grid on mobile, but its children become absolute on large screens -->
            <div class="grid grid-cols-2 gap-8 lg:gap-6 lg:block">

                <!-- Image Item 1 -->
                <div class="relative flex flex-col items-center lg:absolute lg:top-[10%] lg:left-[10%] lg:w-1/5 transition-transform duration-300 hover:scale-110">
                    <div class="w-full aspect-square bg-base-300 rounded-2xl border-4 p-2 shadow-lg" style="border-color: #023047">
                        <img src="{{ asset('images/smart_summaries.png') }}" alt="Feature 1" class="w-full h-full object-cover rounded-lg">
                    </div>
                    <p class="absolute -bottom-4 text-primary-content text-sm font-semibold px-3 py-1 rounded-lg shadow-md" style="background-color: #023047">Smart Summaries</p>
                </div>

                <!-- Image Item 2 -->
                <div class="relative flex flex-col items-center lg:absolute lg:top-[5%] lg:right-[2%] lg:w-1/6 transition-transform duration-300 hover:scale-110">
                    <div class="w-full aspect-square bg-base-300 rounded-2xl border-4 p-2 shadow-lg" style="border-color: #fb6f92;">
                        <img src="{{ asset('images/ai_chat.png') }}" alt="Feature 2" class="w-full h-full object-cover rounded-lg">
                    </div>
                    <p class="absolute -bottom-4 text-secondary-content text-sm font-semibold px-3 py-1 rounded-lg shadow-md" style="background-color: #fb6f92">AI-Powered Chat</p>
                </div>

                <!-- Image Item 3 -->
                <div class="relative flex flex-col items-center lg:absolute lg:top-[45%] lg:left-[-5%] lg:w-1/6 transition-transform duration-300 hover:scale-110">
                    <div class="w-full aspect-square bg-base-300 rounded-2xl border-4 border-accent p-2 shadow-lg">
                        <img src="{{ asset('images/action_items.png') }}" alt="Feature 3" class="w-full h-full object-cover rounded-lg">
                    </div>
                    <p class="absolute -bottom-4 bg-accent text-accent-content text-sm font-semibold px-3 py-1 rounded-lg shadow-md">Action Items</p>
                </div>

                <!-- Image Item 4 -->
                <div class="relative flex flex-col items-center lg:absolute lg:bottom-[6%] lg:left-[30%] lg:w-1/6 transition-transform duration-300 hover:scale-110">
                    <div class="w-full aspect-square bg-base-300 rounded-2xl border-4 border-info p-2 shadow-lg">
                        <img src="{{ asset('images/audio_transcription.png') }}" alt="Feature 4" class="w-full h-full object-cover rounded-lg">
                    </div>
                    <p class="absolute -bottom-4 bg-info text-info-content text-sm font-semibold px-3 py-1 rounded-lg shadow-md">Audio Transcription</p>
                </div>

                <!-- Image Item 5 -->
                <div class="relative flex flex-col items-center lg:absolute lg:bottom-[5%] lg:right-[20%] lg:w-1/4 transition-transform duration-300 hover:scale-110">
                    <div class="w-full aspect-square bg-base-300 rounded-2xl border-4 border-success p-2 shadow-lg">
                        <img src="{{ asset('images/personal_notes.png') }}" alt="Feature 5" class="w-full h-full object-cover rounded-lg">
                    </div>
                    <p class="absolute -bottom-4 bg-success text-success-content text-sm font-semibold px-3 py-1 rounded-lg shadow-md">Personal Notes</p>
                </div>

                <!-- Image Item 6 -->
                <div class="relative flex flex-col items-center lg:absolute lg:top-[65%] lg:right-[-5%] lg:w-1/6 transition-transform duration-300 hover:scale-110">
                    <div class="w-full aspect-square bg-base-300 rounded-2xl border-4 border-warning p-2 shadow-lg">
                        <img src="{{ asset('images/file_management.png') }}" alt="Feature 6" class="w-full h-full object-cover rounded-lg">
                    </div>
                    <p class="absolute -bottom-4 bg-warning text-warning-content text-sm font-semibold px-3 py-1 rounded-lg shadow-md">File Management</p>
                </div>
            </div>
        </div>

        <div class="mt-16 lg:mt-0">
            <button id="continue-btn" class="btn btn-primary btn-wide btn-lg" style="background-color: #023047">Continue</button>
        </div>
    </div>

    <!-- Page 2: Collaboration & Auth (Initially hidden) -->
    <div id="page2" class="text-center w-full max-w-4xl hidden opacity-0">
        <h1 class="text-4xl md:text-5xl font-bold mb-8 text-base-content">Collaborate smarter with Bex</h1>

        <div class="flex justify-center items-center gap-4 my-8">
            <a href="{{ route('register') }}" class="btn btn-primary btn-lg" style="background-color: #023047">
                <i class="bi bi-person-plus-fill text-xl"></i> Sign Up
            </a>
            <span class="text-base-content/60 font-semibold">or</span>
            <a href="{{ route('login') }}" class="btn btn-outline btn-lg">
                <i class="bi bi-box-arrow-in-right text-xl"></i> Log In
            </a>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-3 gap-8 md:gap-8">
            <!-- Image Item 7 -->
            <div class="relative flex flex-col items-center">
                <div class="w-full aspect-square bg-base-300 rounded-2xl border-4 border-accent p-2 shadow-lg">
                    <img src="{{ asset('images/team_workspaces.png') }}" alt="Collaboration 1" class="w-full h-full object-cover rounded-lg">
                </div>
                <p class="absolute -bottom-4 bg-accent text-accent-content text-sm font-semibold px-3 py-1 rounded-lg shadow-md">Team Workspaces</p>
            </div>
            <!-- Image Item 8 -->
            <div class="relative flex flex-col items-center">
                <div class="w-full aspect-square bg-base-300 rounded-2xl border-4 border-warning p-2 shadow-lg">
                    <img src="{{ asset('images/group_chats.png') }}" alt="Collaboration 2" class="w-full h-full object-cover rounded-lg">
                </div>
                <p class="absolute -bottom-4 bg-warning text-info-content text-sm font-semibold px-3 py-1 rounded-lg shadow-md">Group Chats</p>
            </div>
            <!-- Image Item 9 -->
            <div class="relative flex flex-col items-center">
                <div class="w-full aspect-square bg-base-300 rounded-2xl border-4 border-success p-2 shadow-lg">
                    <img src="{{ asset('images/shared_files.png') }}" alt="Collaboration 3" class="w-full h-full object-cover rounded-lg">
                </div>
                <p class="absolute -bottom-4 bg-success text-success-content text-sm font-semibold px-3 py-1 rounded-lg shadow-md">Shared Files</p>
            </div>
            <!-- Image Item 10 -->
            <div class="relative flex flex-col items-center">
                <div class="w-full aspect-square bg-base-300 rounded-2xl border-4 border-info p-2 shadow-lg">
                    <img src="{{ asset('images/member_management.png') }}" alt="Collaboration 4" class="w-full h-full object-cover rounded-lg">
                </div>
                <p class="absolute -bottom-4 bg-info text-warning-content text-sm font-semibold px-3 py-1 rounded-lg shadow-md">Member Management</p>
            </div>
            <!-- Image Item 11 -->
            <div class="relative flex flex-col items-center">
                <div class="w-full aspect-square bg-base-300 rounded-2xl border-4 p-2 shadow-lg" style="border-color: #fb6f92;">
                    <img src="{{ asset('images/secure_data.png') }}" alt="Collaboration 5" class="w-full h-full object-cover rounded-lg">
                </div>
                <p class="absolute -bottom-4 text-primary-content text-sm font-semibold px-3 py-1 rounded-lg shadow-md" style="background-color: #fb6f92">Secure Data</p>
            </div>
            <!-- Image Item 12 -->
            <div class="relative flex flex-col items-center">
                <div class="w-full aspect-square bg-base-300 rounded-2xl border-4 p-2 shadow-lg" style="border-color: #023047">
                    <img src="{{ asset('images/prepare_meetings.png') }}" alt="Collaboration 5" class="w-full h-full object-cover rounded-lg">
                </div>
                <p class="absolute -bottom-4 text-primary-content text-sm font-semibold px-3 py-1 rounded-lg shadow-md" style="background-color: #023047">Prep for Meetings</p>
            </div>
        </div>
    </div>
</div>

<script>
    document.getElementById('continue-btn').addEventListener('click', function() {
        const page1 = document.getElementById('page1');
        const page2 = document.getElementById('page2');
        const mainContainer = document.getElementById('main-container');

        mainContainer.style.backgroundColor = '#0077b6';

        // Hide page 1
        page1.style.display = 'none';

        // Show page 2 with a fade-in effect
        page2.classList.remove('hidden');
        setTimeout(() => {
            page2.style.opacity = '1';
        }, 50); // A small delay to ensure the transition triggers
    });
</script>
</body>
</html>
