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
                        @if($file && is_object($file) && method_exists($file, 'getClientOriginalName'))
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

    <!-- Синхронізація з Укрстатом -->
    <div class="mt-12 pt-8 border-t border-[--color-border]">
        <h4 class="text-lg font-bold text-[--color-text] mb-2">Пряма синхронізація з Укрстатом</h4>
        <p class="text-[--color-text-muted] mb-6 text-sm">
            Ця функція автоматично сканує сайт Укрстату, створює 4-рівневу іерархію (Секції, Розділи, Групи, Класи) 
            та наповнює базу офіційними описами КВЕД-2010.
        </p>

        <div class="bg-[--color-warning-bg] border border-[--color-warning] p-4 rounded-lg mb-6">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-[--color-warning]" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-xs text-[--color-warning]">
                        <strong>Увага!</strong> Режим "Strict 4-level" очистить існуючі дані КВЕД-2010 перед імпортом для забезпечення цілісності ієрархії.
                    </p>
                </div>
            </div>
        </div>

        <div class="flex items-center justify-between">
            <div wire:loading wire:target="syncFromUkrstat" class="flex items-center text-sm text-[--color-info]">
                <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-[--color-info]" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Йде сканування Укрстату... Це може зайняти 1-2 хвилини.
            </div>
            
            <button 
                type="button" 
                wire:click="syncFromUkrstat" 
                wire:loading.attr="disabled"
                wire:target="syncFromUkrstat"
                class="bg-white hover:bg-gray-50 text-[--color-primary] border border-[--color-primary] font-medium py-2 px-6 rounded-md shadow-sm transition disabled:opacity-50"
            >
                Запустити синхронізацію (4 рівні)
            </button>
        </div>
    </div>
</div>

