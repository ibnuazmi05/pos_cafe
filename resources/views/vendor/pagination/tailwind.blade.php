@if ($paginator->hasPages())
    <nav role="navigation" aria-label="{{ __('Pagination Navigation') }}" class="flex items-center justify-between pt-3">
        {{-- Tampilan Mobile --}}
        <div class="flex flex-1 justify-between sm:hidden gap-2">
            @if ($paginator->onFirstPage())
                <span class="inline-flex items-center px-4 py-2 border border-stone-200/80 rounded-xl text-xs font-semibold text-stone-400 bg-stone-50 cursor-not-allowed">
                    Sebelumnya
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" class="inline-flex items-center px-4 py-2 border border-stone-200/90 rounded-xl text-xs font-semibold text-stone-700 bg-white hover:bg-stone-50 hover:text-stone-900 transition shadow-xs">
                    Sebelumnya
                </a>
            @endif

            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" class="inline-flex items-center px-4 py-2 border border-stone-200/90 rounded-xl text-xs font-semibold text-stone-700 bg-white hover:bg-stone-50 hover:text-stone-900 transition shadow-xs">
                    Berikutnya
                </a>
            @else
                <span class="inline-flex items-center px-4 py-2 border border-stone-200/80 rounded-xl text-xs font-semibold text-stone-400 bg-stone-50 cursor-not-allowed">
                    Berikutnya
                </span>
            @endif
        </div>

        {{-- Tampilan Desktop --}}
        <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between gap-4">
            <div>
                <p class="text-xs text-stone-500">
                    Menampilkan
                    @if ($paginator->firstItem())
                        <span class="font-semibold text-stone-800">{{ $paginator->firstItem() }}</span>
                        sampai
                        <span class="font-semibold text-stone-800">{{ $paginator->lastItem() }}</span>
                    @else
                        {{ $paginator->count() }}
                    @endif
                    dari
                    <span class="font-semibold text-stone-800">{{ $paginator->total() }}</span>
                    data
                </p>
            </div>

            <div>
                <div class="inline-flex items-center gap-1.5">
                    {{-- Previous Page Link --}}
                    @if ($paginator->onFirstPage())
                        <span aria-disabled="true" aria-label="Sebelumnya">
                            <span class="inline-flex items-center justify-center w-8 h-8 rounded-xl border border-stone-200/80 bg-stone-50 text-stone-300 cursor-not-allowed text-xs font-medium" aria-hidden="true">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" />
                                </svg>
                            </span>
                        </span>
                    @else
                        <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="inline-flex items-center justify-center w-8 h-8 rounded-xl border border-stone-200/90 bg-white text-stone-600 hover:bg-stone-50 hover:text-stone-900 transition shadow-xs text-xs font-medium" aria-label="Sebelumnya">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" />
                            </svg>
                        </a>
                    @endif

                    {{-- Pagination Elements --}}
                    @foreach ($elements as $element)
                        {{-- "Three Dots" Separator --}}
                        @if (is_string($element))
                            <span aria-disabled="true">
                                <span class="inline-flex items-center justify-center w-8 h-8 text-xs font-medium text-stone-400">{{ $element }}</span>
                            </span>
                        @endif

                        {{-- Array Of Links --}}
                        @if (is_array($element))
                            @foreach ($element as $page => $url)
                                @if ($page == $paginator->currentPage())
                                    <span aria-current="page">
                                        <span class="inline-flex items-center justify-center min-w-8 h-8 px-2 rounded-xl bg-amber-700 text-white text-xs font-bold border border-amber-700 shadow-xs cursor-default">{{ $page }}</span>
                                    </span>
                                @else
                                    <a href="{{ $url }}" class="inline-flex items-center justify-center min-w-8 h-8 px-2 rounded-xl border border-stone-200/90 bg-white text-stone-700 hover:bg-stone-50 hover:text-stone-900 transition shadow-xs text-xs font-semibold" aria-label="Halaman {{ $page }}">
                                        {{ $page }}
                                    </a>
                                @endif
                            @endforeach
                        @endif
                    @endforeach

                    {{-- Next Page Link --}}
                    @if ($paginator->hasMorePages())
                        <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="inline-flex items-center justify-center w-8 h-8 rounded-xl border border-stone-200/90 bg-white text-stone-600 hover:bg-stone-50 hover:text-stone-900 transition shadow-xs text-xs font-medium" aria-label="Berikutnya">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                            </svg>
                        </a>
                    @else
                        <span aria-disabled="true" aria-label="Berikutnya">
                            <span class="inline-flex items-center justify-center w-8 h-8 rounded-xl border border-stone-200/80 bg-stone-50 text-stone-300 cursor-not-allowed text-xs font-medium" aria-hidden="true">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                                </svg>
                            </span>
                        </span>
                    @endif
                </div>
            </div>
        </div>
    </nav>
@endif
