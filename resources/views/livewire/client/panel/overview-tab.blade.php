{{-- resources/views/livewire/client/panel/overview-tab.blade.php --}}
{{--
Aesthetic: Vercel Web Interface Guidelines Strict Compliance
Guidelines: Minimalist, standard sizing, tabular-nums, professional monochrome palette, native-feeling interactive
states, zero oversized components, strict focus visibility.
--}}
<div class="max-w-7xl mx-auto space-y-8 pb-12 antialiased text-slate-900 dark:text-slate-100" x-data="{ 
        mounted: false 
    }" x-init="setTimeout(() => mounted = true, 50)">

    @if($clients->isEmpty())
        {{-- Empty State: Unlinked Account --}}
        <div class="rounded-lg border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-6 shadow-sm"
            x-show="mounted" x-transition.opacity.duration.300ms>
            <div class="flex items-start gap-4">
                <div class="mt-1 flex-shrink-0">
                    <x-heroicon-o-exclamation-circle class="h-5 w-5 text-amber-500" />
                </div>
                <div class="flex-1">
                    <h3 class="text-sm font-semibold">Account Not Linked</h3>
                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                        Your account is not currently associated with any company profiles. An administrator must grant you
                        access before you can view the dashboard.
                    </p>
                    <div class="mt-4">
                        <a href="mailto:admin@example.com"
                            class="inline-flex items-center gap-2 rounded-md bg-slate-900 px-3 py-2 text-sm font-medium text-white shadow-sm hover:bg-slate-800 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-900 focus-visible:ring-offset-2 dark:bg-white dark:text-slate-900 dark:hover:bg-slate-100 dark:focus-visible:ring-white transition-colors">
                            Contact Administrator
                        </a>
                    </div>
                </div>
            </div>
        </div>
    @else

        {{-- HEADER SECTION --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4" x-show="mounted"
            x-transition.opacity.duration.300ms>

            <div class="flex items-center gap-4">
                @if($selectedClient && $selectedClient->logo)
                    <div
                        class="flex-shrink-0 h-10 w-10 rounded-md overflow-hidden border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-950 flex items-center justify-center">
                        <img src="{{ Storage::url($selectedClient->logo) }}" alt="{{ $selectedClient->name }}"
                            class="max-h-full max-w-full object-contain">
                    </div>
                @else
                    <div
                        class="flex-shrink-0 h-10 w-10 rounded-md border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-900 flex items-center justify-center text-slate-400">
                        <x-heroicon-o-building-office-2 class="h-5 w-5" />
                    </div>
                @endif

                <div>
                    <h1 class="text-xl font-semibold tracking-tight text-slate-900 dark:text-white flex items-center gap-2">
                        {{ $selectedClient ? $selectedClient->name : 'Dashboard Overview' }}
                        @if($selectedClient)
                            <span
                                class="inline-flex items-center rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-medium text-slate-800 dark:bg-slate-800 dark:text-slate-200 border border-slate-200 dark:border-slate-700">
                                Active
                            </span>
                        @endif
                    </h1>
                    <p class="text-sm text-slate-500 dark:text-slate-400">Management & Operations Summary</p>
                </div>
            </div>

            <div class="flex items-center gap-3">
                @if($clients->count() > 1)
                    <div class="relative">
                        <select wire:model.live="selectedClientId" id="clientSelect" aria-label="Select Client"
                            class="block w-full rounded-md border-slate-300 py-1.5 pl-3 pr-10 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-slate-700 dark:bg-slate-900 dark:text-white min-w-[200px] shadow-sm cursor-pointer disabled:opacity-50 transition-colors">
                            @foreach($clients as $client)
                                <option value="{{ $client->id }}">{{ $client->name }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif

                <button wire:click="refresh"
                    class="inline-flex items-center justify-center rounded-md border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 px-3 py-1.5 text-sm font-medium text-slate-700 dark:text-slate-300 shadow-sm hover:bg-slate-50 dark:hover:bg-slate-800 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 focus-visible:ring-offset-2 transition-colors disabled:opacity-50">
                    <x-heroicon-o-arrow-path class="mr-1.5 h-4 w-4 text-slate-400" />
                    Refresh
                </button>
            </div>
        </div>


        {{-- DOCUMENT ALERTS - Inline, Clean Banners --}}
        <div class="space-y-3" x-show="mounted" x-transition.opacity.duration.300ms>
            {{-- Pending Alert --}}
            @if($pendingDocuments->count() > 0)
                <div
                    class="rounded-md border-l-4 border-amber-500 bg-amber-50/50 dark:bg-amber-900/10 p-4 ring-1 ring-inset ring-amber-500/20">
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <x-heroicon-s-exclamation-triangle class="h-5 w-5 text-amber-500" />
                        </div>
                        <div class="ml-3 flex-1 md:flex md:justify-between">
                            <div>
                                <h3 class="text-sm font-medium text-amber-800 dark:text-amber-300">Action Required: Upload
                                    Documents</h3>
                                <p class="mt-1 text-sm text-amber-700 dark:text-amber-400/80">
                                    You have {{ $pendingDocuments->count() }} action items requiring file uploads.
                                </p>
                            </div>
                            <div class="mt-3 text-sm md:ml-6 md:mt-0 flex flex-col justify-center">
                                <button
                                    class="font-medium text-amber-800 dark:text-amber-300 hover:text-amber-600 dark:hover:text-amber-200 whitespace-nowrap focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-amber-500 rounded-sm px-1 -mx-1 transition-colors">
                                    View requirements &rarr;
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="mt-4 border-t border-amber-200/50 dark:border-amber-800/50 pt-3">
                        <ul class="space-y-1.5">
                            @foreach($pendingDocuments->take(3) as $doc)
                                <li class="flex items-center justify-between text-sm">
                                    <span class="text-amber-800 dark:text-amber-300 flex items-center gap-2 truncate pr-4">
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500 flex-shrink-0"></span>
                                        <span class="truncate">{{ $doc['name'] }}</span>
                                        @if($doc['is_required'])
                                            <span
                                                class="inline-flex flex-shrink-0 items-center rounded-md bg-amber-100 dark:bg-amber-900/40 px-1.5 py-0.5 text-xs font-medium text-amber-800 dark:text-amber-300 border border-amber-200 dark:border-amber-800">Required</span>
                                        @endif
                                    </span>
                                    @if($doc['due_date'])
                                        <span
                                            class="flex-shrink-0 text-amber-700 dark:text-amber-400 font-variant-numeric: tabular-nums">
                                            Due: {{ \Carbon\Carbon::parse($doc['due_date'])->format('M d') }}
                                        </span>
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif

            {{-- Rejected Alert --}}
            @if($rejectedDocuments->count() > 0)
                <div
                    class="rounded-md border-l-4 border-red-500 bg-red-50/50 dark:bg-red-900/10 p-4 ring-1 ring-inset ring-red-500/20">
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <x-heroicon-s-x-circle class="h-5 w-5 text-red-500" />
                        </div>
                        <div class="ml-3 flex-1">
                            <h3 class="text-sm font-medium text-red-800 dark:text-red-300">Documents Rejected</h3>
                            <div class="mt-2 text-sm text-red-700 dark:text-red-400/90 space-y-3">
                                @foreach($rejectedDocuments->take(3) as $doc)
                                    <div class="flex flex-col sm:flex-row sm:items-start gap-1 sm:gap-4">
                                        <span class="font-medium min-w-[120px]">{{ $doc['name'] }}:</span>
                                        <span
                                            class="text-slate-900 dark:text-slate-100 bg-white/50 dark:bg-black/20 px-2 py-0.5 rounded text-xs border border-red-200 dark:border-red-800 shadow-sm">{{ $doc['admin_notes'] ?? 'Please review and re-upload' }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>


        {{-- STATS GRID - Minimalist Data Density --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4" x-show="mounted" x-transition.opacity.duration.300ms>

            {{-- Proyek Aktif --}}
            <div class="rounded-lg border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-5 shadow-sm">
                <div class="flex items-center gap-2 text-slate-500 dark:text-slate-400 mb-2">
                    <x-heroicon-o-folder class="h-4 w-4" />
                    <h3 class="text-xs font-medium uppercase tracking-wider">Active Projects</h3>
                </div>
                <div class="flex items-baseline gap-2">
                    <span
                        class="text-2xl font-semibold text-slate-900 dark:text-white tabular-nums">{{ $projectStats['active'] }}</span>
                    <span class="text-sm text-slate-500 tabular-nums">of {{ $projectStats['total'] }}</span>
                </div>
                <p class="mt-1 flex items-center gap-1.5 text-xs text-slate-500">
                    <span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span> In Progress
                </p>
            </div>

            {{-- Proyek Selesai --}}
            <div class="rounded-lg border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-5 shadow-sm">
                <div class="flex items-center gap-2 text-slate-500 dark:text-slate-400 mb-2">
                    <x-heroicon-o-check-circle class="h-4 w-4" />
                    <h3 class="text-xs font-medium uppercase tracking-wider">Completed</h3>
                </div>
                <div class="flex items-baseline gap-2">
                    <span
                        class="text-2xl font-semibold text-slate-900 dark:text-white tabular-nums">{{ $projectStats['completed'] }}</span>
                    @if($projectStats['pending'] > 0)
                        <span
                            class="text-xs font-medium text-slate-500 border border-slate-200 dark:border-slate-700 rounded bg-slate-50 dark:bg-slate-800 px-1.5 py-0.5 tabular-nums">{{ $projectStats['pending'] }}
                            pending</span>
                    @endif
                </div>
                <p class="mt-1 flex items-center gap-1.5 text-xs text-slate-500">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Delivered successfully
                </p>
            </div>

            {{-- Laporan Pajak --}}
            <div class="rounded-lg border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-5 shadow-sm">
                <div class="flex items-center gap-2 text-slate-500 dark:text-slate-400 mb-2">
                    <x-heroicon-o-chart-bar class="h-4 w-4" />
                    <h3 class="text-xs font-medium uppercase tracking-wider">Tax Reports</h3>
                </div>
                <div class="flex items-baseline gap-2">
                    <span
                        class="text-2xl font-semibold text-slate-900 dark:text-white tabular-nums">{{ $taxReportStats['reported'] }}</span>
                </div>
                <div class="mt-2 flex items-center gap-2">
                    <div class="h-1 flex-1 rounded-full bg-slate-100 dark:bg-slate-800 overflow-hidden">
                        <div class="h-full bg-slate-400 dark:bg-slate-500 transition-all"
                            style="width: {{ $taxReportStats['completion_percentage'] }}%"></div>
                    </div>
                    <span
                        class="text-xs font-medium text-slate-500 tabular-nums">{{ $taxReportStats['completion_percentage'] }}%</span>
                </div>
            </div>

            {{-- Total Dokumen --}}
            <div class="rounded-lg border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-5 shadow-sm">
                <div class="flex items-center gap-2 text-slate-500 dark:text-slate-400 mb-2">
                    <x-heroicon-o-document-duplicate class="h-4 w-4" />
                    <h3 class="text-xs font-medium uppercase tracking-wider">Documents</h3>
                </div>
                <div class="flex items-baseline gap-2">
                    <span
                        class="text-2xl font-semibold text-slate-900 dark:text-white tabular-nums">{{ $documentStats['total'] }}</span>
                    <span
                        class="text-xs font-medium text-emerald-600 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800/50 rounded bg-emerald-50 dark:bg-emerald-900/20 px-1.5 py-0.5 tabular-nums">{{ $documentStats['valid'] }}
                        valid</span>
                </div>
                @if($documentStats['expired'] > 0)
                    <p class="mt-2 flex items-center gap-1.5 text-xs text-red-600 dark:text-red-400 font-medium">
                        <x-heroicon-s-x-mark class="w-3 h-3" /> {{ $documentStats['expired'] }} Expired
                    </p>
                @else
                    <p class="mt-2 text-xs text-slate-500">Fully compliant</p>
                @endif
            </div>
        </div>


        {{-- MAIN CONTENT SPLIT - Tables / Feeds --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6" x-show="mounted" x-transition.opacity.duration.300ms>

            {{-- LEFT COLUMN: PROJECTS PANEL --}}
            <div
                class="rounded-lg border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 shadow-sm flex flex-col h-[600px] overflow-hidden">
                <div class="border-b border-slate-200 dark:border-slate-800 px-5 py-4 flex items-center justify-between">
                    <h2 class="text-sm font-semibold text-slate-900 dark:text-white">Active Projects</h2>
                    <a href="#"
                        class="text-xs font-medium text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 focus-visible:outline-none focus-visible:underline rounded-sm px-1 -mx-1 transition-colors">
                        View all
                    </a>
                </div>

                <div class="flex-1 overflow-y-auto">
                    <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-800 text-sm">
                        <thead class="bg-slate-50 dark:bg-slate-900/50 sticky top-0 z-10 backdrop-blur-sm">
                            <tr>
                                <th scope="col"
                                    class="py-2.5 pl-5 pr-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">
                                    Project Name</th>
                                <th scope="col"
                                    class="px-3 py-2.5 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">
                                    Status</th>
                                <th scope="col"
                                    class="px-3 py-2.5 text-right text-xs font-medium text-slate-500 uppercase tracking-wider pr-5">
                                    Due</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 dark:divide-slate-800 bg-white dark:bg-slate-900">
                            @forelse($projects as $project)
                                @php
                                    $statusStyles = [
                                        'draft' => 'bg-slate-100 text-slate-700 border-slate-200 dark:bg-slate-800 dark:text-slate-300 dark:border-slate-700',
                                        'analysis' => 'bg-purple-50 text-purple-700 border-purple-200 dark:bg-purple-900/20 dark:text-purple-300 dark:border-purple-800',
                                        'in_progress' => 'bg-blue-50 text-blue-700 border-blue-200 dark:bg-blue-900/20 dark:text-blue-300 dark:border-blue-800',
                                        'review' => 'bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-900/20 dark:text-amber-300 dark:border-amber-800',
                                        'completed' => 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-900/20 dark:text-emerald-300 dark:border-emerald-800',
                                        'on_hold' => 'bg-slate-50 text-slate-600 border-slate-200 dark:bg-slate-900/50 dark:text-slate-400 dark:border-slate-800',
                                    ];
                                    $style = $statusStyles[$project->status] ?? $statusStyles['draft'];
                                    $label = str_replace('_', ' ', Str::title($project->status));
                                @endphp
                                <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors group cursor-pointer">
                                    <td
                                        class="whitespace-nowrap py-3 pl-5 pr-3 text-slate-900 dark:text-white font-medium max-w-[200px] truncate">
                                        {{ $project->name }}
                                        @if($project->priority === 'urgent')
                                            <span
                                                class="ml-1.5 inline-flex items-center rounded-full bg-red-100 px-1.5 py-0.5 text-[10px] font-medium text-red-700 dark:bg-red-900/30 dark:text-red-400 border border-red-200 dark:border-red-800">Urgent</span>
                                        @endif
                                    </td>
                                    <td class="whitespace-nowrap px-3 py-3">
                                        <span
                                            class="inline-flex items-center rounded-md px-2 py-0.5 text-xs font-medium border {{ $style }}">
                                            {{ $label }}
                                        </span>
                                    </td>
                                    <td class="whitespace-nowrap px-3 py-3 text-right text-slate-500 tabular-nums pr-5">
                                        {{ $project->due_date ? $project->due_date->format('M d, Y') : '—' }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="px-5 py-8 text-center text-sm text-slate-500">
                                        No active projects available.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>


            {{-- RIGHT COLUMN: DOCUMENTS PANEL --}}
            <div class="rounded-lg border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 shadow-sm flex flex-col h-[600px] overflow-hidden"
                x-data="{ activeSection: 'pending' }">

                <div class="border-b border-slate-200 dark:border-slate-800 px-5 flex items-center justify-between">
                    <h2 class="text-sm font-semibold text-slate-900 dark:text-white py-4">Required Documents</h2>

                    {{-- Clean native-feeling tabs --}}
                    <div class="flex space-x-4">
                        <button @click="activeSection = 'pending'"
                            :class="activeSection === 'pending' ? 'border-slate-900 text-slate-900 dark:border-white dark:text-white' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300 dark:hover:text-slate-300 dark:hover:border-slate-700'"
                            class="whitespace-nowrap border-b-2 py-4 text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-900 focus-visible:ring-offset-1 rounded-t-sm">
                            To Upload
                            @if($pendingDocuments->count() > 0)
                                <span
                                    class="ml-1.5 inline-flex items-center rounded-full bg-slate-100 px-2 py-0.5 text-xs font-bold text-slate-700 dark:bg-slate-800 dark:text-slate-300 tabular-nums">{{ $pendingDocuments->count() }}</span>
                            @endif
                        </button>
                        <button @click="activeSection = 'uploaded'"
                            :class="activeSection === 'uploaded' ? 'border-slate-900 text-slate-900 dark:border-white dark:text-white' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300 dark:hover:text-slate-300 dark:hover:border-slate-700'"
                            class="whitespace-nowrap border-b-2 py-4 text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-900 focus-visible:ring-offset-1 rounded-t-sm">
                            Archive
                        </button>
                    </div>
                </div>

                <div class="flex-1 overflow-y-auto">
                    <div x-show="activeSection === 'pending'" x-cloak>
                        <ul class="divide-y divide-slate-200 dark:divide-slate-800">
                            @forelse($pendingDocuments as $doc)
                                <li class="px-5 py-4 hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors group">
                                    <div class="flex items-start justify-between">
                                        <div class="min-w-0 flex-1 pr-4">
                                            <h4 class="text-sm font-medium text-slate-900 dark:text-white truncate">
                                                {{ $doc['name'] }}
                                                @if($doc['is_required'])
                                                    <span
                                                        class="ml-2 inline-flex items-center rounded-md bg-slate-100 px-1.5 py-0.5 text-[10px] font-medium text-slate-600 dark:bg-slate-800 dark:text-slate-400 border border-slate-200 dark:border-slate-700">Required</span>
                                                @endif
                                            </h4>
                                            <p class="mt-1 flex items-center text-xs text-slate-500 gap-3">
                                                <span>Type:
                                                    {{ $doc['type'] === 'requirement' ? 'Administrative' : 'Legal Standard' }}</span>
                                                @if($doc['due_date'])
                                                    <span
                                                        class="tabular-nums {{ \Carbon\Carbon::parse($doc['due_date'])->isPast() ? 'text-red-500 font-medium' : '' }}">
                                                        &bull; Due {{ \Carbon\Carbon::parse($doc['due_date'])->format('M d, Y') }}
                                                    </span>
                                                @endif
                                            </p>
                                        </div>
                                        <div class="flex-shrink-0 flex flex-col justify-center h-full pt-1">
                                            <button
                                                class="inline-flex items-center rounded-md border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 px-2.5 py-1.5 text-xs font-medium text-slate-700 dark:text-slate-300 shadow-sm hover:bg-slate-50 dark:hover:bg-slate-800 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-900 transition-colors">
                                                <x-heroicon-o-arrow-up-tray class="mr-1.5 h-3.5 w-3.5 text-slate-400" />
                                                Upload
                                            </button>
                                        </div>
                                    </div>
                                </li>
                            @empty
                                <li class="px-5 py-8 text-center text-sm text-slate-500">
                                    All required documents have been uploaded.
                                </li>
                            @endforelse
                        </ul>
                    </div>

                    <div x-show="activeSection === 'uploaded'" x-cloak>
                        @php $uploaded = $allDocumentsChecklist->where('is_uploaded', true); @endphp
                        <ul class="divide-y divide-slate-200 dark:divide-slate-800">
                            @forelse($uploaded as $doc)
                                <li
                                    class="px-5 py-3 hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors group flex items-center justify-between">
                                    <div class="min-w-0 pr-4">
                                        <h4 class="text-sm font-medium text-slate-900 dark:text-white truncate"
                                            title="{{ $doc['name'] }}">{{ $doc['name'] }}</h4>
                                        <div class="mt-1 flex items-center gap-2 text-xs text-slate-500 tabular-nums">
                                            <span>{{ $doc['uploaded_at'] ? $doc['uploaded_at']->format('M d, Y') : '—' }}</span>
                                            @php
                                                $lbl = $doc['status'] === 'valid' ? 'Valid' : ($doc['status'] === 'pending_review' ? 'In Review' : 'Flagged');
                                            @endphp
                                            <span class="text-slate-400">&bull;</span>
                                            <span>{{ $lbl }}</span>
                                        </div>
                                    </div>
                                    @if($doc['file_path'] && $doc['uploaded_document'])
                                        <div class="flex-shrink-0">
                                            <button wire:click="downloadDocument({{ $doc['uploaded_document']->id }})"
                                                class="text-slate-400 hover:text-slate-700 dark:hover:text-slate-300 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-900 rounded-sm p-1 transition-colors"
                                                title="Download File">
                                                <x-heroicon-o-arrow-down-tray class="h-4 w-4" />
                                                <span class="sr-only">Download</span>
                                            </button>
                                        </div>
                                    @endif
                                </li>
                            @empty
                                <li class="px-5 py-8 text-center text-sm text-slate-500">
                                    No documents in the archive yet.
                                </li>
                            @endforelse
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>