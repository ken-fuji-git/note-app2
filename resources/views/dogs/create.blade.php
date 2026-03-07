<x-app-layout>
    <x-slot name="header">
        <h2 class="font-bold text-xl text-slate-900">おかげ犬 - 犬の登録</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-lg mx-auto px-4">
            <div class="card">
                <div class="p-6">
                    <p class="text-sm text-slate-500 mb-6">お伊勢参りに行く犬の情報を登録してください。</p>

                    @if($errors->any())
                        <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-xl">
                            <ul class="space-y-1">
                                @foreach($errors->all() as $error)
                                    <li class="text-sm text-red-600">{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('dogs.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="space-y-5">
                            {{-- 写真 --}}
                            <div>
                                <label class="form-label">写真 <span class="text-red-500">*</span></label>
                                <div class="mt-1">
                                    @if($dog && $dog->photo_path)
                                        <img src="{{ asset('storage/' . $dog->photo_path) }}" alt="{{ $dog->name }}"
                                            class="w-32 h-32 object-cover rounded-xl mb-3">
                                    @endif
                                    <input type="file" name="photo" accept="image/*"
                                        class="block w-full text-sm text-slate-500 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100"
                                        {{ $dog ? '' : 'required' }}>
                                </div>
                            </div>

                            {{-- 名前 --}}
                            <div>
                                <label class="form-label">名前 <span class="text-red-500">*</span></label>
                                <input type="text" name="name" value="{{ old('name', $dog?->name) }}"
                                    class="form-input" placeholder="例：たろう">
                            </div>

                            {{-- 性別 --}}
                            <div>
                                <label class="form-label">性別 <span class="text-red-500">*</span></label>
                                <div class="flex gap-4 mt-1">
                                    <label class="flex items-center gap-2 cursor-pointer">
                                        <input type="radio" name="gender" value="オス"
                                            {{ old('gender', $dog?->gender) === 'オス' ? 'checked' : '' }}
                                            class="text-indigo-600 focus:ring-indigo-500">
                                        <span class="text-sm text-slate-700">オス</span>
                                    </label>
                                    <label class="flex items-center gap-2 cursor-pointer">
                                        <input type="radio" name="gender" value="メス"
                                            {{ old('gender', $dog?->gender) === 'メス' ? 'checked' : '' }}
                                            class="text-indigo-600 focus:ring-indigo-500">
                                        <span class="text-sm text-slate-700">メス</span>
                                    </label>
                                </div>
                            </div>

                            {{-- 年齢 --}}
                            <div>
                                <label class="form-label">年齢 <span class="text-red-500">*</span></label>
                                <div class="flex items-center gap-2">
                                    <input type="number" name="age" value="{{ old('age', $dog?->age) }}"
                                        class="form-input w-24" min="0" max="30">
                                    <span class="text-sm text-slate-500">歳</span>
                                </div>
                            </div>

                            {{-- 犬種 --}}
                            <div>
                                <label class="form-label">犬種 <span class="text-red-500">*</span></label>
                                <input type="text" name="breed" value="{{ old('breed', $dog?->breed) }}"
                                    class="form-input" placeholder="例：柴犬">
                            </div>

                            {{-- 体高 --}}
                            <div>
                                <label class="form-label">体高 <span class="text-red-500">*</span></label>
                                <div class="flex items-center gap-2">
                                    <input type="number" name="height" value="{{ old('height', $dog?->height) }}"
                                        class="form-input w-24" min="5" max="120">
                                    <span class="text-sm text-slate-500">cm</span>
                                </div>
                            </div>

                            {{-- 性格 --}}
                            <div>
                                <label class="form-label">性格 <span class="text-red-500">*</span></label>
                                <input type="text" name="personality" value="{{ old('personality', $dog?->personality) }}"
                                    class="form-input" placeholder="例：好奇心旺盛、甘えん坊">
                            </div>

                            <div class="pt-3 border-t border-slate-100">
                                <button type="submit" class="btn-primary w-full justify-center">
                                    {{ $dog ? '更新して出発画面へ' : '登録して出発画面へ' }}
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
