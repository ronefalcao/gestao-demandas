{{-- URL já vem pronta do modelo --}}

<div class="p-4">
    @if(in_array($tipo, ['jpg', 'jpeg', 'png']))
        {{-- Imagem --}}
        <div class="flex justify-center">
            <img src="{{ $url }}" 
                 alt="{{ $nome }}" 
                 class="max-w-full h-auto rounded-lg shadow-lg" 
                 style="max-height: 70vh;">
        </div>
    @elseif($tipo === 'pdf')
        {{-- PDF --}}
        <iframe src="{{ $url }}" 
                class="w-full rounded-lg shadow-lg" 
                style="height: 70vh; border: none;">
        </iframe>
    @elseif($tipo === 'mp4')
        {{-- Vídeo --}}
        <div class="flex justify-center">
            <video controls class="w-full rounded-lg shadow-lg" style="max-height: 70vh;">
                <source src="{{ $url }}" type="video/mp4">
                Seu navegador não suporta o elemento de vídeo.
            </video>
        </div>
    @else
        {{-- Outros tipos - mostrar link para download --}}
        <div class="text-center p-8">
            <p class="text-gray-600 mb-4">Este tipo de arquivo não pode ser visualizado no navegador.</p>
            <a href="{{ $url }}" 
               download 
               class="inline-flex items-center px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                </svg>
                Baixar Arquivo
            </a>
        </div>
    @endif
</div>

