<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            Itens por Status
        </x-slot>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @foreach($this->totais as $status)
                <div class="flex items-center justify-between p-4 rounded-lg border" style="border-color: {{ $status['cor'] }}20; background-color: {{ $status['cor'] }}10;">
                    <div class="flex items-center gap-3">
                        <div class="w-4 h-4 rounded-full" style="background-color: {{ $status['cor'] }};"></div>
                        <span class="font-medium text-gray-700">{{ $status['nome'] }}</span>
                    </div>
                    <span class="text-2xl font-bold" style="color: {{ $status['cor'] }};">{{ $status['total'] }}</span>
                </div>
            @endforeach
        </div>
    </x-filament::section>
</x-filament-widgets::widget>


