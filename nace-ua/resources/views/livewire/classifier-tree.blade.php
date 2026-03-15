<div>
    <p>Дерево класифікатора ({{ strtoupper($standard) }})</p>

    <ul>
        @foreach ($nodes as $node)
            <li>
                <button type="button" wire:click="toggle('{{ $node['id'] }}')">
                    {{ in_array($node['id'], $expanded, true) ? '−' : '+' }}
                </button>
                <span class="font-mono">{{ $node['code'] }}</span>
                — {{ $node['title'] }}

                @if (in_array($node['id'], $expanded, true) && ! empty($node['children']))
                    <ul>
                        @foreach ($node['children'] as $child)
                            <li>
                                <span class="font-mono">{{ $child['code'] }}</span>
                                — {{ $child['title'] }}
                            </li>
                        @endforeach
                    </ul>
                @endif
            </li>
        @endforeach
    </ul>
</div>

