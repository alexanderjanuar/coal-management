<div x-data="{ activeTab: 'all-summary' }">
    <div class="mb-6">
        {{ $this->form }}

        <div class="mt-4 flex justify-end">
            <x-filament::button wire:click="createTaxReport">
                Create Tax Report
            </x-filament::button>
        </div>
    </div>

    @if($selectedClient)
    <div class="mt-8">
        @include('components.tax-reports.client-details-accordion', ['client' => $selectedClient])
    </div>
    @endif

    {{-- <div class="mt-8">
        <div class="md:flex">
            <ul class="flex-column space-y-4 text-sm font-medium text-gray-500 dark:text-gray-400 md:me-4 mb-4 md:mb-0">
                <li>
                    <a href="#" @click.prevent="activeTab = 'all-summary'"
                        :class="{'inline-flex items-center px-4 py-3 text-white bg-blue-700 rounded-lg w-full dark:bg-blue-600': activeTab === 'all-summary',
                                'inline-flex items-center px-4 py-3 rounded-lg hover:text-gray-900 bg-gray-50 hover:bg-gray-100 w-full dark:bg-gray-800 dark:hover:bg-gray-700 dark:hover:text-white': activeTab !== 'all-summary'}">
                        <svg class="w-4 h-4 me-2"
                            :class="{'text-white': activeTab === 'all-summary', 'text-gray-500 dark:text-gray-400': activeTab !== 'all-summary'}"
                            aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor"
                            viewBox="0 0 20 20">
                            <path d="M5 5V.13a2.96 2.96 0 0 0-1.293.749L.879 3.707A2.96 2.96 0 0 0 .13 5H5Z" />
                            <path
                                d="M6.737 11.061a2.961 2.961 0 0 1 .81-1.515l6.117-6.116A4.839 4.839 0 0 1 16 2.141V2a1.97 1.97 0 0 0-1.933-2H7v5a2 2 0 0 1-2 2H0v11a1.969 1.969 0 0 0 1.933 2h12.134A1.97 1.97 0 0 0 16 18v-3.093l-1.546 1.546c-.413.413-.94.695-1.513.81l-3.4.679a2.947 2.947 0 0 1-1.85-.227 2.96 2.96 0 0 1-1.635-3.257l.681-3.397Z" />
                            <path
                                d="M8.961 16a.93.93 0 0 0 .189-.019l3.4-.679a.961.961 0 0 0 .49-.263l6.118-6.117a2.884 2.884 0 0 0-4.079-4.078l-6.117 6.117a.96.96 0 0 0-.263.491l-.679 3.4A.961.961 0 0 0 8.961 16Zm7.477-9.8a.958.958 0 0 1 .68-.281.961.961 0 0 1 .682 1.644l-.315.315-1.36-1.36.313-.318Zm-5.911 5.911 4.236-4.236 1.359 1.359-4.236 4.237-1.7.339.341-1.699Z" />
                        </svg>
                        All Summary
                    </a>
                </li>
                <li>
                    <a href="#" @click.prevent="activeTab = 'invoice'"
                        :class="{'inline-flex items-center px-4 py-3 text-white bg-blue-700 rounded-lg w-full dark:bg-blue-600': activeTab === 'invoice',
                                'inline-flex items-center px-4 py-3 rounded-lg hover:text-gray-900 bg-gray-50 hover:bg-gray-100 w-full dark:bg-gray-800 dark:hover:bg-gray-700 dark:hover:text-white': activeTab !== 'invoice'}">
                        <svg class="w-4 h-4 me-2"
                            :class="{'text-white': activeTab === 'invoice', 'text-gray-500 dark:text-gray-400': activeTab !== 'invoice'}"
                            aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor"
                            viewBox="0 0 20 20">
                            <path
                                d="M18 16H2a2 2 0 0 0-2 2v.5A1.5 1.5 0 0 0 1.5 20h17a1.5 1.5 0 0 0 1.5-1.5V18a2 2 0 0 0-2-2Z" />
                            <path
                                d="M18.444 1H1.556A1.556 1.556 0 0 0 0 2.556V15h20V2.556A1.556 1.556 0 0 0 18.444 1ZM7 12H3V8h4v4Zm10-7H3V3h14v2Z" />
                        </svg>
                        Invoice
                    </a>
                </li>
                <li>
                    <a href="#" @click.prevent="activeTab = 'income-tax'"
                        :class="{'inline-flex items-center px-4 py-3 text-white bg-blue-700 rounded-lg w-full dark:bg-blue-600': activeTab === 'income-tax',
                                'inline-flex items-center px-4 py-3 rounded-lg hover:text-gray-900 bg-gray-50 hover:bg-gray-100 w-full dark:bg-gray-800 dark:hover:bg-gray-700 dark:hover:text-white': activeTab !== 'income-tax'}">
                        <svg class="w-4 h-4 me-2"
                            :class="{'text-white': activeTab === 'income-tax', 'text-gray-500 dark:text-gray-400': activeTab !== 'income-tax'}"
                            aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor"
                            viewBox="0 0 20 20">
                            <path
                                d="M14.303 8.045c.076-.32.11-.639.105-.958.022-.158.033-.318.033-.48 0-1.764-1.323-3.217-3.025-3.217-.29 0-.574.043-.846.125C9.89 2.847 8.970 2.5 8 2.5c-.986 0-1.918.356-2.59 1.033A3.159 3.159 0 0 0 3.53 3.5c-1.702 0-3.025 1.453-3.025 3.217 0 .162.01.322.034.48a3.5 3.5 0 0 0 1.092 6.331A3.46 3.46 0 0 0 1.5 14.5c0 1.953 1.547 3.5 3.5 3.5a3.46 3.46 0 0 0 1.785-.473 3.49 3.49 0 0 0 3.56-.041c.541.34 1.169.514 1.805.514 1.953 0 3.5-1.547 3.5-3.5 0-.174-.012-.344-.036-.51a3.496 3.496 0 0 0-1.311-6.945Z" />
                        </svg>
                        Income Tax
                    </a>
                </li>
                <li>
                    <a href="#" @click.prevent="activeTab = 'bupots'"
                        :class="{'inline-flex items-center px-4 py-3 text-white bg-blue-700 rounded-lg w-full dark:bg-blue-600': activeTab === 'bupots',
                                'inline-flex items-center px-4 py-3 rounded-lg hover:text-gray-900 bg-gray-50 hover:bg-gray-100 w-full dark:bg-gray-800 dark:hover:bg-gray-700 dark:hover:text-white': activeTab !== 'bupots'}">
                        <svg class="w-4 h-4 me-2"
                            :class="{'text-white': activeTab === 'bupots', 'text-gray-500 dark:text-gray-400': activeTab !== 'bupots'}"
                            aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor"
                            viewBox="0 0 20 20">
                            <path
                                d="M17 5.923A1 1 0 0 0 16 5h-3V4a4 4 0 1 0-8 0v1H2a1 1 0 0 0-1 .923L.086 17.846A2 2 0 0 0 2.08 20h13.84a2 2 0 0 0 1.994-2.153L17 5.923ZM7 9a1 1 0 0 1-2 0V7h2v2Zm0-5a2 2 0 1 1 4 0v1H7V4Zm6 5a1 1 0 1 1-2 0V7h2v2Z" />
                        </svg>
                        Bupots
                    </a>
                </li>
            </ul>

            <!-- Tab content sections -->
            <div class="p-6 bg-gray-50 text-medium text-gray-500 dark:text-gray-400 dark:bg-gray-800 rounded-lg w-full">
                <!-- All Summary Tab Content -->
                <div x-show="activeTab === 'all-summary'" x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4">Tax Report Summary</h3>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                        <div class="bg-white p-4 rounded-lg shadow-sm border border-gray-200">
                            <h4 class="text-gray-700 font-semibold mb-2">Invoice Summary</h4>
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-gray-500">Total Invoices</span>
                                <span class="text-blue-600 font-bold">42</span>
                            </div>
                            <div class="flex items-center justify-between mt-2">
                                <span class="text-sm text-gray-500">Total Amount</span>
                                <span class="text-blue-600 font-bold">Rp 85,420,000</span>
                            </div>
                        </div>

                        <div class="bg-white p-4 rounded-lg shadow-sm border border-gray-200">
                            <h4 class="text-gray-700 font-semibold mb-2">Income Tax Summary</h4>
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-gray-500">Employee Records</span>
                                <span class="text-green-600 font-bold">16</span>
                            </div>
                            <div class="flex items-center justify-between mt-2">
                                <span class="text-sm text-gray-500">Total PPh 21</span>
                                <span class="text-green-600 font-bold">Rp 12,350,000</span>
                            </div>
                        </div>

                        <div class="bg-white p-4 rounded-lg shadow-sm border border-gray-200">
                            <h4 class="text-gray-700 font-semibold mb-2">Bupot Summary</h4>
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-gray-500">Total Bupots</span>
                                <span class="text-purple-600 font-bold">8</span>
                            </div>
                            <div class="flex items-center justify-between mt-2">
                                <span class="text-sm text-gray-500">Total Amount</span>
                                <span class="text-purple-600 font-bold">Rp 5,280,000</span>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white p-4 rounded-lg shadow-sm border border-gray-200">
                        <h4 class="text-gray-700 font-semibold mb-4">Recent Activity</h4>
                        <ul class="space-y-3">
                            <li class="flex items-start">
                                <div
                                    class="flex-shrink-0 w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center mr-3">
                                    <svg class="w-4 h-4 text-blue-600" fill="currentColor" viewBox="0 0 20 20"
                                        xmlns="http://www.w3.org/2000/svg">
                                        <path d="M10 12a2 2 0 100-4 2 2 0 000 4z"></path>
                                        <path fill-rule="evenodd"
                                            d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11a1 1 0 10-2 0v2H7a1 1 0 100 2h2v2a1 1 0 102 0v-2h2a1 1 0 100-2h-2V7z"
                                            clip-rule="evenodd"></path>
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-700">New invoice <span
                                            class="font-medium">INV-2025-042</span> added</p>
                                    <p class="text-xs text-gray-500 mt-1">2 hours ago</p>
                                </div>
                            </li>
                            <li class="flex items-start">
                                <div
                                    class="flex-shrink-0 w-8 h-8 bg-green-100 rounded-full flex items-center justify-center mr-3">
                                    <svg class="w-4 h-4 text-green-600" fill="currentColor" viewBox="0 0 20 20"
                                        xmlns="http://www.w3.org/2000/svg">
                                        <path fill-rule="evenodd"
                                            d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                            clip-rule="evenodd"></path>
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-700">Income tax report for <span
                                            class="font-medium">April 2025</span> completed</p>
                                    <p class="text-xs text-gray-500 mt-1">Yesterday</p>
                                </div>
                            </li>
                            <li class="flex items-start">
                                <div
                                    class="flex-shrink-0 w-8 h-8 bg-yellow-100 rounded-full flex items-center justify-center mr-3">
                                    <svg class="w-4 h-4 text-yellow-600" fill="currentColor" viewBox="0 0 20 20"
                                        xmlns="http://www.w3.org/2000/svg">
                                        <path fill-rule="evenodd"
                                            d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z"
                                            clip-rule="evenodd"></path>
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-700">Bupot <span class="font-medium">BP-2025-008</span>
                                        needs review</p>
                                    <p class="text-xs text-gray-500 mt-1">3 days ago</p>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- Invoice Tab Content -->
                <div x-show="activeTab === 'invoice'" x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
                    <livewire:monthly-plan.tax-invoice-manager :taxReportId="$taxReport->id ?? null" />
                </div>

                <!-- Income Tax Tab Content -->
                <div x-show="activeTab === 'income-tax'" x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white">Income Tax (PPh 21)</h3>
                        <button type="button"
                            class="inline-flex items-center px-3 py-2 bg-green-600 text-white text-sm font-medium rounded-md hover:bg-green-700">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                            </svg>
                            Add Income Tax
                        </button>
                    </div>

                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-gray-200">
                        <div class="p-4">
                            <div class="flex justify-between items-center mb-4">
                                <div class="w-64">
                                    <input type="text" placeholder="Search employees..."
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                </div>
                                <div>
                                    <select
                                        class="px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                        <option>All Employees</option>
                                        <option>Active Employees</option>
                                        <option>Inactive Employees</option>
                                    </select>
                                </div>
                            </div>

                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th scope="col"
                                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Employee
                                            </th>
                                            <th scope="col"
                                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                NPWP
                                            </th>
                                            <th scope="col"
                                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Position
                                            </th>
                                            <th scope="col"
                                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Salary (TER)
                                            </th>
                                            <th scope="col"
                                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                PPh 21
                                            </th>
                                            <th scope="col"
                                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Actions
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="flex items-center">
                                                    <div
                                                        class="flex-shrink-0 h-10 w-10 bg-gray-200 rounded-full flex items-center justify-center">
                                                        <span class="text-gray-600 font-medium">BS</span>
                                                    </div>
                                                    <div class="ml-4">
                                                        <div class="text-sm font-medium text-gray-900">
                                                            Budi Santoso
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                09.123.456.7-012.345
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                Finance Manager
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                Rp 12,500,000
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                Rp 925,000
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                                <div class="flex space-x-2">
                                                    <button class="text-blue-600 hover:text-blue-900">View</button>
                                                    <button class="text-gray-600 hover:text-gray-900">Edit</button>
                                                    <button class="text-red-600 hover:text-red-900">Delete</button>
                                                </div>
                                            </td>
                                        </tr>
                                        <!-- More income tax rows would be here -->
                                    </tbody>
                                </table>
                            </div>

                            <div class="flex items-center justify-between mt-4">
                                <div class="text-sm text-gray-500">
                                    Showing 1 to 10 of 16 employees
                                </div>
                                <div class="flex space-x-2">
                                    <button
                                        class="px-3 py-1 border border-gray-300 rounded-md text-sm">Previous</button>
                                    <button class="px-3 py-1 bg-green-600 text-white rounded-md text-sm">1</button>
                                    <button class="px-3 py-1 border border-gray-300 rounded-md text-sm">2</button>
                                    <button class="px-3 py-1 border border-gray-300 rounded-md text-sm">Next</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Bupots Tab Content -->
                <div x-show="activeTab === 'bupots'" x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white">Bukti Potong (Bupots)</h3>
                        <button type="button"
                            class="inline-flex items-center px-3 py-2 bg-purple-600 text-white text-sm font-medium rounded-md hover:bg-purple-700">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                            </svg>
                            Add Bupot
                        </button>
                    </div>

                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-gray-200">
                        <div class="p-4">
                            <div class="flex justify-between items-center mb-4">
                                <div class="w-64">
                                    <input type="text" placeholder="Search bupots..."
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                </div>
                                <div class="flex space-x-2">
                                    <select
                                        class="px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                        <option>All Types</option>
                                        <option>Bupot Masukan</option>
                                        <option>Bupot Keluaran</option>
                                    </select>
                                    <select
                                        class="px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                        <option>All PPh Types</option>
                                        <option>PPh 21</option>
                                        <option>PPh 23</option>
                                    </select>
                                </div>
                            </div>

                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th scope="col"
                                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Company
                                            </th>
                                            <th scope="col"
                                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                NPWP
                                            </th>
                                            <th scope="col"
                                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Type
                                            </th>
                                            <th scope="col"
                                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                PPh Type
                                            </th>
                                            <th scope="col"
                                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                DPP
                                            </th>
                                            <th scope="col"
                                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Bupot Amount
                                            </th>
                                            <th scope="col"
                                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Actions
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                PT Jasa Konsultan
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                01.456.789.0-123.456
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span
                                                    class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-purple-100 text-purple-800">
                                                    Bupot Keluaran
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span
                                                    class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                                    PPh 23
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                Rp 8,000,000
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                Rp 160,000
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                                <div class="flex space-x-2">
                                                    <button class="text-blue-600 hover:text-blue-900">View</button>
                                                    <button class="text-gray-600 hover:text-gray-900">Edit</button>
                                                    <button class="text-red-600 hover:text-red-900">Delete</button>
                                                </div>
                                            </td>
                                        </tr>
                                        <!-- More bupot rows would be here -->
                                    </tbody>
                                </table>
                            </div>

                            <div class="flex items-center justify-between mt-4">
                                <div class="text-sm text-gray-500">
                                    Showing 1 to 8 of 8 bupots
                                </div>
                                <div class="flex space-x-2">
                                    <button
                                        class="px-3 py-1 border border-gray-300 rounded-md text-sm">Previous</button>
                                    <button class="px-3 py-1 bg-purple-600 text-white rounded-md text-sm">1</button>
                                    <button class="px-3 py-1 border border-gray-300 rounded-md text-sm">Next</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div> --}}
</div>