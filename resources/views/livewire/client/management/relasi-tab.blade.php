<div wire:key="relasi-{{ $client->id }}">
    <x-group-panel :group="$client->group" :currentId="$client->id" :emptyEditUrl="$this->editUrl()" />
</div>
