@php
// Apply proper naming for clarity
$userClient = $record;
$user = $userClient->user;
$client = $userClient->client;
$gravatar = getGravatarUrl($user->email ?? '');

// Helper function for gravatar
function getGravatarUrl(string $email): string
{
$hash = md5(strtolower(trim($email)));
return "https://www.gravatar.com/avatar/{$hash}?s=200&d=mp";
}

// Get all clients this user is assigned to
$allUserClients = \App\Models\UserClient::where('user_id', $user->id)->with('client')->get();

// Get user's activity logs using Spatie's package
$activities = \Spatie\Activitylog\Models\Activity::causedBy($user)->orderBy('created_at', 'desc')->take(10)->get();
@endphp

<x-filament-panels::page>
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-1 space-y-6">
            <!-- User Profile Card -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow overflow-hidden">
                <div
                    class="px-6 py-5 border-b border-gray-200 dark:border-gray-700 bg-gradient-to-r from-primary-500/10 to-primary-600/10 dark:from-primary-800/20 dark:to-primary-900/20">
                    <h2 class="text-lg font-bold text-gray-900 dark:text-white flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-primary-500" viewBox="0 0 20 20"
                            fill="currentColor">
                            <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z"
                                clip-rule="evenodd" />
                        </svg>
                        Employee Information
                    </h2>
                </div>

                <div class="p-6">
                    <div class="flex flex-col items-center text-center mb-6">
                        <div class="relative">
                            <img src="{{ $gravatar }}" alt="{{ $user->name ?? 'User' }}"
                                class="h-24 w-24 rounded-full object-cover border-4 border-primary-100 dark:border-primary-900 shadow-lg">
                            @if ($user->email_verified_at)
                            <span
                                class="absolute bottom-0 right-0 h-6 w-6 rounded-full bg-green-500 border-2 border-white dark:border-gray-800 flex items-center justify-center"
                                title="Verified Account">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-white" viewBox="0 0 20 20"
                                    fill="currentColor">
                                    <path fill-rule="evenodd"
                                        d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                        clip-rule="evenodd" />
                                </svg>
                            </span>
                            @endif
                        </div>
                        <h3 class="mt-4 text-xl font-bold text-gray-900 dark:text-white">
                            {{ $user->name ?? 'No Name Available' }}</h3>
                        <div
                            class="mt-1 text-sm text-gray-500 dark:text-gray-400 flex items-center justify-center gap-1">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                            {{ $user->email ?? 'No Email Available' }}
                        </div>
                    </div>

                    <!-- User Details -->
                    <div class="space-y-3 text-sm divide-y divide-gray-100 dark:divide-gray-700">
                        <div class="flex justify-between items-center py-2">
                            <div class="flex items-center text-gray-500 dark:text-gray-400 gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0" />
                                </svg>
                                Employee ID
                            </div>
                            <span class="font-medium text-gray-900 dark:text-white">#{{ $user->id }}</span>
                        </div>
                        <div class="flex justify-between items-center py-2">
                            <div class="flex items-center text-gray-500 dark:text-gray-400 gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                Joined Date
                            </div>
                            <span class="font-medium text-gray-900 dark:text-white">{{ $user->created_at ?
                                $user->created_at->format('M d, Y') : 'N/A' }}</span>
                        </div>
                        <div class="flex justify-between items-center py-2">
                            <div class="flex items-center text-gray-500 dark:text-gray-400 gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                </svg>
                                Total Assignments
                            </div>
                            <span class="font-medium text-gray-900 dark:text-white">{{ $allUserClients->count() }}
                                Clients</span>
                        </div>
                        <div class="flex justify-between items-center py-2">
                            <div class="flex items-center text-gray-500 dark:text-gray-400 gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                Last Activity
                            </div>
                            <span class="font-medium text-gray-900 dark:text-white">{{ $activities->isNotEmpty() ?
                                $activities->first()->created_at->diffForHumans() : 'No activity' }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Project Statistics -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow">
                <div
                    class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-gradient-to-r from-primary-500/10 to-primary-600/10 dark:from-primary-800/20 dark:to-primary-900/20">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-primary-500" viewBox="0 0 20 20"
                            fill="currentColor">
                            <path fill-rule="evenodd"
                                d="M5 3a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2V5a2 2 0 00-2-2H5zm9 4a1 1 0 10-2 0v6a1 1 0 102 0V7zm-3 2a1 1 0 10-2 0v4a1 1 0 102 0V9zm-3 3a1 1 0 10-2 0v1a1 1 0 102 0v-1z"
                                clip-rule="evenodd" />
                        </svg>
                        Project Statistics
                    </h3>
                </div>
                <div class="p-6">
                    <!-- Enhanced Statistics Grid -->
                    <div class="grid grid-cols-2 gap-4">
                        <div
                            class="bg-gradient-to-br from-blue-50 to-blue-100 dark:from-blue-900/30 dark:to-blue-800/30 rounded-lg p-4 border border-blue-200 dark:border-blue-800 shadow-sm hover:shadow-md transition-shadow">
                            <div class="flex items-center justify-between">
                                <div class="text-sm font-medium text-blue-700 dark:text-blue-300">Total Projects</div>
                                <div class="rounded-full bg-blue-200 dark:bg-blue-700 p-2">
                                    <svg xmlns="http://www.w3.org/2000/svg"
                                        class="h-5 w-5 text-blue-700 dark:text-blue-300" viewBox="0 0 20 20"
                                        fill="currentColor">
                                        <path fill-rule="evenodd"
                                            d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z"
                                            clip-rule="evenodd" />
                                    </svg>
                                </div>
                            </div>
                            <div class="mt-2 text-3xl font-bold text-blue-700 dark:text-blue-300">
                                {{ $stats['totalProjects'] }}</div>
                        </div>

                        <div
                            class="bg-gradient-to-br from-green-50 to-green-100 dark:from-green-900/30 dark:to-green-800/30 rounded-lg p-4 border border-green-200 dark:border-green-800 shadow-sm hover:shadow-md transition-shadow">
                            <div class="flex items-center justify-between">
                                <div class="text-sm font-medium text-green-700 dark:text-green-300">Active</div>
                                <div class="rounded-full bg-green-200 dark:bg-green-700 p-2">
                                    <svg xmlns="http://www.w3.org/2000/svg"
                                        class="h-5 w-5 text-green-700 dark:text-green-300" viewBox="0 0 20 20"
                                        fill="currentColor">
                                        <path fill-rule="evenodd"
                                            d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z"
                                            clip-rule="evenodd" />
                                    </svg>
                                </div>
                            </div>
                            <div class="mt-2 text-3xl font-bold text-green-700 dark:text-green-300">
                                {{ $stats['activeProjects'] }}</div>
                        </div>

                        <div
                            class="bg-gradient-to-br from-purple-50 to-purple-100 dark:from-purple-900/30 dark:to-purple-800/30 rounded-lg p-4 border border-purple-200 dark:border-purple-800 shadow-sm hover:shadow-md transition-shadow">
                            <div class="flex items-center justify-between">
                                <div class="text-sm font-medium text-purple-700 dark:text-purple-300">Completed</div>
                                <div class="rounded-full bg-purple-200 dark:bg-purple-700 p-2">
                                    <svg xmlns="http://www.w3.org/2000/svg"
                                        class="h-5 w-5 text-purple-700 dark:text-purple-300" viewBox="0 0 20 20"
                                        fill="currentColor">
                                        <path fill-rule="evenodd"
                                            d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                            clip-rule="evenodd" />
                                    </svg>
                                </div>
                            </div>
                            <div class="mt-2 text-3xl font-bold text-purple-700 dark:text-purple-300">
                                {{ $stats['completedProjects'] }}</div>
                        </div>

                        <div
                            class="bg-gradient-to-br from-red-50 to-red-100 dark:from-red-900/30 dark:to-red-800/30 rounded-lg p-4 border border-red-200 dark:border-red-800 shadow-sm hover:shadow-md transition-shadow">
                            <div class="flex items-center justify-between">
                                <div class="text-sm font-medium text-red-700 dark:text-red-300">Urgent</div>
                                <div class="rounded-full bg-red-200 dark:bg-red-700 p-2">
                                    <svg xmlns="http://www.w3.org/2000/svg"
                                        class="h-5 w-5 text-red-700 dark:text-red-300" viewBox="0 0 20 20"
                                        fill="currentColor">
                                        <path fill-rule="evenodd"
                                            d="M12.395 2.553a1 1 0 00-1.45-.385c-.345.23-.614.558-.822.88-.214.33-.403.713-.57 1.116-.334.804-.614 1.768-.84 2.734a31.365 31.365 0 00-.613 3.58 2.64 2.64 0 01-.945-1.067c-.328-.68-.398-1.534-.398-2.654A1 1 0 005.05 6.05 6.981 6.981 0 003 11a7 7 0 1011.95-4.95c-.592-.591-.98-.985-1.348-1.467-.363-.476-.724-1.063-1.207-2.03zM12.12 15.12A3 3 0 017 13s.879.5 2.5.5c0-1 .5-4 1.25-4.5.5 1 .786 1.293 1.371 1.879A2.99 2.99 0 0113 13a2.99 2.99 0 01-.879 2.121z"
                                            clip-rule="evenodd" />
                                    </svg>
                                </div>
                            </div>
                            <div class="mt-2 text-3xl font-bold text-red-700 dark:text-red-300">
                                {{ $stats['urgentProjects'] }}</div>
                        </div>
                    </div>

                    <!-- Progress Bar -->
                    <div class="mt-6">
                        <div class="flex justify-between items-center mb-1">
                            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Project Completion
                                Rate</span>
                            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                @php
                                $completionRate = $stats['totalProjects'] > 0
                                ? round(($stats['completedProjects'] / $stats['totalProjects']) * 100)
                                : 0;
                                @endphp
                                {{ $completionRate }}%
                            </span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2.5 dark:bg-gray-700">
                            <div class="bg-primary-600 h-2.5 rounded-full" style="width: {{ $completionRate }}%">
                            </div>
                        </div>
                    </div>

                    <!-- Enhanced Project Type Distribution -->
                    <div class="mt-6">
                        <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">Project Type Distribution
                        </h4>
                        <div class="grid grid-cols-3 gap-2">
                            @php
                            $projectTypes = $userProjects
                            ->filter(function ($up) {
                            return $up->project;
                            })
                            ->groupBy(function ($userProject) {
                            return $userProject->project->type ?? 'unknown';
                            });

                            $typeColors = [
                            'single' => [
                            'bg' => 'bg-blue-500',
                            'text' => 'text-blue-800 dark:text-blue-200',
                            'border' => 'border-blue-200 dark:border-blue-700',
                            'gradient' => 'from-blue-50 to-blue-100 dark:from-blue-900/30 dark:to-blue-800/30'
                            ],
                            'monthly' => [
                            'bg' => 'bg-green-500',
                            'text' => 'text-green-800 dark:text-green-200',
                            'border' => 'border-green-200 dark:border-green-700',
                            'gradient' => 'from-green-50 to-green-100 dark:from-green-900/30 dark:to-green-800/30'
                            ],
                            'yearly' => [
                            'bg' => 'bg-purple-500',
                            'text' => 'text-purple-800 dark:text-purple-200',
                            'border' => 'border-purple-200 dark:border-purple-700',
                            'gradient' => 'from-purple-50 to-purple-100 dark:from-purple-900/30 dark:to-purple-800/30'
                            ],
                            'unknown' => [
                            'bg' => 'bg-gray-500',
                            'text' => 'text-gray-800 dark:text-gray-200',
                            'border' => 'border-gray-200 dark:border-gray-700',
                            'gradient' => 'from-gray-50 to-gray-100 dark:from-gray-900/30 dark:to-gray-800/30'
                            ],
                            ];

                            $total = $stats['totalProjects'];
                            @endphp

                            @foreach ($projectTypes as $type => $projects)
                            @php
                            $count = $projects->count();
                            $percentage = $total > 0 ? round(($count / $total) * 100) : 0;
                            $color = $typeColors[$type] ?? $typeColors['unknown'];
                            @endphp
                            <div
                                class="bg-gradient-to-br {{ $color['gradient'] }} p-3 rounded-lg border {{ $color['border'] }} shadow-sm hover:shadow-md transition-shadow text-center">
                                <div class="{{ $color['text'] }} font-medium">{{ ucfirst($type) }}</div>
                                <div class="mt-1 text-lg font-semibold text-gray-800 dark:text-gray-200">
                                    {{ $count }}</div>
                                <div class="mt-1 text-xs {{ $color['text'] }}">{{ $percentage }}%</div>
                                <div class="mt-1 w-full bg-gray-200 dark:bg-gray-800 rounded-full h-1.5">
                                    <div class="{{ $color['bg'] }} h-1.5 rounded-full"
                                        style="width: {{ $percentage }}%">
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="lg:col-span-2">
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow h-full">
                <!-- Tabs Header with counters -->
                <div class="flex border-b border-gray-200 dark:border-gray-700 overflow-x-auto">
                    <button
                        class="tab-btn active flex-1 inline-flex justify-center items-center gap-2 py-4 px-1 text-center border-b-2 border-primary-500 font-medium text-primary-600 dark:text-primary-400 whitespace-nowrap"
                        data-tab="projects">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd"
                                d="M3 5a2 2 0 012-2h10a2 2 0 012 2v10a2 2 0 01-2 2H5a2 2 0 01-2-2V5zm11 1H6v8l4-2 4 2V6z"
                                clip-rule="evenodd" />
                        </svg>
                        Projects
                        <span
                            class="inline-flex items-center justify-center w-5 h-5 ml-1 text-xs font-medium text-primary-600 bg-primary-100 rounded-full dark:bg-primary-800 dark:text-primary-300">
                            {{ $stats['totalProjects'] }}
                        </span>
                    </button>
                    <button
                        class="tab-btn flex-1 inline-flex justify-center items-center gap-2 py-4 px-1 text-center border-b-2 border-transparent hover:text-gray-600 hover:border-gray-300 font-medium text-gray-500 dark:text-gray-400 dark:hover:text-gray-300 whitespace-nowrap"
                        data-tab="clients">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path
                                d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v3h8v-3zM6 8a2 2 0 11-4 0 2 2 0 014 0zM16 18v-3a5.972 5.972 0 00-.75-2.906A3.005 3.005 0 0119 15v3h-3zM4.75 12.094A5.973 5.973 0 004 15v3H1v-3a3 3 0 013.75-2.906z" />
                        </svg>
                        Clients
                        <span
                            class="inline-flex items-center justify-center w-5 h-5 ml-1 text-xs font-medium text-gray-600 bg-gray-100 rounded-full dark:bg-gray-700 dark:text-gray-300">
                            {{ $allUserClients->count() }}
                        </span>
                    </button>
                    <button
                        class="tab-btn flex-1 inline-flex justify-center items-center gap-2 py-4 px-1 text-center border-b-2 border-transparent hover:text-gray-600 hover:border-gray-300 font-medium text-gray-500 dark:text-gray-400 dark:hover:text-gray-300 whitespace-nowrap"
                        data-tab="activity">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd"
                                d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z"
                                clip-rule="evenodd" />
                        </svg>
                        Activity
                        <span
                            class="inline-flex items-center justify-center w-5 h-5 ml-1 text-xs font-medium text-gray-600 bg-gray-100 rounded-full dark:bg-gray-700 dark:text-gray-300">
                            {{ $activities->count() }}
                        </span>
                    </button>
                </div>

                <!-- Tab Contents with enhanced styling -->
                <div class="tab-content" id="projects-content">
                    <div class="p-4">
                        <!-- Project Search -->
                        <div class="flex flex-col sm:flex-row space-y-2 sm:space-y-0 sm:space-x-2 mb-4">
                            <div class="relative flex-1">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg"
                                        viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd"
                                            d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z"
                                            clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <input type="text" id="project-search"
                                    class="bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 text-gray-900 dark:text-gray-100 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full pl-10 p-2.5"
                                    placeholder="Search projects or clients...">
                            </div>
                        </div>

                        <div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-700">
                            @if ($userProjects->count() > 0)
                            @php
                            $groupedProjects = $userProjects->groupBy(function($userProject) {
                            return $userProject->project ? $userProject->project->status : 'unknown';
                            });

                            // Define status order and labels
                            $statusOrder = ['in_progress', 'review', 'analysis', 'draft', 'completed', 'canceled'];
                            $statusLabels = [
                            'in_progress' => 'In Progress',
                            'review' => 'Under Review',
                            'analysis' => 'In Analysis',
                            'draft' => 'Draft',
                            'completed' => 'Completed',
                            'canceled' => 'Canceled',
                            'unknown' => 'Unknown'
                            ];
                            @endphp

                            @foreach($statusOrder as $status)
                            @if($groupedProjects->has($status) && $groupedProjects[$status]->count() > 0)
                            <div class="mb-4">
                                <div
                                    class="bg-gray-50 dark:bg-gray-800 px-6 py-3 border-b border-gray-200 dark:border-gray-700">
                                    <h3 class="text-sm font-medium text-gray-900 dark:text-white">
                                        {{ $statusLabels[$status] }} ({{ $groupedProjects[$status]->count() }})
                                    </h3>
                                </div>
                                <!-- Modified table structure with removed columns -->
                                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                    <thead class="bg-gray-50 dark:bg-gray-800">
                                        <tr>
                                            <th scope="col"
                                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                                Project
                                            </th>
                                            <th scope="col"
                                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                                Client
                                            </th>
                                            <th scope="col"
                                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                                Due Date
                                            </th>
                                            <th scope="col"
                                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                                Documents
                                            </th>
                                            <th scope="col"
                                                class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                                Actions
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody
                                        class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                        @foreach($groupedProjects[$status] as $userProject)
                                        @if ($userProject->project)
                                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 project-row"
                                            data-status="{{ $userProject->project->status }}">
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="flex items-center">
                                                    <div class="flex-shrink-0 h-10 w-10 flex items-center justify-center rounded-md 
                                @if ($userProject->project->type === 'single') bg-blue-100 text-blue-600 dark:bg-blue-900 dark:text-blue-300
                                @elseif($userProject->project->type === 'monthly') bg-green-100 text-green-600 dark:bg-green-900 dark:text-green-300 
                                @elseif($userProject->project->type === 'yearly') bg-purple-100 text-purple-600 dark:bg-purple-900 dark:text-purple-300
                                @else bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300 @endif">
                                                        @if ($userProject->project->type === 'single')
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5"
                                                            fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="2"
                                                                d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                                        </svg>
                                                        @elseif($userProject->project->type === 'monthly')
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5"
                                                            fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="2"
                                                                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                        </svg>
                                                        @elseif($userProject->project->type === 'yearly')
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5"
                                                            fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="2"
                                                                d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                                        </svg>
                                                        @else
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5"
                                                            fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="2"
                                                                d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                                        </svg>
                                                        @endif
                                                    </div>
                                                    <div class="ml-4">
                                                        <div class="text-sm font-medium text-gray-900 dark:text-white">
                                                            {{ $userProject->project->name }}
                                                        </div>
                                                        <div
                                                            class="text-xs text-gray-500 dark:text-gray-400 flex items-center gap-1">
                                                            <span
                                                                class="uppercase font-medium text-xs @if ($userProject->role === 'direktur') text-indigo-600 dark:text-indigo-400 @elseif($userProject->role === 'person-in-charge') text-amber-600 dark:text-amber-400 @else text-teal-600 dark:text-teal-400 @endif">
                                                                {{ $userProject->role }}
                                                            </span>
                                                            <span class="text-gray-300 dark:text-gray-600">•</span>
                                                            <span>{{ ucfirst($userProject->project->type) }}</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="flex items-center">
                                                    @if ($userProject->project && $userProject->project->client &&
                                                    $userProject->project->client->logo)
                                                    <img src="{{ Storage::url($userProject->project->client->logo) }}"
                                                        alt="{{ $userProject->project->client->name ?? 'Client' }}"
                                                        class="h-6 w-6 rounded-full mr-2">
                                                    @else
                                                    <div
                                                        class="h-6 w-6 rounded-full bg-gray-200 dark:bg-gray-700 flex items-center justify-center mr-2">
                                                        <span
                                                            class="text-xs font-medium text-gray-600 dark:text-gray-300">
                                                            {{ substr($userProject->project->client->name ?? 'NA', 0, 2)
                                                            }}
                                                        </span>
                                                    </div>
                                                    @endif
                                                    <span class="text-sm text-gray-900 dark:text-white">
                                                        {{ $userProject->project->client->name ?? 'N/A' }}
                                                    </span>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                @php
                                                $dueDate = \Carbon\Carbon::parse($userProject->project->due_date);
                                                $today = \Carbon\Carbon::today();
                                                $daysLeft = $today->diffInDays($dueDate, false);

                                                $dueDateClass = 'text-gray-700 dark:text-gray-300';
                                                $bgClass = '';

                                                if ($daysLeft < 0) {
                                                    $dueDateClass='text-red-600 dark:text-red-400 font-medium' ;
                                                    $bgClass='bg-red-50 dark:bg-red-900/20' ; } elseif ($daysLeft <=3) {
                                                    $dueDateClass='text-amber-600 dark:text-amber-400 font-medium' ;
                                                    $bgClass='bg-amber-50 dark:bg-amber-900/20' ; } @endphp <div
                                                    class="flex flex-col items-center">
                                                    <div
                                                        class="text-sm {{ $dueDateClass }} {{ $bgClass }} py-1 px-2 rounded-md">
                                                        {{ $dueDate->format('M d, Y') }}
                                                    </div>
                                                    @if ($userProject->project->status !== 'completed' &&
                                                    $userProject->project->status !== 'canceled')
                                                    <div class="text-xs {{ $dueDateClass }} mt-1">
                                                        @if ($daysLeft < 0) <span class="flex items-center">
                                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 mr-1"
                                                                fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                    stroke-width="2"
                                                                    d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                            </svg>
                                                            {{ abs($daysLeft) }} {{ abs($daysLeft) > 1 ? 'days' : 'day'
                                                            }} overdue
                                                            </span>
                                                            @elseif($daysLeft == 0)
                                                            <span class="flex items-center">
                                                                <svg xmlns="http://www.w3.org/2000/svg"
                                                                    class="h-3 w-3 mr-1" fill="none" viewBox="0 0 24 24"
                                                                    stroke="currentColor">
                                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                                        stroke-width="2"
                                                                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                                </svg>
                                                                Due today
                                                            </span>
                                                            @else
                                                            <span class="flex items-center">
                                                                <svg xmlns="http://www.w3.org/2000/svg"
                                                                    class="h-3 w-3 mr-1" fill="none" viewBox="0 0 24 24"
                                                                    stroke="currentColor">
                                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                                        stroke-width="2"
                                                                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                                </svg>
                                                                {{ $daysLeft }} {{ $daysLeft > 1 ? 'days' : 'day' }}
                                                                left
                                                            </span>
                                                            @endif
                                                    </div>
                                                    @endif
                            </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @php
                                // Variables for user-specific document counts
                                $userDocumentCount = 0;
                                $totalRequiredDocuments = 0;
                                $totalProjectDocuments = 0;
                                $userPercentage = 0;
                                $progressColor = 'bg-gray-500';

                                // Array to store submitted document filenames
                                $submittedFileNames = [];

                                // Safely handle possible null values
                                try {
                                // Check if relationship exists and is loaded
                                if ($userProject->project) {
                                // Get project steps safely
                                $projectSteps = $userProject->project->steps;

                                if ($projectSteps && $projectSteps->count() > 0) {
                                $projectStepIds = $projectSteps->pluck('id')->toArray();

                                // Find all required documents for these steps
                                $requiredDocuments = \App\Models\RequiredDocument::whereIn('project_step_id',
                                $projectStepIds)->get();
                                $totalRequiredDocuments = $requiredDocuments->count();

                                if ($totalRequiredDocuments > 0) {
                                $requiredDocumentIds = $requiredDocuments->pluck('id')->toArray();

                                // Get documents submitted by this user with filepath info
                                $userSubmittedDocs = \App\Models\SubmittedDocument::whereIn('required_document_id',
                                $requiredDocumentIds)
                                ->where('user_id', $user->id)
                                ->get(['file_path']);

                                $userDocumentCount = $userSubmittedDocs->count();

                                // Extract filenames from file paths
                                foreach ($userSubmittedDocs as $doc) {
                                $pathParts = explode('/', $doc->file_path);
                                $fileName = end($pathParts);
                                $submittedFileNames[] = $fileName;
                                }

                                // Count total submitted documents for this project (regardless of user)
                                $totalProjectDocuments = \App\Models\SubmittedDocument::whereIn('required_document_id',
                                $requiredDocumentIds)->count();

                                // Calculate percentage for progress bar (fixed division by zero)
                                $userPercentage = $totalProjectDocuments > 0 ?
                                round(($userDocumentCount / $totalProjectDocuments) * 100) : 0;
                                }
                                }
                                }
                                } catch (\Exception $e) {
                                // If any error occurs, we'll keep the default values
                                $userDocumentCount = 0;
                                $totalRequiredDocuments = 0;
                                $userPercentage = 0;
                                $totalProjectDocuments = 0;
                                $submittedFileNames = [];
                                }

                                // Determine color based on percentage
                                if ($userPercentage >= 100) {
                                $progressColor = 'bg-green-500';
                                } elseif ($userPercentage >= 50) {
                                $progressColor = 'bg-blue-500';
                                } elseif ($userPercentage >= 25) {
                                $progressColor = 'bg-amber-500';
                                } elseif ($userPercentage > 0) {
                                $progressColor = 'bg-red-500';
                                }

                                // Fix the tooltip text - replace newlines with commas
                                $tooltipText = !empty($submittedFileNames) ?
                                implode(', ', $submittedFileNames) : 'No documents submitted';
                                @endphp

                                <div class="flex flex-col">
                                    <div class="flex items-center justify-between mb-1">
                                        <div class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                            <span class="cursor-help" title="{{ $tooltipText }}">
                                                {{ $userDocumentCount }} / {{ $totalProjectDocuments }}
                                                <span class="ml-1 text-xs text-gray-500 dark:text-gray-400">({{
                                                    $totalRequiredDocuments }} req.)</span>
                                            </span>
                                        </div>
                                        <span class="text-xs text-gray-500 dark:text-gray-400">{{ $userPercentage
                                            }}%</span>
                                    </div>
                                    <div class="w-full bg-gray-200 rounded-full h-1.5 dark:bg-gray-700">
                                        <div class="{{ $progressColor }} h-1.5 rounded-full"
                                            style="width: {{ $userPercentage }}%"></div>
                                    </div>
                                    @if (!empty($submittedFileNames))
                                    <div class="mt-1">
                                        <button type="button"
                                            class="text-xs text-primary-600 dark:text-primary-400 hover:underline"
                                            onclick="alert('{{ $tooltipText }}')">
                                            View files
                                        </button>
                                    </div>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex justify-end space-x-2">
                                    <a href="{{ route('filament.admin.resources.projects.view', $userProject->project) }}"
                                        class="text-primary-600 hover:text-primary-900 dark:text-primary-400 dark:hover:text-primary-300 bg-primary-50 dark:bg-primary-900/20 hover:bg-primary-100 dark:hover:bg-primary-800/30 p-1.5 rounded-lg transition-colors">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                            viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                    </a>
                                </div>
                            </td>
                            </tr>
                            @endif
                            @endforeach
                            </tbody>
                            </table>
                        </div>
                        @endif
                        @endforeach
                        @else
                        <div class="flex justify-center items-center py-8 text-gray-500 dark:text-gray-400">
                            <div class="text-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="mx-auto h-12 w-12 text-gray-400"
                                    fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                </svg>
                                <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">No Projects Assigned
                                </h3>
                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">This user doesn't have any
                                    projects assigned yet.</p>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Activity Tab Content -->
            <div class="tab-content hidden" id="activity-content">
                <div class="p-4">
                    @if ($activities->count() > 0)
                    <div class="flow-root">
                        <ul role="list" class="-mb-8">
                            @foreach ($activities as $index => $activity)
                            <li>
                                <div class="relative pb-8">
                                    @if ($index < $activities->count() - 1)
                                        <span
                                            class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-200 dark:bg-gray-700"
                                            aria-hidden="true"></span>
                                        @endif
                                        <div class="relative flex space-x-3">
                                            <div>
                                                @php
                                                // Determine the activity type and appropriate styling
                                                $activityType = strtolower($activity->description ?? '');

                                                if (str_contains($activityType, 'create')) {
                                                $bgColor = 'bg-green-500';
                                                $icon =
                                                '
                                                <path fill-rule="evenodd"
                                                    d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z"
                                                    clip-rule="evenodd" />';
                                                } elseif (str_contains($activityType, 'update')) {
                                                $bgColor = 'bg-blue-500';
                                                $icon =
                                                '
                                                <path
                                                    d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
                                                ';
                                                } elseif (str_contains($activityType, 'delete')) {
                                                $bgColor = 'bg-red-500';
                                                $icon =
                                                '
                                                <path fill-rule="evenodd"
                                                    d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z"
                                                    clip-rule="evenodd" />';
                                                } elseif (
                                                str_contains($activityType, 'comment') ||
                                                str_contains($activityType, 'message')
                                                ) {
                                                $bgColor = 'bg-purple-500';
                                                $icon =
                                                '
                                                <path fill-rule="evenodd"
                                                    d="M18 10c0 3.866-3.582 7-8 7a8.841 8.841 0 01-4.083-.98L2 17l1.338-3.123C2.493 12.767 2 11.434 2 10c0-3.866 3.582-7 8-7s8 3.134 8 7zM7 9H5v2h2V9zm8 0h-2v2h2V9zM9 9h2v2H9V9z"
                                                    clip-rule="evenodd" />';
                                                } elseif (
                                                str_contains($activityType, 'upload') ||
                                                str_contains($activityType, 'document')
                                                ) {
                                                $bgColor = 'bg-amber-500';
                                                $icon =
                                                '
                                                <path fill-rule="evenodd"
                                                    d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z"
                                                    clip-rule="evenodd" />';
                                                } elseif (
                                                str_contains($activityType, 'login') ||
                                                str_contains($activityType, 'logged in')
                                                ) {
                                                $bgColor = 'bg-indigo-500';
                                                $icon =
                                                '
                                                <path fill-rule="evenodd"
                                                    d="M3 3a1 1 0 011 1v12a1 1 0 11-2 0V4a1 1 0 011-1zm7.707 3.293a1 1 0 010 1.414L9.414 9H17a1 1 0 110 2H9.414l1.293 1.293a1 1 0 01-1.414 1.414l-3-3a1 1 0 010-1.414l3-3a1 1 0 011.414 0z"
                                                    clip-rule="evenodd" />';
                                                } else {
                                                $bgColor = 'bg-gray-500';
                                                $icon =
                                                '
                                                <path fill-rule="evenodd"
                                                    d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z"
                                                    clip-rule="evenodd" />';
                                                }

                                                // Get the model type in a more human-readable format
                                                $subjectType = $activity->subject_type ?? '';
                                                $modelName = '';

                                                if (!empty($subjectType)) {
                                                $parts = explode('\\', $subjectType);
                                                $modelName = end($parts);
                                                }
                                                @endphp
                                                <span
                                                    class="{{ $bgColor }} h-8 w-8 rounded-full flex items-center justify-center ring-8 ring-white dark:ring-gray-800">
                                                    <svg class="h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg"
                                                        viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                                        {!! $icon !!}
                                                    </svg>
                                                </span>
                                            </div>
                                            <div class="min-w-0 flex-1 pt-1.5 flex justify-between space-x-4">
                                                <div>
                                                    <p class="text-sm text-gray-800 dark:text-gray-200">
                                                        {{ ucfirst($activity->description) }}
                                                        @if ($modelName)
                                                        <span class="font-medium">{{ $modelName }}</span>
                                                        @endif
                                                        @if ($activity->subject_id)
                                                        <span class="text-gray-500 dark:text-gray-400">#{{
                                                            $activity->subject_id }}</span>
                                                        @endif
                                                    </p>
                                                    @if (!empty($activity->properties))
                                                    @php
                                                    $properties = $activity->properties->toArray();
                                                    $attributes = $properties['attributes'] ?? [];
                                                    $old = $properties['old'] ?? [];

                                                    // Limit to a few key attributes for display
                                                    $attributesToShow = array_slice($attributes, 0, 3);
                                                    @endphp
                                                    @if (count($attributesToShow) > 0)
                                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                                        @foreach ($attributesToShow as $key => $value)
                                                        @if (is_string($value) || is_numeric($value))
                                                        <span class="inline-flex items-center mr-2">
                                                            <span class="font-medium">{{
                                                                Str::title(str_replace('_', '
                                                                ', $key)) }}:</span>
                                                            <span class="ml-1">{{ Str::limit($value, 30)
                                                                }}</span>
                                                        </span>
                                                        @endif
                                                        @endforeach
                                                    </p>
                                                    @endif
                                                    @endif
                                                </div>
                                                <div
                                                    class="text-right text-xs text-gray-500 dark:text-gray-400 whitespace-nowrap">
                                                    <div>{{ $activity->created_at->format('M d, Y') }}</div>
                                                    <div>{{ $activity->created_at->format('h:i A') }}</div>
                                                </div>
                                            </div>
                                        </div>
                                </div>
                            </li>
                            @endforeach
                        </ul>

                        @if ($activities->count() >= 10)
                        <div class="mt-4 text-center">
                            <a href="#"
                                class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-primary-700 bg-primary-100 hover:bg-primary-200 dark:text-primary-300 dark:bg-primary-900/30 dark:hover:bg-primary-800/40 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 dark:focus:ring-offset-gray-800">
                                View more activity
                                <svg xmlns="http://www.w3.org/2000/svg" class="ml-1 h-5 w-5" viewBox="0 0 20 20"
                                    fill="currentColor">
                                    <path fill-rule="evenodd"
                                        d="M10.293 5.293a1 1 0 011.414 0l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414-1.414L12.586 11H5a1 1 0 110-2h7.586l-2.293-2.293a1 1 0 010-1.414z"
                                        clip-rule="evenodd" />
                                </svg>
                            </a>
                        </div>
                        @endif
                    </div>
                    @else
                    <div class="flex justify-center items-center py-8 text-gray-500 dark:text-gray-400">
                        <div class="text-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="mx-auto h-12 w-12 text-gray-400" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">No Activity Yet
                            </h3>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">This user doesn't have any
                                recent activity.</p>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-filament-panels::page>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
            // Tab functionality
            const tabBtns = document.querySelectorAll('.tab-btn');
            const tabContents = document.querySelectorAll('.tab-content');

            tabBtns.forEach(tab => {
                tab.addEventListener('click', () => {
                    // Remove active class from all tabs
                    tabBtns.forEach(btn => {
                        btn.classList.remove('active', 'border-primary-500',
                            'text-primary-600', 'dark:text-primary-400');
                        btn.classList.add('border-transparent', 'text-gray-500',
                            'dark:text-gray-400', 'hover:text-gray-600',
                            'hover:border-gray-300', 'dark:hover:text-gray-300');
                    });

                    // Hide all tab contents
                    tabContents.forEach(content => {
                        content.classList.add('hidden');
                    });

                    // Add active class to clicked tab
                    tab.classList.add('active', 'border-primary-500', 'text-primary-600',
                        'dark:text-primary-400');
                    tab.classList.remove('border-transparent', 'text-gray-500',
                        'dark:text-gray-400', 'hover:text-gray-600', 'hover:border-gray-300',
                        'dark:hover:text-gray-300');

                    // Show corresponding tab content
                    const tabId = tab.getAttribute('data-tab');
                    document.getElementById(tabId + '-content').classList.remove('hidden');
                });
            });

            // Updated search functionality
            const projectSearch = document.getElementById('project-search');
            if (projectSearch) {
                projectSearch.addEventListener('input', function() {
                    const searchValue = this.value.toLowerCase();
                    const projectRows = document.querySelectorAll('.project-row');

                    projectRows.forEach(row => {
                        const projectName = row.querySelector('td:first-child').textContent.toLowerCase();
                        const clientName = row.querySelector('td:nth-child(2)').textContent.toLowerCase();
                        
                        if (projectName.includes(searchValue) || clientName.includes(searchValue)) {
                            row.closest('tr').style.display = '';
                        } else {
                            row.closest('tr').style.display = 'none';
                        }
                    });
                });
            }
        });
</script>
@endpush