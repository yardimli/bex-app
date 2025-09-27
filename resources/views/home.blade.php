@extends('layouts.app')

@section('content')
    <div class="p-4 flex flex-col h-full gap-4">
        @include('partials.page_header')
        
        {{-- Main content area that grows and allows scrolling --}}
        <div class="bg-base-100 rounded-box shadow-sm flex-grow p-4 md:p-6 overflow-y-auto">
            
            {{-- Welcome Header --}}
            <div class="mb-8">
                <h1 class="text-3xl font-bold">Welcome to Bex, {{ Auth::user()->name }}!</h1>
                <p class="text-base-content/70 mt-2">Here’s a quick overview of what you can do. Let's get started.</p>
            </div>
            
            {{-- Grid for the feature cards --}}
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                
                {{-- Card 1: New Chat --}}
                <div class="card bg-base-200 shadow-xl border-2 border-primary">
                    <div class="card-body">
                        <div class="flex items-center gap-4 mb-2">
                            <i class="bi bi-chat-dots-fill text-primary text-3xl"></i>
                            <h2 class="card-title">Start a Conversation</h2>
                        </div>
                        <p>Begin a new chat with Bex to ask questions, get assistance, or brainstorm ideas.</p>
                        <div class="card-actions justify-start mt-4">
                            <a href="{{ route('chat.show') }}" class="btn btn-primary">
                                <i class="bi bi-plus-lg"></i> New Chat
                            </a>
                        </div>
                    </div>
                </div>
                
                {{-- Card 2: Workspace --}}
                <div class="card bg-base-200 shadow-xl">
                    <div class="card-body">
                        <div class="flex items-center gap-4 mb-2">
                            <i class="bi bi-person-workspace text-secondary text-3xl"></i>
                            <h2 class="card-title">Your Workspace</h2>
                        </div>
                        <p>Upload, manage, and share your personal files. Ask Bex to summarize or analyze your documents.</p>
                        <div class="card-actions justify-start mt-4">
                            <a href="{{ route('files.index') }}" class="btn btn-secondary">
                                <i class="bi bi-folder-fill"></i> Go to My Files
                            </a>
                        </div>
                    </div>
                </div>
                
                {{-- Card 3: Teams --}}
                <div class="card bg-base-200 shadow-xl">
                    <div class="card-body">
                        <div class="flex items-center gap-4 mb-2">
                            <i class="bi bi-people-fill text-accent text-3xl"></i>
                            <h2 class="card-title">Collaborate with Teams</h2>
                        </div>
                        <p>Create or join teams to collaborate with others. Engage in group chats and share files in a dedicated workspace.</p>
                        <div class="card-actions justify-start mt-4">
                            <a href="{{ route('teams.index') }}" class="btn btn-accent">
                                <i class="bi bi-people"></i> Manage Teams
                            </a>
                        </div>
                    </div>
                </div>
                
                {{-- Card 4: Utilities --}}
                <div class="card bg-base-200 shadow-xl">
                    <div class="card-body">
                        <div class="flex items-center gap-4 mb-2">
                            <i class="bi bi-tools text-info text-3xl"></i>
                            <h2 class="card-title">Powerful Utilities</h2>
                        </div>
                        <p>Use powerful tools to summarize documents, articles, or transcribe audio files with just a few clicks.</p>
                        <div class="card-actions justify-start mt-4 space-x-2">
                            <button class="btn btn-info btn-sm" id="summarizeButton"><i class="bi bi-file-text-fill"></i> Summarize</button>
                            <button class="btn btn-info btn-sm" id="transcribeButton"><i class="bi bi-mic-fill"></i> Transcribe</button>
                        </div>
                    </div>
                </div>
                
                {{-- Card 5: Profile --}}
                <div class="card bg-base-200 shadow-xl">
                    <div class="card-body">
                        <div class="flex items-center gap-4 mb-2">
                            <i class="bi bi-person-circle text-warning text-3xl"></i>
                            <h2 class="card-title">Your Profile</h2>
                        </div>
                        <p>Keep your personal information up to date and manage your account security settings.</p>
                        <div class="card-actions justify-start mt-4">
                            <a href="{{ route('profile.edit') }}" class="btn btn-warning">
                                <i class="bi bi-pencil-fill"></i> Edit Profile
                            </a>
                        </div>
                    </div>
                </div>
                
                {{-- Card 6: Subscription --}}
                <div class="card bg-base-200 shadow-xl">
                    <div class="card-body">
                        <div class="flex items-center gap-4 mb-2">
                            <i class="bi bi-credit-card-2-front-fill text-success text-3xl"></i>
                            <h2 class="card-title">Subscription</h2>
                        </div>
                        <p>Manage your billing details, view invoices, or make changes to your current subscription plan.</p>
                        <div class="card-actions justify-start mt-4">
                            <a href="{{ route('subscription.manage') }}" class="btn btn-success">
                                <i class="bi bi-gear-fill"></i> Manage Subscription
                            </a>
                        </div>
                    </div>
                </div>
            
            </div>
        
        </div>
    </div>
@endsection
