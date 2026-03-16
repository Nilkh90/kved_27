<div class="bg-white p-6 rounded-xl shadow-sm border border-[--color-border]">
    <h3 class="text-2xl font-bold text-[--color-text] mb-4">Імпорт даних KVED / NACE</h3>
    <p class="text-[--color-text-muted] mb-6">Завантажте CSV-файл для оновлення бази даних.</p>

    @if ($statusMessage)
        <div class="mb-6 p-4 rounded-lg {{ $statusType === 'success' ? 'bg-[--color-success-bg] text-[--color-success]' : 'bg-[--color-danger-bg] text-[--color-danger]' }}">
            {{ $statusMessage }}
        </div>
    @endif

    <form wire:submit="import" class="space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Тип даних -->
            <div>
                <label class="block text-sm font-medium text-[--color-text] mb-2">Що імпортуємо?</label>
                <select wire:model="type" class="w-full border-gray-300 rounded-md shadow-sm focus:border-[--color-primary] focus:ring-[--color-primary]">
                    <option value="kved_2010">Довідник: КВЕД-2010</option>
                    <option value="nace_2027">Довідник: NACE 2.1-UA</option>
                    <option value="transition_mapping">Маппінг: КВЕД → NACE</option>
                </select>
                @error('type') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>

            <!-- Режим -->
            <div>
                <label class="block text-sm font-medium text-[--color-text] mb-2">Режим імпорту</label>
                <select wire:model="mode" class="w-full border-gray-300 rounded-md shadow-sm focus:border-[--color-primary] focus:ring-[--color-primary]">
                    <option value="upsert">Оновити існуючі + Додати нові (Безпечно)</option>
                    <option value="replace">Очистити таблицю і завантажити наново (Увага!)</option>
                </select>
                @error('mode') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>
        </div>

        <!-- Завантаження файлу -->
        <div>
            <label class="block text-sm font-medium text-[--color-text] mb-2">Файл (CSV, TXT, макс 10MB)</label>
            <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-[--color-border] border-dashed rounded-md bg-[--color-surface] hover:bg-gray-50 transition">
                <div class="space-y-1 text-center">
                    <svg class="mx-auto h-12 w-12 text-[--color-text-hint]" stroke="currentColor" fill="none" viewBox="0 0 48 48" aria-hidden="true">
                        <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                    <div class="flex text-sm text-gray-600 justify-center">
                        <label for="file-upload" class="relative cursor-pointer bg-white rounded-md font-medium text-[--color-primary] hover:text-[--color-primary-dark] focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-[--color-primary]">
                            <span>Завантажити файл</span>
                            <input id="file-upload" type="file" wire:model="file" class="sr-only" accept=".csv,.txt">
                        </label>
                    </div>
                    <p class="text-xs text-[--color-text-muted]">
                        @if($file)
                            Обрано: {{ $file->getClientOriginalName() }}
                        @else
                            Натисніть сюди
                        @endif
                    </p>
                </div>
            </div>
            
            <div wire:loading wire:target="file" class="mt-2 text-sm text-[--color-info]">
                Триває завантаження файлу...
            </div>
            @error('file') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
        </div>

        <!-- Кнопка Action -->
        <div class="pt-4 border-t border-[--color-border] flex items-center justify-end">
            <div wire:loading wire:target="import" class="mr-4 text-sm text-[--color-text-muted]">
                Обробка даних, зачекайте...
            </div>
            <button type="submit" wire:loading.attr="disabled" class="bg-[--color-primary] hover:bg-[--color-primary-dark] text-white font-medium py-2 px-6 rounded-md shadow transition disabled:opacity-50">
                Почати імпорт
            </button>
        </div>
    </form>
</div>
