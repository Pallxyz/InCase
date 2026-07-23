@php
    $role = strtolower($user->role ?? 'student');
@endphp

<section class="rounded-[24px] border border-border bg-card p-6 shadow-sm sm:p-8">

    @if (session('status') === 'profile-updated')
        <div class="mb-6 rounded-xl bg-success/10 px-4 py-3 text-sm font-medium text-success">
            Profil berhasil diperbarui.
        </div>
    @endif

    <div class="flex flex-col items-center gap-4 sm:flex-row">
        <span class="flex h-24 w-24 shrink-0 items-center justify-center rounded-full bg-primary text-3xl font-bold text-primary-foreground">
            {{ strtoupper(substr($user->name,0,1)) }}
        </span>

        <div class="flex flex-col items-center sm:items-start">
            <button
                type="button"
                class="inline-flex items-center gap-2 rounded-full border border-border bg-background px-4 py-2 text-xs font-semibold text-foreground transition hover:bg-muted"
            >
                <x-icon.camera class="h-4 w-4"/>
                Ganti Foto
            </button>
        </div>

        <div class="sm:ml-auto text-center sm:text-right">
            <div class="flex items-center justify-center gap-2 sm:justify-end">
                <h2 class="text-lg font-bold text-foreground">
                    {{ $user->name }}
                </h2>

                <span class="rounded-full bg-primary/10 px-2.5 py-1 text-xs font-semibold text-primary">
                    {{ ucfirst($role) }}
                </span>
            </div>

            <p class="text-sm text-muted-foreground">
                {{ $user->schoolClass->name ?? '-' }}
            </p>
        </div>
    </div>

    <div class="my-6 border-t border-border"></div>

    <form method="POST"
          action="{{ route('profile.update') }}"
          class="grid gap-5 sm:grid-cols-2">

        @csrf
        @method('PATCH')

        <div>
            <label class="mb-1.5 block text-sm font-medium text-foreground">
                Nama Lengkap
            </label>

            <x-text-input
                id="name"
                name="name"
                type="text"
                class="block w-full rounded-xl border-border bg-background"
                :value="old('name',$user->name)"
                required
            />

            <x-input-error
                :messages="$errors->get('name')"
                class="mt-2"
            />
        </div>

        <div>
            <label class="mb-1.5 block text-sm font-medium text-foreground">
                Email
            </label>

            <x-text-input
                id="email"
                name="email"
                type="email"
                class="block w-full rounded-xl border-border bg-background"
                :value="old('email',$user->email)"
                required
            />

            <x-input-error
                :messages="$errors->get('email')"
                class="mt-2"
            />

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())

                <p class="mt-2 text-xs text-warning">
                    Email belum diverifikasi.

                    <button
                        form="send-verification"
                        class="font-semibold underline"
                    >
                        Kirim ulang
                    </button>
                </p>

            @endif
        </div>

        <div>
            <label class="mb-1.5 block text-sm font-medium text-foreground">
                Role
            </label>

            <input
                type="text"
                value="{{ ucfirst($role) }}"
                disabled
                class="block w-full rounded-xl border border-border bg-muted px-3.5 py-2.5 text-sm text-muted-foreground"
            >
        </div>

        <div>
            <label class="mb-1.5 block text-sm font-medium text-foreground">
                Kelas
            </label>

            <input
                type="text"
                value="{{ $user->schoolClass->name ?? '-' }}"
                disabled
                class="block w-full rounded-xl border border-border bg-muted px-3.5 py-2.5 text-sm text-muted-foreground"
            >
        </div>

        <div class="sm:col-span-2 flex items-center gap-4 pt-2">

            <button
                type="submit"
                class="inline-flex items-center justify-center rounded-full bg-primary px-5 py-2.5 text-sm font-semibold text-primary-foreground transition hover:bg-primary/90"
            >
                Simpan Perubahan
            </button>

            @if(session('status')==='profile-updated')
                <span
                    x-data="{show:true}"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(()=>show=false,2000)"
                    class="text-sm font-medium text-success"
                >
                    Berhasil disimpan.
                </span>
            @endif

        </div>

    </form>

</section>

<form id="send-verification"
      method="POST"
      action="{{ route('verification.send') }}">
    @csrf
</form>