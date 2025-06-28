<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Nova Mensagem Agendada
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <form action="{{ route('scheduled-messages.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Título -->
                            <div class="md:col-span-2">
                                <label for="title" class="block text-sm font-medium text-gray-700">Título</label>
                                <input type="text" name="title" id="title" value="{{ old('title') }}" 
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                                @error('title')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Mensagem -->
                            <div class="md:col-span-2">
                                <label for="message" class="block text-sm font-medium text-gray-700">Mensagem</label>
                                <textarea name="message" id="message" rows="4" 
                                          class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>{{ old('message') }}</textarea>
                                @error('message')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Tipo de Destinatário -->
                            <div>
                                <label for="recipient_type" class="block text-sm font-medium text-gray-700">Tipo de Destinatário</label>
                                <select name="recipient_type" id="recipient_type" 
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                                    <option value="">Selecione...</option>
                                    <option value="user" {{ old('recipient_type') == 'user' ? 'selected' : '' }}>Usuário</option>
                                    <option value="group" {{ old('recipient_type') == 'group' ? 'selected' : '' }}>Grupo</option>
                                </select>
                                @error('recipient_type')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Destinatário -->
                            <div>
                                <label for="recipient_id" class="block text-sm font-medium text-gray-700">Destinatário</label>
                                <select name="recipient_id" id="recipient_id" 
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                                    <option value="">Selecione o tipo primeiro...</option>
                                </select>
                                @error('recipient_id')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Data e Hora -->
                            <div>
                                <label for="scheduled_at" class="block text-sm font-medium text-gray-700">Data e Hora</label>
                                <input type="datetime-local" name="scheduled_at" id="scheduled_at" value="{{ old('scheduled_at') }}" 
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                                @error('scheduled_at')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Tipo de Recorrência -->
                            <div>
                                <label for="recurrence_type" class="block text-sm font-medium text-gray-700">Recorrência</label>
                                <select name="recurrence_type" id="recurrence_type" 
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                                    <option value="none" {{ old('recurrence_type') == 'none' ? 'selected' : '' }}>Única</option>
                                    <option value="daily" {{ old('recurrence_type') == 'daily' ? 'selected' : '' }}>Diária</option>
                                    <option value="weekly" {{ old('recurrence_type') == 'weekly' ? 'selected' : '' }}>Semanal</option>
                                    <option value="monthly" {{ old('recurrence_type') == 'monthly' ? 'selected' : '' }}>Mensal</option>
                                    <option value="yearly" {{ old('recurrence_type') == 'yearly' ? 'selected' : '' }}>Anual</option>
                                </select>
                                @error('recurrence_type')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Intervalo de Recorrência -->
                            <div>
                                <label for="recurrence_interval" class="block text-sm font-medium text-gray-700">Intervalo</label>
                                <input type="number" name="recurrence_interval" id="recurrence_interval" value="{{ old('recurrence_interval', 1) }}" min="1"
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                                @error('recurrence_interval')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Mídia -->
                            <div class="md:col-span-2">
                                <label for="media" class="block text-sm font-medium text-gray-700">Mídia (Opcional)</label>
                                <input type="file" name="media" id="media" accept="image/*,video/*"
                                       class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                                <p class="mt-1 text-sm text-gray-500">Formatos aceitos: JPG, PNG, GIF, MP4, MOV, AVI (máx. 50MB)</p>
                                @error('media')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div class="mt-6 flex justify-end space-x-3">
                            <a href="{{ route('scheduled-messages.index') }}" 
                               class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded">
                                Cancelar
                            </a>
                            <button type="submit" 
                                    class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                Agendar Mensagem
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('recipient_type').addEventListener('change', function() {
            const type = this.value;
            const recipientSelect = document.getElementById('recipient_id');
            
            recipientSelect.innerHTML = '<option value="">Carregando...</option>';
            
            if (type === 'user') {
                recipientSelect.innerHTML = '<option value="">Selecione um usuário...</option>';
                @foreach($users as $user)
                    recipientSelect.innerHTML += '<option value="{{ $user->telegram_id }}">{{ $user->full_name }} (@{{ $user->username ?? $user->telegram_id }})</option>';
                @endforeach
            } else if (type === 'group') {
                recipientSelect.innerHTML = '<option value="">Selecione um grupo...</option>';
                @foreach($groups as $group)
                    recipientSelect.innerHTML += '<option value="{{ $group->telegram_id }}">{{ $group->title }}</option>';
                @endforeach
            } else {
                recipientSelect.innerHTML = '<option value="">Selecione o tipo primeiro...</option>';
            }
        });
    </script>
</x-app-layout>

