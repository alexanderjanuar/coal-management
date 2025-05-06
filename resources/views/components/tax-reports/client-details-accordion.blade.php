@if($client)
<div class="bg-white rounded-xl shadow-sm overflow-hidden transition-all duration-300 hover:shadow-md">
    <!-- Client Header - Always visible -->
    <div class="flex items-center justify-between p-5 border-b border-gray-100 bg-gradient-to-r from-blue-50 to-indigo-50">
        <div class="flex items-center">
            <div class="flex-shrink-0 mr-4">
                @if($client->logo)
                    <img src="{{ Storage::url($client->logo) }}" alt="{{ $client->name }}" class="h-14 w-14 rounded-full object-cover border-2 border-white shadow-sm">
                @else
                    <div class="h-14 w-14 rounded-full bg-gradient-to-r from-blue-500 to-indigo-600 flex items-center justify-center shadow-sm">
                        <span class="text-white font-bold text-xl">{{ substr($client->name, 0, 1) }}</span>
                    </div>
                @endif
            </div>
            <div>
                <h3 class="text-xl font-bold text-gray-800">{{ $client->name }}</h3>
                <div class="text-sm text-gray-500 mt-1">
                    @if($client->email)
                        <span class="inline-flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207" />
                            </svg>
                            {{ $client->email }}
                        </span>
                    @endif
                </div>
            </div>
        </div>
        
        <div class="flex items-center space-x-3">
            <a href="{{ url('clients/'.$client->id) }}" class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors duration-200">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                </svg>
                View Client
            </a>
            
            <button type="button" 
                x-data="{}" 
                x-on:click="$dispatch('toggle-details')"
                class="inline-flex items-center p-2 border border-transparent rounded-full text-blue-600 hover:bg-blue-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 transition-transform duration-300 ease-in-out" x-data="{}" x-bind:class="$store.clientDetails.open ? 'transform rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
            </button>
        </div>
    </div>
    
    <!-- Client Details - Accordion Content -->
    <div x-data="{ open: false }" 
         x-on:toggle-details.window="open = !open"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 transform -translate-y-4"
         x-transition:enter-end="opacity-100 transform translate-y-0"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 transform translate-y-0"
         x-transition:leave-end="opacity-0 transform -translate-y-4"
         x-show="open" 
         x-cloak
         x-init="$store.clientDetails = { open: false }">
        
        <div class="p-5 bg-gray-50 border-b border-gray-100">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div class="bg-white p-4 rounded-lg border border-gray-200 shadow-sm hover:shadow-md transition-all duration-200 transform hover:-translate-y-1">
                    <span class="text-xs font-medium text-gray-500 uppercase tracking-wider">NPWP</span>
                    <p class="font-medium mt-1 text-gray-900">{{ $client->NPWP ?? 'Not provided' }}</p>
                </div>
                
                <div class="bg-white p-4 rounded-lg border border-gray-200 shadow-sm hover:shadow-md transition-all duration-200 transform hover:-translate-y-1">
                    <span class="text-xs font-medium text-gray-500 uppercase tracking-wider">KPP</span>
                    <p class="font-medium mt-1 text-gray-900">{{ $client->KPP ?? 'Not provided' }}</p>
                </div>
                
                <div class="bg-white p-4 rounded-lg border border-gray-200 shadow-sm hover:shadow-md transition-all duration-200 transform hover:-translate-y-1">
                    <span class="text-xs font-medium text-gray-500 uppercase tracking-wider">Account Representative</span>
                    <p class="font-medium mt-1 text-gray-900">{{ $client->account_representative ?? 'Not assigned' }}</p>
                </div>
                
                <div class="bg-white p-4 rounded-lg border border-gray-200 shadow-sm hover:shadow-md transition-all duration-200 transform hover:-translate-y-1">
                    <span class="text-xs font-medium text-gray-500 uppercase tracking-wider">Status</span>
                    <p class="font-medium mt-1">
                        @if($client->status == 'Active')
                            <span class="px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-800">Active</span>
                        @else
                            <span class="px-2 py-1 text-xs font-medium rounded-full bg-red-100 text-red-800">Inactive</span>
                        @endif
                    </p>
                </div>
            </div>
        </div>
        
        <div class="p-5 bg-white">
            <div class="mb-3 flex items-center justify-between">
                <h4 class="text-sm font-semibold text-gray-700">Recent Tax Reports</h4>
                
                @php
                    $recentReports = \App\Models\TaxReport::where('client_id', $client->id)
                        ->orderBy('month', 'desc')
                        ->limit(3)
                        ->get();
                    $totalReports = \App\Models\TaxReport::where('client_id', $client->id)->count();
                @endphp
                
                @if($totalReports > 3)
                    <a href="{{ url('clients/'.$client->id.'/tax-reports') }}" class="text-xs text-blue-600 hover:text-blue-800 transition-colors group flex items-center">
                        View all {{ $totalReports }} reports
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 ml-1 transform transition-transform duration-200 group-hover:translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </a>
                @endif
            </div>
            
            @if($recentReports->count() > 0)
                <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Period</th>
                                <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created</th>
                                <th scope="col" class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($recentReports as $report)
                                <tr class="hover:bg-gray-50 transition-colors duration-150">
                                    <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-gray-900">
                                        {{ \Carbon\Carbon::parse($report->month)->format('F Y') }}
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">
                                        {{ $report->created_at->diffForHumans() }}
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500 text-right">
                                        <a href="{{ url('tax-reports/'.$report->id) }}" class="text-blue-600 hover:text-blue-900 transition-colors inline-flex items-center group">
                                            <span>View</span>
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 ml-1 opacity-0 group-hover:opacity-100 transform transition-all duration-200 group-hover:translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                                            </svg>
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-10 bg-gray-50 rounded-lg border border-gray-200 transition-all duration-300 hover:bg-gray-100">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mx-auto text-gray-400 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <p class="text-gray-600 font-medium">No tax reports available for this client</p>
                    <p class="text-sm text-gray-500 mt-2">Tax reports created for this client will appear here</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endif