<div>
    {{-- Standard Toggle --}}
    <div class="flex items-center gap-2 mb-6 p-1 rounded-xl w-fit" style="background:#F1F5F9">
        <button type="button"
                wire:click="setStandard('kved')"
                class="px-4 py-2 rounded-lg text-sm font-semibold transition-all"
                style="{{ $standard === 'kved' ? 'background:#FFFFFF; color:#1A5FBE; box-shadow:0 1px 4px rgba(0,0,0,0.1)' : 'color:#5A6A7F' }}">
            КВЕД-2010
        </button>
        <button type="button"
                wire:click="setStandard('nace')"
                class="px-4 py-2 rounded-lg text-sm font-semibold transition-all"
                style="{{ $standard === 'nace' ? 'background:#FFFFFF; color:#1A5FBE; box-shadow:0 1px 4px rgba(0,0,0,0.1)' : 'color:#5A6A7F' }}">
            NACE 2.1-UA
        </button>
    </div>

    {{-- Tree Nodes --}}
    @if (count($nodes) > 0)
        <div class="space-y-1">
            @foreach ($nodes as $node)
                <div class="rounded-xl overflow-hidden border" style="border-color:#E2E8F2">
                    {{-- Node Row --}}
                    <button type="button"
                            wire:click="toggle('{{ $node['id'] }}')"
                            class="w-full flex items-start gap-3 px-4 py-3 text-left transition-colors hover:bg-blue-50 {{ in_array($node['id'], $expanded, true) ? 'bg-blue-50' : 'bg-white' }}">
                        {{-- Expand Icon --}}
                        <span class="w-5 h-5 mt-0.5 flex-shrink-0 flex items-center justify-center rounded transition-transform"
                              style="color:#94A3B8; transform: rotate({{ in_array($node['id'], $expanded, true) ? '90' : '0' }}deg)">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </span>

                        {{-- Code --}}
                        <span class="font-mono text-sm font-bold flex-shrink-0 mt-0.5" style="color:#1A5FBE">
                            {{ $node['code'] }}
                        </span>

                        {{-- Title --}}
                        <span class="text-sm font-medium flex-1 text-left" style="color:#0F1923">
                            {{ $node['title'] }}
                        </span>
                    </button>

                    {{-- Children --}}
                    @if (in_array($node['id'], $expanded, true) && !empty($node['children']))
                        <div class="border-t divide-y" style="border-color:#E2E8F2; divide-color:#F1F5F9; background:#F8F9FC">
                            @foreach ($node['children'] as $child)
                                <a href="{{ route('code.show', [$standard, $child['code']]) }}"
                                   class="flex items-start gap-3 px-4 py-2.5 hover:bg-white transition-colors group">
                                    <span class="w-5 flex-shrink-0"></span>
                                    <span class="w-2 h-2 rounded-full flex-shrink-0 mt-1.5" style="background:#E2E8F2"></span>
                                    <span class="font-mono text-xs font-semibold flex-shrink-0 mt-0.5" style="color:#5A6A7F">
                                        {{ $child['code'] }}
                                    </span>
                                    <span class="text-sm flex-1 group-hover:text-blue-700 transition-colors" style="color:#0F1923">
                                        {{ $child['title'] }}
                                    </span>
                                    <svg class="w-4 h-4 flex-shrink-0 mt-0.5 opacity-0 group-hover:opacity-100 transition-opacity" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color:#1A5FBE">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                    </svg>
                                </a>
                            @endforeach
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    @else
        <div class="rounded-2xl border py-12 text-center" style="border-color:#E2E8F2">
            <p class="text-sm" style="color:#94A3B8">Дерево класифікатора порожнє. Завантажте дані через адмін-панель.</p>
        </div>
    @endif
</div>
