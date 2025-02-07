<div class="bg-white rounded-xl shadow-sm overflow-hidden">
    <div class="px-6 py-5 border-b border-gray-100">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 bg-primary-50 rounded-xl flex items-center justify-center">
                <x-heroicon-o-user-group class="w-6 h-6 text-primary-600" />
            </div>
            <div>
                <h2 class="text-lg font-semibold text-gray-900">Project Team</h2>
                <p class="text-sm text-gray-500 mt-0.5">{{ $users->count() }} members</p>
            </div>
        </div>
    </div>

    <div class="divide-y divide-gray-50">
        @foreach($users as $user)
        <div class="p-5 hover:bg-gray-50/50 transition-all duration-200">
            <div class="flex items-start gap-4">
                <div class="relative flex-shrink-0 group">
                    <img src="{{ $user['avatar'] }}" alt="{{ $user['name'] }}"
                        class="w-12 h-12 rounded-xl object-cover ring-2 ring-white shadow-sm transition-transform group-hover:scale-105">
                    <div class="absolute -bottom-1 -right-1 h-4 w-4 bg-green-400 rounded-full ring-2 ring-white"></div>
                </div>

                <div class="flex-1 min-w-0">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                        <div>
                            <h3 class="text-sm font-medium text-gray-900">{{ $user['name'] }}</h3>
                            <p class="text-sm text-gray-500">{{ $user['email'] }}</p>
                        </div>

                        @if(!auth()->user()->hasRole('staff'))
                        <x-filament::dropdown placement="bottom-end">
                            <x-slot name="trigger">
                                <button class="p-2 text-gray-400 hover:text-gray-500 rounded-lg transition-colors">
                                    <x-heroicon-m-ellipsis-horizontal class="w-5 h-5" />
                                </button>
                            </x-slot>

                            <x-filament::dropdown.list>
                                <x-filament::dropdown.list.item
                                    x-on:click="$dispatch('open-modal', { id: 'confirm-remove-{{ $user['id'] }}' })"
                                    icon="heroicon-m-trash" color="danger">
                                    Remove
                                </x-filament::dropdown.list.item>
                            </x-filament::dropdown.list>
                        </x-filament::dropdown>
                        @endif
                    </div>

                    <div class="flex flex-wrap items-center gap-4 mt-3">
                        <div class="inline-flex items-center gap-2">
                            <div class="p-1.5 rounded-full bg-gray-50">
                                <x-heroicon-m-chat-bubble-left-right class="w-4 h-4 text-gray-400" />
                            </div>
                            <span class="text-sm text-gray-600">{{ $user['comments_count'] }} comments</span>
                        </div>

                        <div class="inline-flex items-center gap-2">
                            <div class="p-1.5 rounded-full bg-gray-50">
                                <x-heroicon-m-document class="w-4 h-4 text-gray-400" />
                            </div>
                            <span class="text-sm text-gray-600">{{ $user['documents_count'] }} documents</span>
                        </div>

                        <div class="inline-flex items-center gap-2">
                            <div class="p-1.5 rounded-full bg-gray-50">
                                <x-heroicon-m-clock class="w-4 h-4 text-gray-400" />
                            </div>
                            <span class="text-sm text-gray-600">Active {{ $user['last_active'] }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <x-filament::modal id="confirm-remove-{{ $user['id'] }}">
            <div class="p-6">
                <h3 class="text-lg font-medium text-gray-900">Remove Team Member</h3>
                <p class="mt-2 text-sm text-gray-500">
                    Are you sure you want to remove {{ $user['name'] }} from the project?
                </p>

                <div class="mt-6 flex justify-end gap-3">
                    <x-filament::button color="gray"
                        x-on:click="$dispatch('close-modal', { id: 'confirm-remove-{{ $user['id'] }}' })">
                        Cancel
                    </x-filament::button>
                    <x-filament::button color="danger" wire:click="removeMember({{ $user['id'] }})">
                        Remove
                    </x-filament::button>
                </div>
            </div>
        </x-filament::modal>
        @endforeach
    </div>

    @if(!auth()->user()->hasRole('staff'))
    <div x-data="{ open: false }" class="border-t border-gray-100">
        <button @click="open = !open"
            class="w-full px-6 py-4 text-sm font-medium text-gray-700 hover:bg-gray-50 flex items-center justify-center gap-2 transition-colors {{ auth()->user()->hasRole('staff') ? 'hidden' : '' }}">
            <x-heroicon-m-plus-circle class="w-5 h-5" />
            <span>Add Team Member</span>
        </button>

        <div x-show="open" x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 transform translate-y-4"
            x-transition:enter-end="opacity-100 transform translate-y-0" class="border-t border-gray-100 p-6">

            <div class="space-y-6">
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <x-heroicon-m-magnifying-glass class="w-5 h-5 text-gray-400" />
                    </div>
                    <x-filament::input type="text" wire:model.debounce.300ms="search" placeholder="Search users..."
                        class="pl-10" />
                </div>

                <div class="space-y-2 max-h-[400px] overflow-y-auto px-1">
                    @forelse($availableUsers as $availableUser)
                    <div
                        class="group p-4 bg-gray-50 hover:bg-white rounded-xl transition-all duration-200 border border-gray-100">
                        <div class="flex items-center gap-4">
                            <img src="https://ui-avatars.com/api/?name={{ urlencode($availableUser->name) }}"
                                class="w-10 h-10 rounded-lg object-cover">

                            <div class="flex-1 min-w-0">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <h4 class="text-sm font-medium text-gray-900">{{ $availableUser->name }}</h4>
                                        <p class="text-xs text-gray-500">{{ $availableUser->email }}</p>
                                    </div>

                                    <x-filament::button wire:click="addUserToProject({{ $availableUser->id }})"
                                        size="sm" class="opacity-0 group-hover:opacity-100 transition-opacity">
                                        Add Member
                                    </x-filament::button>
                                </div>
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="text-center py-8">
                        <div class="w-12 h-12 mx-auto bg-gray-50 rounded-xl flex items-center justify-center mb-4">
                            <x-heroicon-o-user-plus class="w-6 h-6 text-gray-400" />
                        </div>
                        <h4 class="text-sm font-medium text-gray-900">No users found</h4>
                        <p class="text-xs text-gray-500 mt-1">Try searching with a different term</p>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
    @endif

    @if($users->isEmpty())
    <div class="p-12 text-center">
        <div class="w-16 h-16 mx-auto bg-gray-50 rounded-xl flex items-center justify-center mb-4">
            <x-heroicon-o-user-group class="w-8 h-8 text-gray-400" />
        </div>
        <h3 class="text-base font-medium text-gray-900">No team members yet</h3>
        <p class="text-sm text-gray-500 mt-1">Start building your team by adding members</p>
    </div>
    @endif
</div>