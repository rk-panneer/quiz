@extends('layouts.quiz')

@section('content')
    <div class="max-w-4xl mx-auto space-y-12 animate-fade-in">
        <!-- Professional Diagnostic Result Interface -->
        <div class="glass-card rounded-[3.5rem] p-10 relative overflow-hidden group shadow-2xl space-y-6">
            <div class="absolute top-0 left-0 w-full h-3 bg-gradient-to-r from-primary-600 via-indigo-600 to-violet-600">
            </div>

            <div class="text-center space-y-4">
                <div
                    class="w-20 h-20 bg-green-500 text-white rounded-3xl mx-auto flex items-center justify-center shadow-2xl shadow-green-200 mb-8 rotate-3 transition-transform group-hover:rotate-0">
                    <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="4" d="M5 13l4 4L19 7"></path>
                    </svg>
                </div>
                <h2 class="text-5xl font-black text-slate-900 tracking-tighter uppercase leading-none">Diagnostic Success
                </h2>
                <p class="text-slate-400 font-bold tracking-widest uppercase text-xs">Environment: {{ $quiz->title }}</p>
            </div>

            <!-- Score Module -->
            <div class="flex flex-col md:flex-row items-center justify-center gap-12">
                <div class="relative w-64 h-64" x-data="{ count: 0 }" x-init="setTimeout(() => { 
                                    let target = {{ $attempt->total_score }};
                                    let interval = setInterval(() => { if (count >= target) clearInterval(interval); else count++; }, 30);
                                }, 500)">
                    <svg class="w-full h-full transform -rotate-90 drop-shadow-2xl">
                        <circle cx="128" cy="128" r="110" stroke="#f1f5f9" stroke-width="16" fill="transparent" />
                        @php $scoreRatio = $maxScore > 0 ? ($attempt->total_score / $maxScore) : 0; @endphp
                        <circle cx="128" cy="128" r="110" stroke="url(#gradientScoreResult)" stroke-width="16"
                                    fill="transparent" stroke-dasharray="691.1"
                                    stroke-dashoffset="{{ 691.1 - (691.1 * $scoreRatio) }}"
                                    class="transition-all duration-1000 ease-out" />
                        <defs>
                            <linearGradient id="gradientScoreResult" x1="0%" y1="0%" x2="100%" y2="100%">
                                <stop offset="0%" stop-color="#4f46e5" />
                                <stop offset="100%" stop-color="#9333ea" />
                            </linearGradient>
                        </defs>
                    </svg>
                    <div class="absolute inset-0 flex flex-col items-center justify-center text-center">
                        <span class="text-7xl font-black text-slate-900 tracking-tighter tabular-nums"
                            x-text="count"></span>
                        <div class="h-1 w-12 bg-slate-100 mx-auto my-1"></div>
                        <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Global Points</span>
                    </div>
                </div>

                <div class="flex-grow max-w-sm space-y-6">
                    <div
                        class="p-8 bg-slate-900 rounded-[2.5rem] text-white shadow-2xl relative overflow-hidden group/rank">
                        <div class="absolute top-0 right-0 p-4 opacity-10">
                            <svg class="w-16 h-16 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path
                                    d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z">
                                </path>
                            </svg>
                        </div>
                        @php $percentage = (int) round($scoreRatio * 100); @endphp
                        <div class="space-y-4">
                            <span
                                class="text-[10px] font-black text-primary-400 uppercase tracking-[0.3em] leading-none">Diagnostic
                                Rank</span>
                            <div class="text-3xl font-black tracking-tighter leading-none">
                                @if($percentage >= 90) ULTIMATE LEVEL
                                @elseif($percentage >= 70) MASTERY FOUND
                                @elseif($percentage >= 50) BALANCED STATE
                                @else LEARNING PATH
                                @endif
                            </div>
                            <div class="flex items-center gap-4 border-t border-white/10 pt-4">
                                <span
                                    class="text-4xl font-black tracking-tighter text-primary-500">{{ $percentage }}%</span>
                                <span
                                    class="text-[8px] font-bold text-white/40 uppercase tracking-widest leading-none">Cognitive<br>Efficiency</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Propagation Share Module -->
            <div class="py-6 px-8 bg-slate-50 rounded-[3rem] border border-slate-100 text-center space-y-8">
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-[0.4em]">Propagate Success to Networks
                </p>
                <div class="flex flex-wrap justify-center gap-6">
                    @php
                        $shareText = "Diagnostic Complete: Scored {$percentage}% on {$quiz->title}. Test your knowledge at:";
                        $shareUrl = route('quiz.show', $quiz->slug);
                    @endphp
                    <a href="https://twitter.com/intent/tweet?text={{ urlencode($shareText) }}&url={{ urlencode($shareUrl) }}"
                        target="_blank" rel="noopener noreferrer" aria-label="Share on Twitter"
                        class="w-16 h-16 bg-white shadow-xl rounded-2xl flex items-center justify-center hover:-translate-y-2 hover:bg-[#1DA1F2] hover:text-white transition-all duration-300">
                        <svg class="w-7 h-7 fill-current" viewBox="0 0 24 24">
                            <path
                                d="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.84 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z" />
                        </svg>
                    </a>
                    <a href="https://api.whatsapp.com/send?text={{ urlencode($shareText . ' ' . $shareUrl) }}"
                        target="_blank" rel="noopener noreferrer" aria-label="Share on WhatsApp"
                        class="w-16 h-16 bg-white shadow-xl rounded-2xl flex items-center justify-center hover:-translate-y-2 hover:bg-[#25D366] hover:text-white transition-all duration-300">
                        <svg class="w-7 h-7 fill-current" viewBox="0 0 24 24">
                            <path
                                d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z" />
                        </svg>
                    </a>
                </div>
            </div>

            <div class="flex flex-col sm:flex-row gap-6 pt-8">
                <a href="{{ route('quiz.show', $quiz->slug) }}"
                    class="flex-1 btn-premium text-white font-black py-4 rounded-[2.5rem] tracking-widest uppercase shadow-2xl hover:translate-y-[-4px] flex items-center justify-center gap-4 transition-all">
                    Re-Deploy Diagnostic
                </a>
                <a href="{{ route('home') }}"
                    class="flex-1 bg-white border-2 border-slate-100 text-slate-900 font-black py-4 rounded-[2.5rem] tracking-widest uppercase hover:bg-slate-50 transition-all flex items-center justify-center gap-4">
                    Return to Hub
                </a>
            </div>
        </div>
    </div>
@endsection