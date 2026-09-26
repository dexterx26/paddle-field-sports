@extends('layouts.app')

@section('content')
<div class="py-12 px-4 sm:px-6 lg:px-8 max-w-2xl mx-auto space-y-6">
    <div class="text-center space-y-2">
        <h2 class="text-2xl font-black text-white">Matching Reservations</h2>
        <p class="text-xs text-slate-400">Found multiple bookings for phone search: <span class="font-mono text-lime-400">{{ $query }}</span></p>
    </div>

    <div class="space-y-4">
        @foreach($bookings as $b)
            <div class="p-5 rounded-3xl glass-card border border-white/10 flex items-center justify-between hover:border-lime-400/40 transition-all">
                <div class="space-y-1 text-xs">
                    <div class="flex items-center gap-2">
                        <span class="font-mono font-bold text-lime-400 text-sm">{{ $b->booking_reference }}</span>
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase
                            {{ $b->booking_status === 'confirmed' ? 'bg-emerald-500/20 text-emerald-400 border border-emerald-500/30' : '' }}
                            {{ $b->booking_status === 'pending_approval' ? 'bg-amber-500/20 text-amber-400 border border-amber-500/30' : '' }}
                            {{ $b->booking_status === 'held' ? 'bg-sky-500/20 text-sky-400 border border-sky-500/30' : '' }}">
                            {{ str_replace('_', ' ', $b->booking_status) }}
                        </span>
                    </div>
                    <p class="text-white font-semibold">{{ $b->court->name }} • {{ $b->booking_date->format('M d, Y') }}</p>
                    <p class="text-slate-400">{{ date('g:i A', strtotime($b->start_time)) }} - {{ date('g:i A', strtotime($b->end_time)) }} • Total: {{ $b->formatted_amount }}</p>
                </div>

                <a href="{{ route('booking.track', $b->booking_reference) }}" class="px-4 py-2 rounded-xl bg-lime-400 hover:bg-lime-300 text-slate-950 font-bold text-xs shadow-md shadow-lime-500/20 transition-all">
                    View Tracker &rarr;
                </a>
            </div>
        @endforeach
    </div>

    <div class="text-center pt-4">
        <a href="{{ route('home') }}" class="text-xs text-slate-400 hover:text-white">
            &larr; Back to Main Page
        </a>
    </div>
</div>
@endsection
