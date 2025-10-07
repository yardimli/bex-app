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
            position: relative;
            overflow: hidden;
        }

        /* START: Styles for Background Curves */
        #background-curves {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 0;
        }
        /* END: Styles for Background Curves */

        /* Ensure content is on top of the curves */
        #page1, #page2 {
            position: relative;
            z-index: 1;
        }

        /* START: Flip Card Styles */
        .flip-card {
            background-color: transparent;
            aspect-ratio: 1 / 1; /* Ensure the card area is square */
            perspective: 1000px; /* 3D effect */
            cursor: pointer;
        }

        .flip-card-inner {
            position: relative;
            width: 100%;
            height: 100%;
            transition: transform 0.6s;
            transform-style: preserve-3d;
        }

        /* This class is added by JS on click */
        .flip-card:hover .flip-card-inner,
        .flip-card-inner.is-flipped {
            transform: rotateY(180deg);
        }

        .flip-card-front, .flip-card-back {
            position: absolute;
            width: 100%;
            height: 100%;
            -webkit-backface-visibility: hidden; /* Safari */
            backface-visibility: hidden;
            border-radius: 1rem; /* 16px */
        }

        .flip-card-back {
            transform: rotateY(180deg);
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
            /* Added for SVG background positioning */
            position: relative;
            overflow: hidden;
        }
        /* END: Flip Card Styles */
    </style>
