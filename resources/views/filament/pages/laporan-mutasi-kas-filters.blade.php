<div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 mb-6">
    <h3 class="text-lg font-semibold mb-4">Filter Laporan</h3>
    <form wire:submit="applyFilters">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                {{ $this->form->getComponent('dari') }}
            </div>
            <div>
                {{ $this->form->getComponent('sampai') }}
            </div>
            <div>
                {{ $this->form->getComponent('id_jenis_kas') }}
            </div>
            <div class="flex items-end">
                <x-filament::button type="submit" class="w-full">
                    LIHAT DATA
                </x-filament::button>
            </div>
        </div>
    </form>
</div>
