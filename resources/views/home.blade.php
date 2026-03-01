@extends('layouts.quiz')

@section('content')
    <div class="space-y-24">
        <!-- Professional Hero Section -->
        <div class="max-w-4xl mx-auto text-center space-y-10 group">
            <div class="inline-flex items-center gap-3 px-6 py-2 rounded-full border border-primary-100 bg-white/60 backdrop-blur-md shadow-sm animate-fade-in transition-all group-hover:shadow-md group-hover:translate-y-[-2px]">
                <span class="flex h-2 w-2 rounded-full bg-primary-600 animate-pulse"></span>
                <span class="text-xs font-bold uppercase tracking-[0.2em] text-primary-900">Expert Curated Content</span>
            </div>
            
            <h2 class="text-6xl md:text-[5rem] font-bold leading-[1.05] tracking-tighter text-slate-900">
                Sharpen Your <span class="text-primary-600 italic">Edge.</span><br>
                Master Your Domain.
            </h2>
            
            <p class="text-xl text-slate-500 max-w-2xl mx-auto leading-relaxed font-medium">
                Challenge your cognitive performance with our interactive research-backed quizzes designed for continuous learning.
            </p>
        </div>

        <!-- Section Control -->
        <div class="space-y-12">
            <div class="flex items-end justify-between border-b-2 border-slate-100 pb-8">
                <div class="space-y-2">
                    <span class="text-xs font-bold uppercase tracking-widest text-primary-500">Live Dashboard</span>
                    <h3 class="text-3xl font-bold tracking-tight text-slate-900 uppercase">Available Challenges</h3>
                </div>
                <div class="flex items-center gap-2">
                    <span class="text-xs font-bold uppercase tracking-widest text-slate-400 transition-colors">Sort by Popularity</span>
                    <svg class="w-4 h-4 text-slate-300 transition-all" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M19 9l-7 7-7-7"></path></svg>
                </div>
            </div>

            @if($quizzes->isEmpty())
                <div class="glass-card rounded-[2.5rem] p-24 text-center border-dashed border-2">
                    <div class="w-24 h-24 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-8 border border-slate-100 shadow-inner">
                        <svg class="w-10 h-10 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path></svg>
                    </div>
                    <p class="text-slate-500 font-bold tracking-tight uppercase text-lg">Infrastructure is ready. Waiting for content injection.</p>
                </div>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 gap-10">
                    @foreach($quizzes as $quiz)
                        <div class="group relative">
                            <!-- Premium Card Decoration -->
                            <div class="absolute inset-0 bg-primary-600/5 rounded-[2.5rem] blur-2xl opacity-0 group-hover:opacity-100 transition-all duration-700"></div>
                            
                            <a href="{{ route('quiz.show', $quiz->slug) }}"
                                class="relative block glass-card rounded-[2.5rem] p-10 transition-all duration-500 hover:-translate-y-3 group-hover:border-primary-200">
                                
                                <div class="flex justify-between items-start mb-8">
                                    <div class="p-4 bg-primary-600 text-white rounded-2xl shadow-xl shadow-primary-200 rotate-3 group-hover:rotate-0 transition-transform duration-500">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"></path></svg>
                                    </div>
                                    <div class="flex flex-col items-end">
                                        <span class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-1">Duration</span>
                                        <span class="text-sm font-black text-slate-900 uppercase tracking-tighter">{{ round($quiz->questions_count * 1.5) }} Minutes</span>
                                    </div>
                                </div>

                                <h3 class="text-3xl font-black text-slate-900 group-hover:text-primary-600 transition-all tracking-tighter mb-4 leading-[1.1]">
                                    {{ $quiz->title }}
                                </h3>

                                <div class="flex flex-wrap gap-2 mb-8">
                                    <span class="px-3 py-1 bg-primary-50 text-primary-700 text-[10px] font-black uppercase rounded-lg tracking-widest">{{ $quiz->questions_count }} Questions</span>
                                    <span class="px-3 py-1 bg-green-50 text-green-700 text-[10px] font-black uppercase rounded-lg tracking-widest">Certified</span>
                                    <span class="px-3 py-1 bg-indigo-50 text-indigo-700 text-[10px] font-black uppercase rounded-lg tracking-widest">Interactive</span>
                                </div>

                                <div class="flex items-center justify-between group/action">
                                    <span class="text-sm font-black uppercase tracking-widest text-slate-900 group-hover:text-primary-600 transition-colors">Enter Environment</span>
                                    <div class="h-12 w-12 bg-slate-900 group-hover:bg-primary-600 rounded-full flex items-center justify-center text-white transition-all shadow-xl group-hover:shadow-primary-200 group-hover:translate-x-1">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M13 7l5 5m0 0l-5 5m5-5H6"></path></svg>
                                    </div>
                                </div>
                            </a>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
@endsection