</head>
<body class="bg-base-200 font-sans antialiased">
<div id="main-container" class="container mx-auto min-h-screen flex flex-col items-center justify-center p-4" style="background-color: #a2d2ff; padding-bottom: 2rem;">

    <!-- START: Background Curves SVG -->
    <div id="background-curves">
        <svg width="100%" height="100%" viewBox="0 0 1440 800" preserveAspectRatio="xMidYMid slice" xmlns="http://www.w3.org/2000/svg">
            <path d="M-200 200 C 400 0, 1000 800, 1600 600" stroke="rgba(255, 255, 255, 0.2)" stroke-width="5" fill="none" />
            <path d="M-100 700 C 500 900, 900 100, 1500 200" stroke="rgba(255, 255, 255, 0.15)" stroke-width="4" fill="none" />
        </svg>
    </div>
    <!-- END: Background Curves SVG -->


    <!-- Page 1: Introduction -->
    <div id="page1" class="text-center w-full max-w-7xl">
        <div class="relative w-full lg:h-[600px] flex flex-col justify-center">
            <h1 class="text-4xl md:text-5xl font-bold lg:my-0 lg:absolute lg:top-1/4 lg:left-1/2 lg:-translate-x-1/2 lg:-translate-y-1/2 lg:z-10">
                Accomplish more<br> with Bex
            </h1>
            <div class="grid grid-cols-2 gap-8 lg:gap-6 lg:block mt-8 lg:mt-0">

                <!-- Item 1: Smart Summaries -->
                <div class="flip-card lg:absolute lg:top-[47%] lg:left-[-2%] lg:w-1/6 transition-transform duration-300 hover:scale-110">
                    <div class="flip-card-inner">
                        <div class="flip-card-front">
                            <!-- Changed here -->
                            <div class="w-full aspect-square bg-base-300 rounded-2xl border-4 p-2 shadow-lg" style="border-color: #fca311;">
                                <img src="{{ asset('images/group_chats.png') }}" alt="Group Chats" class="w-full h-full object-cover rounded-lg">
                            </div>
                            <!-- Changed here -->
                            <p class="absolute -bottom-4 text-warning-content text-sm font-semibold px-3 py-1 rounded-lg shadow-md" style="background-color: #fca311;">Group Chats</p>
                        </div>
                        <!-- Changed here -->
                        <div class="flip-card-back border-4" style="background-color: #fca311; border-color: #fca311;">
                            <svg width="100%" height="100%" viewBox="0 0 100 100" preserveAspectRatio="none" class="absolute inset-0 z-0 opacity-50"><path d="M-10 20 C 40 0, 60 100, 110 80" stroke="rgba(0, 0, 0, 0.1)" stroke-width="2" fill="none" /><path d="M-5 90 C 30 110, 70 50, 105 60" stroke="rgba(0, 0, 0, 0.05)" stroke-width="1" fill="none" /></svg>
                            <div class="relative z-10 p-4">
                                <h4 class="font-bold text-lg mb-1 text-secondary-content">Group Chats</h4>
                                <!-- Changed here -->
                                <p class="text-base text-secondary-content/80">Collaborate in real-time with your team in dedicated, organized conversation channels.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Item 2: AI-Powered Chat -->
                <div class="flip-card lg:absolute lg:top-[5%] lg:right-[2%] lg:w-1/6 transition-transform duration-300 hover:scale-110">
                    <div class="flip-card-inner">
                        <div class="flip-card-front">
                            <div class="w-full aspect-square bg-base-300 rounded-2xl border-4 p-2 shadow-lg" style="border-color: #fb6f92;">
                                <img src="{{ asset('images/ai_chat.png') }}" alt="AI-Powered Chat" class="w-full h-full object-cover rounded-lg">
                            </div>
                            <p class="absolute -bottom-4 text-secondary-content text-sm font-semibold px-3 py-1 rounded-lg shadow-md" style="background-color: #fb6f92">AI-Powered Chat</p>
                        </div>
                        <div class="flip-card-back border-4" style="background-color: #fb6f92; border-color: #fb6f92">
                            <svg width="100%" height="100%" viewBox="0 0 100 100" preserveAspectRatio="none" class="absolute inset-0 z-0 opacity-50"><path d="M-10 20 C 40 0, 60 100, 110 80" stroke="rgba(255, 255, 255, 0.2)" stroke-width="2" fill="none" /><path d="M-5 90 C 30 110, 70 50, 105 60" stroke="rgba(255, 255, 255, 0.15)" stroke-width="1" fill="none" /></svg>
                            <div class="relative z-10 p-4">
                                <h4 class="font-bold text-lg mb-1 text-secondary-content">AI-Powered Chat</h4>
                                <!-- Changed here -->
                                <p class="text-base text-secondary-content/80">Ask questions, brainstorm ideas, and get instant answers from an AI that understands your context.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Item 3: Group Chats -->
                <div class="flip-card lg:absolute lg:top-[10%] lg:left-[10%] lg:w-1/5 transition-transform duration-300 hover:scale-110">
                    <div class="flip-card-inner">
                        <div class="flip-card-front">
                            <div class="w-full aspect-square bg-base-300 rounded-2xl border-4 p-2 shadow-lg" style="border-color: #023047">
                                <img src="{{ asset('images/smart_summaries.png') }}" alt="Smart Summaries" class="w-full h-full object-cover rounded-lg">
                            </div>
                            <p class="absolute -bottom-4 text-primary-content text-sm font-semibold px-3 py-1 rounded-lg shadow-md" style="background-color: #023047">Smart Summaries</p>
                        </div>
                        <div class="flip-card-back border-4" style="background-color: #023047; border-color: #023047">
                            <svg width="100%" height="100%" viewBox="0 0 100 100" preserveAspectRatio="none" class="absolute inset-0 z-0 opacity-50"><path d="M-10 20 C 40 0, 60 100, 110 80" stroke="rgba(255, 255, 255, 0.2)" stroke-width="2" fill="none" /><path d="M-5 90 C 30 110, 70 50, 105 60" stroke="rgba(255, 255, 255, 0.15)" stroke-width="1" fill="none" /></svg>
                            <div class="relative z-10 p-4">
                                <h4 class="font-bold text-lg mb-1 text-primary-content">Smart Summaries</h4>
                                <!-- Changed here -->
                                <p class="text-base text-primary-content/80">Instantly condense long documents and conversations into key points, saving you hours of reading.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Item 4: Audio Transcription -->
                <div class="flip-card lg:absolute lg:bottom-[6%] lg:left-[30%] lg:w-1/6 transition-transform duration-300 hover:scale-110">
                    <div class="flip-card-inner">
                        <div class="flip-card-front">
                            <div class="w-full aspect-square bg-base-300 rounded-2xl border-4 border-info p-2 shadow-lg">
                                <img src="{{ asset('images/audio_transcription.png') }}" alt="Audio Transcription" class="w-full h-full object-cover rounded-lg">
                            </div>
                            <p class="absolute -bottom-4 bg-info text-info-content text-sm font-semibold px-3 py-1 rounded-lg shadow-md">Audio Transcription</p>
                        </div>
                        <div class="flip-card-back bg-info border-4 border-info">
                            <svg width="100%" height="100%" viewBox="0 0 100 100" preserveAspectRatio="none" class="absolute inset-0 z-0 opacity-50"><path d="M-10 20 C 40 0, 60 100, 110 80" stroke="rgba(255, 255, 255, 0.2)" stroke-width="2" fill="none" /><path d="M-5 90 C 30 110, 70 50, 105 60" stroke="rgba(255, 255, 255, 0.15)" stroke-width="1" fill="none" /></svg>
                            <div class="relative z-10 p-4">
                                <h4 class="font-bold text-lg mb-1 text-white">Audio Transcription</h4>
                                <!-- Changed here -->
                                <p class="text-base text-white/80">Turn spoken words from audio or video files into searchable, editable text in seconds.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Item 5: Team Workspaces -->
                <div class="flip-card lg:absolute lg:bottom-[5%] lg:right-[20%] lg:w-1/4 transition-transform duration-300 hover:scale-110">
                    <div class="flip-card-inner">
                        <div class="flip-card-front">
                            <!-- Changed here -->
                            <div class="w-full aspect-square bg-base-300 rounded-2xl border-4 p-2 shadow-lg" style="border-color: #0a9396;">
                                <img src="{{ asset('images/team_workspaces.png') }}" alt="Team Workspaces" class="w-full h-full object-cover rounded-lg">
                            </div>
                            <!-- Changed here -->
                            <p class="absolute -bottom-4 text-accent-content text-sm font-semibold px-3 py-1 rounded-lg shadow-md" style="background-color: #0a9396;">Team Workspaces</p>
                        </div>
                        <!-- Changed here -->
                        <div class="flip-card-back border-4" style="background-color: #0a9396; border-color: #0a9396;">
                            <svg width="100%" height="100%" viewBox="0 0 100 100" preserveAspectRatio="none" class="absolute inset-0 z-0 opacity-50"><path d="M-10 20 C 40 0, 60 100, 110 80" stroke="rgba(255, 255, 255, 0.2)" stroke-width="2" fill="none" /><path d="M-5 90 C 30 110, 70 50, 105 60" stroke="rgba(255, 255, 255, 0.15)" stroke-width="1" fill="none" /></svg>
                            <div class="relative z-10 p-4">
                                <h4 class="font-bold text-lg mb-1 text-white">Team Workspaces</h4>
                                <!-- Changed here -->
                                <p class="text-base text-white/80">A central hub for your team's projects, files, and conversations. Keep everyone aligned and informed.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Item 6: Personal Notes -->
                <div class="flip-card lg:absolute lg:top-[65%] lg:right-[-5%] lg:w-1/6 transition-transform duration-300 hover:scale-110">
                    <div class="flip-card-inner">
                        <div class="flip-card-front">
                            <div class="w-full aspect-square bg-base-300 rounded-2xl border-4 border-success p-2 shadow-lg">
                                <img src="{{ asset('images/personal_notes.png') }}" alt="Personal Notes" class="w-full h-full object-cover rounded-lg">
                            </div>
                            <p class="absolute -bottom-4 bg-success text-success-content text-sm font-semibold px-3 py-1 rounded-lg shadow-md">Personal Notes</p>
                        </div>
                        <div class="flip-card-back bg-success border-4 border-success">
                            <svg width="100%" height="100%" viewBox="0 0 100 100" preserveAspectRatio="none" class="absolute inset-0 z-0 opacity-50"><path d="M-10 20 C 40 0, 60 100, 110 80" stroke="rgba(255, 255, 255, 0.2)" stroke-width="2" fill="none" /><path d="M-5 90 C 30 110, 70 50, 105 60" stroke="rgba(255, 255, 255, 0.15)" stroke-width="1" fill="none" /></svg>
                            <div class="relative z-10 p-4">
                                <h4 class="font-bold text-lg mb-1 text-white">Personal Notes</h4>
                                <!-- Changed here -->
                                <p class="text-base text-white/80">A private space to jot down ideas, draft messages, and organize your thoughts before sharing.</p>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <div class="mt-24 lg:mt-0">
            <button id="continue-btn" class="btn btn-primary btn-wide btn-lg" style="background-color: #023047">Continue</button>
        </div>
    </div>

    <!-- Page 2: Collaboration & Auth (Initially hidden) -->
    <div id="page2" class="w-full max-w-7xl hidden opacity-0 px-4">
        <!-- START: Global Header for Page 2 -->
        <div class="text-center w-full mb-12">
            <h1 class="text-4xl md:text-5xl font-bold mb-8 text-base-content">Collaborate smarter with Bex</h1>
            <div class="flex justify-center items-center gap-4">
                <a href="{{ route('register') }}" class="btn btn-primary btn-lg" style="background-color: #023047">
                    <i class="bi bi-person-plus-fill text-xl"></i> Sign Up
                </a>
                <span class="text-base-content/60 font-semibold">or</span>
                <a href="{{ route('login') }}" class="btn btn-outline btn-lg">
                    <i class="bi bi-box-arrow-in-right text-xl"></i> Log In
                </a>
            </div>
        </div>
        <!-- END: Global Header for Page 2 -->

        <!-- START: Two-Column Layout -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-start">

            <!-- Left Column: Collaboration Features -->
            <div class="grid grid-cols-2 gap-4 md:gap-6">
                <!-- Item 7: Action Items -->
                <div class="flip-card">
                    <div class="flip-card-inner">
                        <div class="flip-card-front">
                            <!-- Changed here -->
                            <div class="w-full aspect-square bg-base-300 rounded-2xl border-4 p-2 shadow-lg" style="border-color: #0a9396;">
                                <img src="{{ asset('images/action_items.png') }}" alt="Action Items" class="w-full h-full object-cover rounded-lg">
                            </div>
                            <!-- Changed here -->
                            <p class="absolute -bottom-3 text-accent-content text-sm font-semibold px-3 py-1 rounded-lg shadow-md" style="background-color: #0a9396;">Action Items</p>
                        </div>
                        <!-- Changed here -->
                        <div class="flip-card-back border-4" style="background-color: #0a9396; border-color: #0a9396;">
                            <svg width="100%" height="100%" viewBox="0 0 100 100" preserveAspectRatio="none" class="absolute inset-0 z-0 opacity-50"><path d="M-10 20 C 40 0, 60 100, 110 80" stroke="rgba(255, 255, 255, 0.2)" stroke-width="2" fill="none" /><path d="M-5 90 C 30 110, 70 50, 105 60" stroke="rgba(255, 255, 255, 0.15)" stroke-width="1" fill="none" /></svg>
                            <div class="relative z-10 p-4">
                                <h4 class="font-bold text-lg mb-1 text-white">Action Items</h4>
                                <!-- Changed here -->
                                <p class="text-base text-white/80">AI automatically identifies tasks and deadlines from your conversations so nothing gets missed.</p>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Item 8: File Management -->
                <div class="flip-card">
                    <div class="flip-card-inner">
                        <div class="flip-card-front">
                            <!-- Changed here -->
                            <div class="w-full aspect-square bg-base-300 rounded-2xl border-4 p-2 shadow-lg" style="border-color: #fca311;">
                                <img src="{{ asset('images/file_management.png') }}" alt="File Management" class="w-full h-full object-cover rounded-lg">
                            </div>
                            <!-- Changed here -->
                            <p class="absolute -bottom-3 text-warning-content text-sm font-semibold px-3 py-1 rounded-lg shadow-md" style="background-color: #fca311;">File Management</p>
                        </div>
                        <!-- Changed here -->
                        <div class="flip-card-back border-4" style="background-color: #fca311; border-color: #fca311;">
                            <svg width="100%" height="100%" viewBox="0 0 100 100" preserveAspectRatio="none" class="absolute inset-0 z-0 opacity-50"><path d="M-10 20 C 40 0, 60 100, 110 80" stroke="rgba(0, 0, 0, 0.1)" stroke-width="2" fill="none" /><path d="M-5 90 C 30 110, 70 50, 105 60" stroke="rgba(0, 0, 0, 0.05)" stroke-width="1" fill="none" /></svg>
                            <div class="relative z-10 p-4">
                                <h4 class="font-bold text-lg mb-1 text-secondary-content">File Management</h4>
                                <!-- Changed here -->
                                <p class="text-base text-secondary-content/80">Upload, organize, and find any file within your chats and workspaces. Fully searchable and secure.</p>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Item 9: Shared Files -->
                <div class="flip-card">
                    <div class="flip-card-inner">
                        <div class="flip-card-front">
                            <div class="w-full aspect-square bg-base-300 rounded-2xl border-4 border-success p-2 shadow-lg">
                                <img src="{{ asset('images/shared_files.png') }}" alt="Shared Files" class="w-full h-full object-cover rounded-lg">
                            </div>
                            <p class="absolute -bottom-3 bg-success text-success-content text-sm font-semibold px-3 py-1 rounded-lg shadow-md">Shared Files</p>
                        </div>
                        <div class="flip-card-back bg-success border-4 border-success">
                            <svg width="100%" height="100%" viewBox="0 0 100 100" preserveAspectRatio="none" class="absolute inset-0 z-0 opacity-50"><path d="M-10 20 C 40 0, 60 100, 110 80" stroke="rgba(255, 255, 255, 0.2)" stroke-width="2" fill="none" /><path d="M-5 90 C 30 110, 70 50, 105 60" stroke="rgba(255, 255, 255, 0.15)" stroke-width="1" fill="none" /></svg>
                            <div class="relative z-10 p-4">
                                <h4 class="font-bold text-lg mb-1 text-white">Shared Files</h4>
                                <!-- Changed here -->
                                <p class="text-base text-white/80">Easily share documents with team members and get feedback directly within the platform.</p>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Item 10: Member Management -->
                <div class="flip-card">
                    <div class="flip-card-inner">
                        <div class="flip-card-front">
                            <div class="w-full aspect-square bg-base-300 rounded-2xl border-4 border-info p-2 shadow-lg">
                                <img src="{{ asset('images/member_management.png') }}" alt="Member Management" class="w-full h-full object-cover rounded-lg">
                            </div>
                            <p class="absolute -bottom-3 bg-info text-info-content text-sm font-semibold px-3 py-1 rounded-lg shadow-md">Member Management</p>
                        </div>
                        <div class="flip-card-back bg-info border-4 border-info">
                            <svg width="100%" height="100%" viewBox="0 0 100 100" preserveAspectRatio="none" class="absolute inset-0 z-0 opacity-50"><path d="M-10 20 C 40 0, 60 100, 110 80" stroke="rgba(255, 255, 255, 0.2)" stroke-width="2" fill="none" /><path d="M-5 90 C 30 110, 70 50, 105 60" stroke="rgba(255, 255, 255, 0.15)" stroke-width="1" fill="none" /></svg>
                            <div class="relative z-10 p-4">
                                <h4 class="font-bold text-lg mb-1 text-white">Member Management</h4>
                                <!-- Changed here -->
                                <p class="text-base text-white/80">Invite, remove, and manage user roles and permissions with simple, intuitive controls.</p>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Item 11: Secure Data -->
                <div class="flip-card">
                    <div class="flip-card-inner">
                        <div class="flip-card-front">
                            <div class="w-full aspect-square bg-base-300 rounded-2xl border-4 p-2 shadow-lg" style="border-color: #fb6f92;">
                                <img src="{{ asset('images/secure_data.png') }}" alt="Secure Data" class="w-full h-full object-cover rounded-lg">
                            </div>
                            <p class="absolute -bottom-3 text-primary-content text-sm font-semibold px-3 py-1 rounded-lg shadow-md" style="background-color: #fb6f92">Secure Data</p>
                        </div>
                        <div class="flip-card-back border-4" style="background-color: #fb6f92; border-color: #fb6f92">
                            <svg width="100%" height="100%" viewBox="0 0 100 100" preserveAspectRatio="none" class="absolute inset-0 z-0 opacity-50"><path d="M-10 20 C 40 0, 60 100, 110 80" stroke="rgba(255, 255, 255, 0.2)" stroke-width="2" fill="none" /><path d="M-5 90 C 30 110, 70 50, 105 60" stroke="rgba(255, 255, 255, 0.15)" stroke-width="1" fill="none" /></svg>
                            <div class="relative z-10 p-4">
                                <h4 class="font-bold text-lg mb-1 text-secondary-content">Secure Data</h4>
                                <!-- Changed here -->
                                <p class="text-base text-secondary-content/80">Your conversations and files are protected with end-to-end encryption and enterprise-grade security.</p>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Item 12: Prep for Meetings -->
                <div class="flip-card">
                    <div class="flip-card-inner">
                        <div class="flip-card-front">
                            <div class="w-full aspect-square bg-base-300 rounded-2xl border-4 p-2 shadow-lg" style="border-color: #023047">
                                <img src="{{ asset('images/prepare_meetings.png') }}" alt="Prep for Meetings" class="w-full h-full object-cover rounded-lg">
                            </div>
                            <p class="absolute -bottom-3 text-primary-content text-sm font-semibold px-3 py-1 rounded-lg shadow-md" style="background-color: #023047">Prep for Meetings</p>
                        </div>
                        <div class="flip-card-back border-4" style="background-color: #023047; border-color: #023047">
                            <svg width="100%" height="100%" viewBox="0 0 100 100" preserveAspectRatio="none" class="absolute inset-0 z-0 opacity-50"><path d="M-10 20 C 40 0, 60 100, 110 80" stroke="rgba(255, 255, 255, 0.2)" stroke-width="2" fill="none" /><path d="M-5 90 C 30 110, 70 50, 105 60" stroke="rgba(255, 255, 255, 0.15)" stroke-width="1" fill="none" /></svg>
                            <div class="relative z-10 p-4">
                                <h4 class="font-bold text-lg mb-1 text-primary-content">Prep for Meetings</h4>
                                <!-- Changed here -->
                                <p class="text-base text-primary-content/80">Generate agendas, talking points, and summaries of past conversations to walk into every meeting prepared.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column: Pricing -->
            <div class="bg-base-100/50 backdrop-blur-lg p-6 md:p-8 rounded-2xl shadow-xl border border-white/20">
                <div class="text-center mb-6">
                    <h2 class="text-3xl font-bold">Affordable Plans for Every Team</h2>
                    <p class="text-md mt-2">More members means more savings.</p>
                </div>

                <div class="w-full max-w-md mx-auto">
                    <div class="text-center mb-6">
                        <span class="font-semibold mr-4">Bill Monthly</span>
                        <input type="checkbox" class="toggle toggle-primary" id="billing-toggle">
                        <span class="ml-4 font-semibold">Bill Yearly</span>
                    </div>

                    <div class="card bg-base-100 shadow-lg">
                        <div class="card-body">
                            <div class="flex justify-between items-start">
                                <div>
                                    <h3 class="card-title text-2xl" id="plan-title">Individual Plan</h3>
                                    <p id="plan-description">For solo power users.</p>
                                </div>
                            </div>

                            <div class="mt-4">
                                <label for="quantity-slider" class="label">Number of Users: <span class="font-bold" id="quantity-label">1</span></label>
                                <input type="range" min="1" max="100" value="1" class="range range-primary" id="quantity-slider">
                            </div>

                            <div class="my-4 text-center">
                                <p class="text-xl">
                                    <span class="text-5xl font-extrabold" id="price-per-user">$6.99</span>
                                    <span id="period">/ month</span>
                                </p>
                                <p class="text-2xl font-bold mt-4">
                                    Total: <span id="total-price">$6.99</span> <span id="total-period">/ month</span>
                                </p>
                            </div>

                            <ul class="space-y-2 mt-4 text-sm">
                                <li class="flex items-center gap-2"><i class="bi bi-check-circle-fill text-success"></i> Unlimited Personal &amp; Group Chats</li>
                                <li class="flex items-center gap-2"><i class="bi bi-check-circle-fill text-success"></i> Document Summarization &amp; Analysis</li>
                                <li class="flex items-center gap-2"><i class="bi bi-check-circle-fill text-success"></i> Audio Transcription</li>
                                <li class="flex items-center gap-2"><i class="bi bi-check-circle-fill text-success"></i> Shared Team Workspace (2+ users)</li>
                                <li class="flex items-center gap-2"><i class="bi bi-check-circle-fill text-success"></i> Centralized Billing (2+ users)</li>
                            </ul>

                            <div class="card-actions mt-6">
                                <a href="{{ route('register') }}" class="btn btn-primary w-full">Get Started</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- END: Two-Column Layout -->
    </div>
