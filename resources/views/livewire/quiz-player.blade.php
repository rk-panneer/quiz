<div class="max-w-4xl mx-auto space-y-12">
    @if(!$isCompleted)
        @php
            $currentQuestion = $questions[$currentIndex];
            $totalCount = $questions->count();
            $progress = (($currentIndex + 1) / $totalCount) * 100;
        @endphp

        <!-- Professional Progress Bar -->
        <div
            class="glass-card rounded-3xl p-8 mb-12 flex flex-col md:flex-row items-center justify-between gap-8 border-none shadow-none bg-white/40">
            <div class="flex-grow w-full space-y-3">
                <div class="flex justify-between items-end text-[10px] font-black tracking-widest uppercase text-slate-400">
                    <span>PROGRESS MODULE</span>
                    <span class="text-primary-600 font-bold italic">{{ round($progress) }}% SYSTEM LOADED</span>
                </div>
                <div class="h-2 w-full bg-slate-100 rounded-full overflow-hidden">
                    <div class="h-full bg-gradient-to-r from-primary-500 to-indigo-600 rounded-full transition-all duration-1000 ease-in-out"
                        style="width: {{ $progress }}%"></div>
                </div>
            </div>
            <div
                class="flex-shrink-0 flex items-center gap-4 bg-white px-6 py-4 rounded-2xl shadow-sm border border-slate-100">
                @if($timeLimitSeconds !== null)
                    <div class="text-center border-r border-slate-100 pr-4 mr-2" x-data="{ 
                                            timeLeft: {{ $timeLimitSeconds }},
                                            formatTime() {
                                                const minutes = Math.floor(this.timeLeft / 60);
                                                const seconds = this.timeLeft % 60;
                                                return `${minutes}:${seconds.toString().padStart(2, '0')}`;
                                            }
                                         }"
                        x-init="setInterval(() => { if(timeLeft > 0) timeLeft-- }, 1000); $watch('timeLeft', value => { if(value <= 0) $wire.submit() })">
                        <span
                            class="block text-[8px] font-black text-slate-400 uppercase tracking-widest leading-none mb-1">Time
                            Left</span>
                        <span class="text-lg font-black tracking-tighter transition-colors tabular-nums"
                            :class="timeLeft < 60 ? 'text-red-600 animate-pulse' : 'text-slate-900'"
                            x-text="formatTime()"></span>
                    </div>
                @endif
                <div class="text-center">
                    <span
                        class="block text-[8px] font-black text-slate-400 uppercase tracking-widest leading-none mb-1">Queue</span>
                    <span class="text-lg font-black text-slate-900 tracking-tighter">{{ $currentIndex + 1 }} /
                        {{ $totalCount }}</span>
                </div>
            </div>
        </div>

        <!-- System Question Interface -->
        <div class="glass-card rounded-[3rem] p-10 md:p-16 relative overflow-hidden group shadow-2xl"
            x-data="{ answer: @entangle('answers.' . $currentQuestion->id), submitting: false }"
            wire:key="question-{{ $currentQuestion->id }}">

            <div class="absolute top-0 right-0 p-8 opacity-10 group-hover:opacity-20 transition-opacity">
                <svg class="w-24 h-24 text-primary-500 rotate-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z">
                    </path>
                </svg>
            </div>

            <div class="relative z-10 space-y-12">
                <div class="space-y-4">
                    <span
                        class="inline-flex items-center px-4 py-1.5 bg-primary-50 text-primary-700 text-[10px] font-black uppercase rounded-lg tracking-[0.2em]">Environment
                        Query</span>
                    <h3 class="text-3xl md:text-5xl font-black text-slate-900 leading-[1.1] tracking-tighter">
                        {{ $currentQuestion->question_text }}
                    </h3>

                    @if ($currentQuestion->media_type === 'image' && $currentQuestion->media_path)
                        <div class="mt-8 rounded-3xl overflow-hidden shadow-2xl border-4 border-white">
                            <img src="{{ Storage::url($currentQuestion->media_path) }}" alt="Question Media"
                                class="w-full h-auto object-cover max-h-[400px]">
                        </div>
                    @elseif ($currentQuestion->media_type === 'video' && $currentQuestion->media_url)
                        <div class="mt-8 aspect-video rounded-3xl overflow-hidden shadow-2xl border-4 border-white">
                            <iframe src="{{ $currentQuestion->embed_url }}" class="w-full h-full" allowfullscreen></iframe>
                        </div>
                    @elseif ($currentQuestion->media_type === 'audio' && $currentQuestion->media_url)
                        <div
                            class="mt-8 p-6 bg-slate-50 rounded-3xl border-2 border-slate-100 flex items-center justify-center">
                            <audio controls src="{{ $currentQuestion->media_url }}" class="w-full"></audio>
                        </div>
                    @endif
                </div>

                <div class="grid grid-cols-1 gap-5">
                    {{-- MCQ SINGLE / BOOLEAN --}}
                    @if(in_array($currentQuestion->question_type, ['mcq_single', 'boolean']))
                        @foreach($currentQuestion->options as $option)
                            <label class="cursor-pointer group/option">
                                <input type="radio" x-model="answer" value="{{ $option->id }}" class="hidden">
                                <div class="flex items-center gap-6 p-4 rounded-[1.5rem] border-2 transition-all duration-300 transform group-hover/option:scale-[1.01]"
                                    :class="answer == {{ $option->id }} ? 'border-primary-600 bg-primary-50/50 shadow-lg shadow-primary-100' : 'border-slate-100 bg-white/50 hover:border-primary-200'">

                                    <div class="flex-shrink-0 w-8 h-8 rounded-xl border-2 flex items-center justify-center transition-all duration-300"
                                        :class="answer == {{ $option->id }} ? 'border-primary-600 bg-primary-600 rotate-12' : 'border-slate-200 bg-slate-50'">
                                        <div class="w-2 h-2 rounded-sm bg-white transition-opacity"
                                            :class="answer == {{ $option->id }} ? 'opacity-100' : 'opacity-0'"></div>
                                    </div>

                                    @if($option->image_path)
                                        <div
                                            class="flex-shrink-0 w-16 h-16 rounded-xl overflow-hidden border-2 border-slate-100 shadow-sm">
                                            <img src="{{ Storage::url($option->image_path) }}" class="w-full h-full object-cover">
                                        </div>
                                    @endif

                                    <span class="text-lg font-bold tracking-tight transition-colors"
                                        :class="answer == {{ $option->id }} ? 'text-primary-900' : 'text-slate-600'">
                                        {{ $option->option_text }}
                                    </span>
                                </div>
                            </label>
                        @endforeach

                        {{-- MCQ MULTIPLE --}}
                    @elseif($currentQuestion->question_type === 'mcq_multiple')
                        @foreach($currentQuestion->options as $option)
                            <label class="cursor-pointer group/option">
                                <input type="checkbox" wire:model.live="answers.{{ $currentQuestion->id }}"
                                    value="{{ $option->id }}" class="hidden">
                                <div class="flex items-center gap-6 p-4 rounded-[1.5rem] border-2 transition-all duration-300 transform group-hover/option:scale-[1.01]"
                                    :class="(typeof answer === 'object' && answer !== null && (Object.values(answer).includes('{{ $option->id }}') || Object.values(answer).includes({{ $option->id }}))) || (Array.isArray(answer) && (answer.includes('{{ $option->id }}') || answer.includes({{ $option->id }}))) ? 'border-primary-600 bg-primary-50/50 shadow-lg shadow-primary-100' : 'border-slate-100 bg-white/50 hover:border-primary-200'">

                                    <div class="flex-shrink-0 w-8 h-8 rounded-xl border-2 flex items-center justify-center transition-all duration-300"
                                        :class="(typeof answer === 'object' && answer !== null && (Object.values(answer).includes('{{ $option->id }}') || Object.values(answer).includes({{ $option->id }}))) || (Array.isArray(answer) && (answer.includes('{{ $option->id }}') || answer.includes({{ $option->id }}))) ? 'border-primary-600 bg-primary-600' : 'border-slate-200 bg-slate-50'">
                                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="4"
                                                d="M5 13l4 4L19 7"></path>
                                        </svg>
                                    </div>

                                    @if($option->image_path)
                                        <div
                                            class="flex-shrink-0 w-16 h-16 rounded-xl overflow-hidden border-2 border-slate-100 shadow-sm">
                                            <img src="{{ Storage::url($option->image_path) }}" class="w-full h-full object-cover">
                                        </div>
                                    @endif

                                    <span class="text-lg font-bold tracking-tight transition-colors"
                                        :class="(typeof answer === 'object' && answer !== null && (Object.values(answer).includes('{{ $option->id }}') || Object.values(answer).includes({{ $option->id }}))) || (Array.isArray(answer) && (answer.includes('{{ $option->id }}') || answer.includes({{ $option->id }}))) ? 'text-primary-900' : 'text-slate-600'">
                                        {{ $option->option_text }}
                                    </span>
                                </div>
                            </label>
                        @endforeach

                        {{-- NUMBER RANGE --}}
                    @elseif($currentQuestion->question_type === 'number_range')
                        <div class="p-4 bg-slate-50 rounded-[2rem] border border-slate-100">
                            <label for="number_answer_{{ $currentQuestion->id }}" class="sr-only">Enter numeric answer</label>
                            <input id="number_answer_{{ $currentQuestion->id }}" type="number" step="any" x-model="answer"
                                class="w-full px-8 py-4 rounded-2xl bg-white border-2 border-slate-100 focus:border-primary-500 focus:ring-4 focus:ring-primary-100 outline-none transition-all text-xl font-black tracking-tight"
                                placeholder="Value Entry Mode..." aria-label="Enter numeric answer">
                        </div>

                        {{-- TEXT KEYWORDS --}}
                    @elseif($currentQuestion->question_type === 'text_keywords')
                        <div class="p-4 bg-slate-50 rounded-[2rem] border border-slate-100">
                            <label for="text_keywords_{{ $currentQuestion->id }}" class="sr-only">Enter keywords separated by
                                delimiters</label>
                            <textarea id="text_keywords_{{ $currentQuestion->id }}" x-model="answer" rows="3"
                                class="w-full px-8 py-4 rounded-2xl bg-white border-2 border-slate-100 focus:border-primary-500 focus:ring-4 focus:ring-primary-100 outline-none transition-all text-xl font-bold tracking-tight leading-relaxed"
                                placeholder="Linguistic Input Required..."
                                aria-label="Enter keywords separated by commas or spaces"></textarea>
                            <div class="mt-4 flex items-center gap-2 px-2">
                                <span class="w-2 h-2 rounded-full bg-primary-600"></span>
                                <span class="text-[10px] font-black uppercase tracking-widest text-slate-400">Separate key
                                    points with delimiter characters</span>
                            </div>
                        </div>

                        {{-- MEDIA CHOICE (Image Response) --}}
                    @elseif($currentQuestion->question_type === 'image_answer')
                        <div class="grid grid-cols-2 md:grid-cols-3 gap-6">
                            @foreach($currentQuestion->options as $option)
                                <label class="cursor-pointer group/option">
                                    <input type="radio" x-model="answer" value="{{ $option->id }}" class="hidden">
                                    <div class="flex flex-col h-full rounded-[2rem] border-2 transition-all duration-300 transform group-hover/option:scale-[1.02] overflow-hidden bg-white shadow-sm"
                                        :class="answer == {{ $option->id }} ? 'border-primary-600 ring-4 ring-primary-100' : 'border-slate-100 hover:border-primary-200'">

                                        @if($option->image_path)
                                            <div class="aspect-square relative overflow-hidden bg-slate-50">
                                                <img src="{{ Storage::url($option->image_path) }}"
                                                    class="w-full h-full object-cover transition-transform duration-500 group-hover/option:scale-110"
                                                    alt="{{ $option->option_text }}">
                                                <div class="absolute inset-0 bg-gradient-to-t from-black/40 to-transparent opacity-0 transition-opacity"
                                                    :class="answer == {{ $option->id }} ? 'opacity-100' : ''"></div>
                                            </div>
                                        @else
                                            <div class="aspect-square flex items-center justify-center bg-slate-50">
                                                <svg class="w-12 h-12 text-slate-200" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z">
                                                    </path>
                                                </svg>
                                            </div>
                                        @endif

                                        <div class="p-4 flex items-center justify-between gap-3">
                                            @if($option->option_text)
                                                <span class="text-sm font-bold tracking-tight text-slate-700 clamp-1">
                                                    {{ $option->option_text }}
                                                </span>
                                            @endif

                                            <div class="w-6 h-6 rounded-full border-2 flex items-center justify-center flex-shrink-0 transition-all"
                                                :class="answer == {{ $option->id }} ? 'border-primary-600 bg-primary-600' : 'border-slate-200'">
                                                <svg x-show="answer == {{ $option->id }}" class="w-3 h-3 text-white" fill="none"
                                                    stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                                                        d="M5 13l4 4L19 7"></path>
                                                </svg>
                                            </div>
                                        </div>
                                    </div>
                                </label>
                            @endforeach
                        </div>
                    @endif
                </div>

                <div class="flex flex-col sm:flex-row justify-between items-center gap-6 pt-4">
                    @if($currentIndex > 0)
                        <button wire:click="previousQuestion"
                            class="w-full sm:w-auto px-10 py-4 rounded-2xl font-black text-slate-400 hover:text-slate-900 hover:bg-slate-50 transition-all uppercase tracking-widest text-xs">
                            Fallback Prev
                        </button>
                    @else
                        <div></div>
                    @endif

                    <button @click="
                                                let isValid = true;
                                                if ({{ $currentQuestion->is_required ? 'true' : 'false' }}) {
                                                    if (Array.isArray(answer)) {
                                                        isValid = answer.length > 0;
                                                    } else if (typeof answer === 'object' && answer !== null) {
                                                        isValid = Object.keys(answer).length > 0;
                                                    } else {
                                                        isValid = (answer !== null && answer !== undefined && answer !== '');
                                                    }
                                                }

                                                if (isValid) {
                                                    let submitAnswer = answer;
                                                    if (typeof submitAnswer === 'object' && submitAnswer !== null && !Array.isArray(submitAnswer)) {
                                                        submitAnswer = Object.values(submitAnswer);
                                                    }
                                                    $wire.nextQuestion(submitAnswer);
                                                } else {
                                                    window.dispatchEvent(new CustomEvent('toast', { 
                                                        detail: { text: 'Question choice is required. Please select an option.', type: 'error' } 
                                                    }));
                                                }
                                            "
                        class="w-full sm:w-auto btn-premium text-white font-black px-16 py-4 rounded-[2rem] uppercase tracking-widest text-sm flex items-center justify-center gap-3">
                        {{ $currentIndex === ($totalCount - 1) ? 'Finalize Result' : 'Push Next' }}
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                                d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    @else
        <!-- Premium Deployment Result Interface -->
        <div class="glass-card rounded-[3.5rem] p-10 relative overflow-hidden group shadow-2xl space-y-10 animate-zoom-in">
            <div class="absolute top-0 left-0 w-full h-3 bg-gradient-to-r from-primary-600 via-indigo-600 to-violet-600">
            </div>

            <div class="text-center space-y-4">
                <div
                    class="w-20 h-20 bg-green-500 text-white rounded-3xl mx-auto flex items-center justify-center shadow-2xl shadow-green-200 mb-8 rotate-3">
                    <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="4" d="M5 13l4 4L19 7"></path>
                    </svg>
                </div>
                <h2 class="text-5xl font-black text-slate-900 tracking-tighter uppercase leading-none">Diagnostic Success
                </h2>
                @if($timeExpired)
                    <div
                        class="inline-flex items-center px-4 py-1.5 bg-red-50 text-red-700 text-[10px] font-black uppercase rounded-lg tracking-widest animate-pulse mt-2">
                        System Shutdown: Time Expired
                    </div>
                @endif
                <p class="text-slate-400 font-bold tracking-widest uppercase text-xs">Environment: {{ $quiz->title }}</p>
            </div>

            <!-- Score Module -->
            <div class="flex flex-col md:flex-row items-center justify-center gap-12">
                <div class="relative w-64 h-64" x-data="{ count: 0 }" x-init="setTimeout(() => { 
                                            let target = {{ $totalScore }};
                                            let interval = setInterval(() => { if (count >= target) clearInterval(interval); else count++; }, 30);
                                        }, 500)">
                    <svg class="w-full h-full transform -rotate-90 drop-shadow-2xl">
                        <circle cx="128" cy="128" r="110" stroke="#f1f5f9" stroke-width="16" fill="transparent" />
                        <circle cx="128" cy="128" r="110" stroke="url(#gradientScore)" stroke-width="16" fill="transparent"
                            stroke-dasharray="691.1"
                            stroke-dashoffset="{{ 691.1 - (691.1 * ($totalScore / max(1, $maxScore))) }}"
                            class="transition-all duration-1000 ease-out" />
                        <defs>
                            <linearGradient id="gradientScore" x1="0%" y1="0%" x2="100%" y2="100%">
                                <stop offset="0%" stop-color="#4f46e5" />
                                <stop offset="100%" stop-color="#9333ea" />
                            </linearGradient>
                        </defs>
                    </svg>
                    <div class="absolute inset-0 flex flex-col items-center justify-center">
                        <span class="text-7xl font-black text-slate-900 tracking-tighter tabular-nums"
                            x-text="count"></span>
                        <span
                            class="text-[10px] font-black text-slate-400 uppercase tracking-widest border-t-2 border-slate-100 pt-2 mt-1">Global
                            Points</span>
                    </div>
                </div>

                <div class="flex-grow max-w-sm space-y-6">
                    <div
                        class="p-8 bg-slate-900 rounded-[2.5rem] text-white shadow-2xl relative overflow-hidden group/rank">
                        <div class="absolute top-0 right-0 p-4 opacity-5">
                            <svg class="w-16 h-16 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path
                                    d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z">
                                </path>
                            </svg>
                        </div>
                        @php $percentage = round($maxScore > 0 ? ($totalScore / $maxScore) * 100 : 0); @endphp
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

            <!-- Deployment Share Module -->
            <div class="p-4 bg-slate-50 rounded-[3rem] border border-slate-100 text-center space-y-8">
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

            <div class="flex flex-col sm:flex-row gap-6">
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
    @endif
</div>