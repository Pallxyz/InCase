<x-layouts.auth title="Daftar — InCase">
    <div class="w-full max-w-2xl rounded-3xl border border-border/70 bg-white/80 p-8 shadow-[0_24px_60px_-24px_rgba(15,23,42,0.15)] backdrop-blur-xl sm:p-10">
        {{-- Header --}}
        <div class="flex items-center gap-3 lg:hidden">
            <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-primary text-primary-foreground">
                <x-icon.viewfinder-circle class="h-5 w-5" />
            </span>
            <span class="text-lg font-bold tracking-tight text-foreground">InCase</span>
        </div>

        <div class="mt-6 lg:mt-0">
            <h1 class="text-2xl font-bold tracking-tight text-foreground sm:text-3xl">
                Buat Akun Kamu
            </h1>
            <p class="mt-2 text-sm leading-relaxed text-muted-foreground">
                Daftar untuk mulai memantau kelengkapan tas sekolahmu dengan InCase.
            </p>
        </div>

        <form method="POST" action="{{ route('register') }}" class="mt-8 flex flex-col gap-5">
            @csrf

            {{-- Baris 1 — 2 kolom: Nama Lengkap | Email --}}
            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                <x-auth-input
                    icon="user"
                    label="Nama Lengkap"
                    name="name"
                    type="text"
                    placeholder="Nama lengkap kamu"
                    required
                    autofocus
                    autocomplete="name"
                />

                <x-auth-input
                    icon="envelope"
                    label="Email"
                    name="email"
                    type="email"
                    placeholder="nama@sekolah.sch.id"
                    required
                    autocomplete="username"
                />
            </div>

           {{-- Baris 2 — Sekolah --}}
            <x-auth-input
                icon="building-library"
                label="Sekolah"
                name="school_name"
                type="text"
                placeholder="Nama sekolah"
                required
                autocomplete="organization"
                value="{{ old('school_name') }}"
            />

            {{-- Hari Sekolah --}}
            <div>
                <label for="days_per_week" class="mb-1.5 flex items-center gap-1.5 text-sm font-medium text-foreground">
                    Hari Sekolah
                    <span class="text-xs font-normal text-muted-foreground">(kalau sekolah baru)</span>
                </label>
                <div class="relative">
                    <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-muted-foreground">
                        <x-icon.calendar class="h-5 w-5" />
                    </span>
                    <select
                        name="days_per_week"
                        id="days_per_week"
                        class="block w-full appearance-none rounded-xl border border-border bg-background py-2.5 pl-11 pr-3.5 text-sm text-foreground transition-colors focus:border-primary focus:outline-none focus:ring-4 focus:ring-primary/10"
                    >
                        <option value="5" {{ old('days_per_week', '5') == '5' ? 'selected' : '' }}>Senin – Jumat</option>
                        <option value="6" {{ old('days_per_week') == '6' ? 'selected' : '' }}>Senin – Sabtu</option>
                    </select>
                </div>
                @error('days_per_week')
                    <p class="mt-1.5 text-xs font-medium text-destructive">{{ $message }}</p>
                @enderror
            </div>

            {{-- Tingkat & Kelas (dropdown kalau sekolah udah ada, manual kalau belum) --}}
            <div id="existing-school-fields" class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                <div>
                    <label for="grade-select" class="mb-1.5 block text-sm font-medium text-foreground">
                        Tingkat
                    </label>
                    <div class="relative">
                        <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-muted-foreground">
                            <x-icon.academic-cap class="h-5 w-5" />
                        </span>
                        <select
                            id="grade-select"
                            class="block w-full appearance-none rounded-xl border border-border bg-background py-2.5 pl-11 pr-3.5 text-sm text-foreground transition-colors focus:border-primary focus:outline-none focus:ring-4 focus:ring-primary/10"
                        >
                            <option value="" disabled selected>Pilih tingkat</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label for="class-select" class="mb-1.5 block text-sm font-medium text-foreground">
                        Kelas
                    </label>
                    <div class="relative">
                        <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-muted-foreground">
                            <x-icon.tag class="h-5 w-5" />
                        </span>
                        <select
                            name="class_id"
                            id="class-select"
                            class="block w-full appearance-none rounded-xl border border-border bg-background py-2.5 pl-11 pr-3.5 text-sm text-foreground transition-colors focus:border-primary focus:outline-none focus:ring-4 focus:ring-primary/10"
                        >
                            <option value="" disabled selected>Pilih tingkat dulu</option>
                        </select>
                    </div>
                    @error('class_id')
                        <p class="mt-1.5 text-xs font-medium text-destructive">{{ $message }}</p>
                    @enderror

                    {{-- Muncul kalau user pilih "+ Kelas belum ada" di dropdown atas --}}
                    <div id="new-class-inline" class="mt-2.5 hidden">
                        <input
                            type="text"
                            name="new_class_name"
                            id="new_class_name_inline"
                            placeholder="Nama kelas baru, contoh: VII B"
                            class="block w-full rounded-xl border border-border bg-background px-3.5 py-2.5 text-sm text-foreground placeholder:text-muted-foreground/70 focus:border-primary focus:outline-none focus:ring-4 focus:ring-primary/10"
                        >
                        <input type="hidden" name="new_class_grade" id="new_class_grade_inline" value="">
                        @error('new_class_name')
                            <p class="mt-1.5 text-xs font-medium text-destructive">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            {{-- Muncul kalau sekolah belum terdaftar sama sekali (belum ada kelas apapun) --}}
            <div id="new-school-fields" class="hidden rounded-xl border border-dashed border-warning/40 bg-warning/5 p-4">
                <p class="text-xs font-medium text-warning">
                    Sekolah ini belum terdaftar. Isi manual buat bikin kelas pertama di sekolahmu.
                </p>
                <div class="mt-3 grid grid-cols-1 gap-3 sm:grid-cols-2">
                    <div>
                        <label for="new_class_grade" class="mb-1.5 block text-xs font-medium text-foreground">
                            Tingkat
                        </label>
                        <input
                            type="text"
                            name="new_class_grade"
                            id="new_class_grade"
                            placeholder="Contoh: X, VII, 10"
                            class="block w-full rounded-xl border border-border bg-background px-3.5 py-2.5 text-sm text-foreground placeholder:text-muted-foreground/70 focus:border-primary focus:outline-none focus:ring-4 focus:ring-primary/10"
                        >
                        @error('new_class_grade')
                            <p class="mt-1.5 text-xs font-medium text-destructive">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="new_class_name_fresh" class="mb-1.5 block text-xs font-medium text-foreground">
                            Nama Kelas
                        </label>
                        <input
                            type="text"
                            name="new_class_name"
                            id="new_class_name_fresh"
                            placeholder="Contoh: X IPA 1, VII A"
                            class="block w-full rounded-xl border border-border bg-background px-3.5 py-2.5 text-sm text-foreground placeholder:text-muted-foreground/70 focus:border-primary focus:outline-none focus:ring-4 focus:ring-primary/10"
                        >
                    </div>
                </div>
                <input type="hidden" name="school_type" id="school_type_input" value="">
            </div>

            {{-- Baris — 2 kolom: Kata Sandi | Konfirmasi Kata Sandi --}}
            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                <div>
                    <label for="password" class="mb-1.5 block text-sm font-medium text-foreground">
                        Kata Sandi
                    </label>
                    <div class="relative">
                        <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-muted-foreground">
                            <x-icon.lock-closed class="h-5 w-5" />
                        </span>
                        <input
                            type="password"
                            id="password"
                            name="password"
                            placeholder="••••••••"
                            required
                            autocomplete="new-password"
                            class="block w-full rounded-xl border border-border bg-background py-2.5 pl-11 pr-3.5 text-sm text-foreground placeholder:text-muted-foreground/70 transition-colors focus:border-primary focus:outline-none focus:ring-4 focus:ring-primary/10"
                        >
                    </div>
                    @error('password')
                        <p class="mt-1.5 text-xs font-medium text-destructive">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password_confirmation" class="mb-1.5 block text-sm font-medium text-foreground">
                        Konfirmasi Kata Sandi
                    </label>
                    <div class="relative">
                        <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-muted-foreground">
                            <x-icon.lock-closed class="h-5 w-5" />
                        </span>
                        <input
                            type="password"
                            id="password_confirmation"
                            name="password_confirmation"
                            placeholder="••••••••"
                            required
                            autocomplete="new-password"
                            class="block w-full rounded-xl border border-border bg-background py-2.5 pl-11 pr-3.5 text-sm text-foreground placeholder:text-muted-foreground/70 transition-colors focus:border-primary focus:outline-none focus:ring-4 focus:ring-primary/10"
                        >
                    </div>
                </div>
            </div>

            <button
                type="submit"
                class="mt-2 inline-flex w-full items-center justify-center gap-2 rounded-full bg-primary py-3 text-sm font-semibold text-primary-foreground shadow-sm transition-colors hover:bg-primary/90"
            >
                Daftar
                <x-icon.arrow-right class="h-4 w-4" />
            </button>
        </form>

        <p class="mt-8 text-center text-sm text-muted-foreground">
            Sudah punya akun?
            <a href="{{ route('login') }}" class="font-semibold text-primary hover:underline">
                Masuk di sini
            </a>
        </p>
    </div>

    @php
        $classesData = $classes->map(function ($c) {
            return [
                'id' => $c->id,
                'grade' => $c->grade,
                'label' => $c->name,
                'school_name' => $c->school_name,
            ];
        });

        $schoolsData = $schools->map(function ($s) {
            return ['name' => $s->name, 'type' => $s->type];
        });
    @endphp

    <script>
        const classesData = @json($classesData);
        const schoolsData = @json($schoolsData);

        const STANDARD_GRADES = {
            SMP: ['VII', 'VIII', 'IX'],
            SMA: ['X', 'XI', 'XII'],
            SMK: ['X', 'XI', 'XII'],
        };

        const schoolInput = document.querySelector('input[name="school_name"]');
        const gradeSelect = document.getElementById('grade-select');
        const classSelect = document.getElementById('class-select');
        const existingFields = document.getElementById('existing-school-fields');
        const newFields = document.getElementById('new-school-fields');
        const newGradeFresh = document.getElementById('new_class_grade');
        const newNameFresh = document.getElementById('new_class_name_fresh');
        const schoolTypeInput = document.getElementById('school_type_input');

        const newClassInline = document.getElementById('new-class-inline');
        const newNameInline = document.getElementById('new_class_name_inline');
        const newGradeInline = document.getElementById('new_class_grade_inline');

        function normalize(str) {
            return str.trim().toLowerCase().replace(/\s+/g, ' ');
        }

        function detectSchoolType(name) {
            const n = normalize(name);
            if (n.includes('smk')) return 'SMK';
            if (n.includes('sma')) return 'SMA';
            if (n.includes('smp')) return 'SMP';
            return '';
        }

        function findSchool(name) {
            const n = normalize(name);
            return schoolsData.find(function (s) { return normalize(s.name) === n; });
        }

        function classesForCurrentSchool() {
            const schoolName = normalize(schoolInput.value);
            return classesData.filter(function (c) { return normalize(c.school_name) === schoolName; });
        }

        function populateGrades() {
            // Reset dulu semua kondisi setiap kali sekolah diketik ulang.
            newClassInline.classList.add('hidden');
            newNameInline.required = false;
            classSelect.name = 'class_id';

            if (schoolInput.value.trim() === '') {
                existingFields.classList.remove('hidden');
                newFields.classList.add('hidden');
                classSelect.required = false;
                newGradeFresh.required = false;
                newNameFresh.required = false;
                return;
            }

            const school = findSchool(schoolInput.value);

            if (!school) {
                // Sekolah baru total, belum pernah terdaftar sama sekali.
                existingFields.classList.add('hidden');
                newFields.classList.remove('hidden');
                classSelect.required = false;
                newGradeFresh.required = true;
                newNameFresh.required = true;
                schoolTypeInput.value = detectSchoolType(schoolInput.value);
                return;
            }

            // Sekolah udah terdaftar — tampilin SEMUA tingkat standar buat jenjang ini,
            // gak cuma tingkat yang kebetulan udah ada kelasnya.
            existingFields.classList.remove('hidden');
            newFields.classList.add('hidden');
            classSelect.required = true;
            newGradeFresh.required = false;
            newNameFresh.required = false;

            const grades = STANDARD_GRADES[school.type] || STANDARD_GRADES.SMK;

            gradeSelect.innerHTML = '<option value="" disabled selected>Pilih tingkat</option>';
            classSelect.innerHTML = '<option value="" disabled selected>Pilih tingkat dulu</option>';

            grades.forEach(function (grade) {
                const opt = document.createElement('option');
                opt.value = grade;
                opt.textContent = grade;
                gradeSelect.appendChild(opt);
            });
        }

        function populateClasses(grade) {
            const filtered = classesForCurrentSchool().filter(function (c) { return c.grade === grade; });

            const options = filtered.map(function (c) {
                return '<option value="' + c.id + '">' + c.label + '</option>';
            }).join('');

            classSelect.innerHTML = '<option value="" disabled selected>Pilih kelas</option>'
                + options
                + '<option value="__new__">+ Kelas belum ada, tambah baru</option>';

            newClassInline.classList.add('hidden');
            newNameInline.required = false;
            classSelect.name = 'class_id';
        }

        schoolInput.addEventListener('input', populateGrades);

        gradeSelect.addEventListener('change', function () {
            populateClasses(this.value);
        });

        classSelect.addEventListener('change', function () {
            if (classSelect.value === '__new__') {
                newClassInline.classList.remove('hidden');
                newGradeInline.value = gradeSelect.value;
                newNameInline.required = true;
                classSelect.name = '';
            } else {
                newClassInline.classList.add('hidden');
                newNameInline.required = false;
                classSelect.name = 'class_id';
            }
        });

        @if (old('school_name'))
            populateGrades();
        @endif

        @if (old('class_id'))
            (function () {
                const oldClass = classesData.find(function (c) {
                    return String(c.id) === '{{ old('class_id') }}';
                });
                if (oldClass) {
                    gradeSelect.value = oldClass.grade;
                    populateClasses(oldClass.grade);
                    classSelect.value = oldClass.id;
                }
            })();
        @endif
    </script>
</x-layouts.auth>