</div>

<script>
    // Script for page transition
    document.getElementById('continue-btn').addEventListener('click', function() {
        const page1 = document.getElementById('page1');
        const page2 = document.getElementById('page2');
        const mainContainer = document.getElementById('main-container');

        mainContainer.style.backgroundColor = '#0077b6';
        page1.style.display = 'none';
        page2.classList.remove('hidden');
        setTimeout(() => {
            page2.style.opacity = '1';
        }, 50);
    });

    document.addEventListener('DOMContentLoaded', function () {
        // --- Script for Flip Cards (for touch devices) ---
        const cards = document.querySelectorAll('.flip-card');
        cards.forEach(card => {
            card.addEventListener('click', function() {
                const cardInner = card.querySelector('.flip-card-inner');
                if (cardInner) {
                    cardInner.classList.toggle('is-flipped');
                }
            });
        });

        // --- Script for pricing calculator ---
        const billingToggle = document.getElementById('billing-toggle');
        const quantitySlider = document.getElementById('quantity-slider');
        const planTitle = document.getElementById('plan-title');
        const planDescription = document.getElementById('plan-description');
        const quantityLabel = document.getElementById('quantity-label');
        const pricePerUserEl = document.getElementById('price-per-user');
        const periodEl = document.getElementById('period');
        const totalPriceEl = document.getElementById('total-price');
        const totalPeriodEl = document.getElementById('total-period');

        const monthlyTiers = { 1: 6.99, 2: 6.49, 11: 5.99, 51: 5.49, 101: 4.99 };
        const yearlyTiers = { 1: 69.90, 2: 64.90, 11: 59.90, 51: 54.90, 101: 49.90 };

        function getPriceForQuantity(quantity, tiers) {
            if (quantity >= 101) return tiers[101];
            if (quantity >= 51) return tiers[51];
            if (quantity >= 11) return tiers[11];
            if (quantity >= 2) return tiers[2];
            return tiers[1];
        }

        function updateUI() {
            const quantity = parseInt(quantitySlider.value, 10);
            const isYearly = billingToggle.checked;
            const tiers = isYearly ? yearlyTiers : monthlyTiers;
            const pricePerUser = getPriceForQuantity(quantity, tiers);
            const totalPrice = pricePerUser * quantity;
            const billingPeriodString = isYearly ? 'year' : 'month';

            quantityLabel.textContent = quantity;
            pricePerUserEl.textContent = `$${pricePerUser.toFixed(2)}`;
            totalPriceEl.textContent = `$${totalPrice.toFixed(2)}`;
            totalPeriodEl.textContent = `/ ${billingPeriodString}`;

            if (quantity === 1) {
                planTitle.textContent = 'Individual Plan';
                planDescription.textContent = 'For solo power users.';
                periodEl.textContent = `/ ${billingPeriodString}`;
            } else {
                planTitle.textContent = 'Team Plan';
                planDescription.textContent = `For your team of ${quantity}.`;
                periodEl.textContent = `/ user / ${billingPeriodString}`;
            }
        }

        if (billingToggle && quantitySlider) {
            billingToggle.addEventListener('change', updateUI);
            quantitySlider.addEventListener('input', updateUI);
            updateUI();
        }
    });
</script>
</body>
</html>
