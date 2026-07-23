@props(['status' => 'unassigned', 'uid' => null])

@if($status === 'active' || $status === 'terhubung')
    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200/60 text-xs font-medium">
        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
        <span>RFID Active</span>
        @if($uid)
            <span class="text-emerald-500/80 font-mono text-[10px]">({{ $uid }})</span>
        @endif
    </span>
@else
    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-slate-100 text-slate-600 border border-slate-200/80 text-xs font-medium">
        <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span>
        <span>Unassigned</span>
    </span>
@endif