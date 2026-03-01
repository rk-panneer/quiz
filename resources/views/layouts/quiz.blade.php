<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? config('app.name', 'Quiz System') }}</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Plus Jakarta Sans', 'sans-serif'],
                    },
                    colors: {
                        primary: {
                            50: '#f0f4ff',
                            100: '#e0e9fe',
                            200: '#c1d3fe',
                            300: '#91b1fd',
                            400: '#5a89fb',
                            500: '#335ef7',
                            600: '#2547eb',
                            700: '#1d35d8',
                            800: '#1e2dae',
                            900: '#1d2a8a',
                        },
                    }
                }
            }
        }
    </script>

    @livewireStyles
    <style>
        [x-cloak] { display: none !important; }
        
        body {
            background-color: #f8faff;
            background-image: 
                radial-gradient(at 0% 0%, hsla(225,100%,94%,1) 0, transparent 50%), 
                radial-gradient(at 50% 0%, hsla(260,100%,92%,1) 0, transparent 50%), 
                radial-gradient(at 100% 0%, hsla(190,100%,94%,1) 0, transparent 50%);
        }

        .glass-card {
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.4);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.05);
        }

        .premium-text-gradient {
            background: linear-gradient(135deg, #1e1b4b 0%, #312e81 30%, #4338ca 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .btn-premium {
            background: linear-gradient(135deg, #4f46e5 0%, #3730a3 100%);
            box-shadow: 0 10px 15px -3px rgba(79, 70, 229, 0.3);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .btn-premium:hover {
            transform: translateY(-2px);
            box-shadow: 0 20px 25px -5px rgba(79, 70, 229, 0.4);
        }

        /* Modern Scrollbar */
        ::-webkit-scrollbar { width: 8px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 10px; }
        ::-webkit-scrollbar-thumb:hover { background: #cbd5e1; }
    </style>
</head>

<body class="min-h-screen font-sans text-slate-900 selection:bg-primary-100 selection:text-primary-900">
    <div class="max-w-6xl mx-auto px-6 py-12">
        <header class="flex flex-col items-center mb-20">
            <a href="{{ route('home') }}" class="group mb-4">
                <div class="flex items-center gap-3">
                    <div
                        class="w-12 h-12 bg-primary-600 rounded-2xl rotate-3 flex items-center justify-center shadow-2xl group-hover:rotate-12 transition-transform duration-500">
                        <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0012 18.75c-1.03 0-1.9-.4-2.525-1.053l-.547-.547z">
                            </path>
                        </svg>
                    </div>
                    <span class="text-3xl font-black tracking-tight text-slate-900 uppercase">Quiz<span
                            class="text-primary-600 italic">Labs</span></span>
                </div>
            </a>
            <div class="h-1 w-24 bg-gradient-to-r from-primary-600 to-indigo-600 rounded-full"></div>
        </header>

        <main class="relative z-10">
            @yield('content')
            {{ $slot ?? '' }}
        </main>

        <footer class="mt-32 pt-12 border-t border-slate-200">
            <div
                class="flex flex-col md:flex-row justify-between items-center gap-8 opacity-60 grayscale hover:grayscale-0 transition-all">
                <p class="text-sm font-semibold tracking-tight uppercase">&copy; {{ date('Y') }} QuizLabs Professional.
                    All Rights Reserved.</p>
                <div class="flex gap-8">
                    <a href="javascript:void(0)" class="text-sm font-bold uppercase hover:text-primary-600">Privacy</a>
                    <a href="javascript:void(0)" class="text-sm font-bold uppercase hover:text-primary-600">Terms</a>
                    <a href="javascript:void(0)" class="text-sm font-bold uppercase hover:text-primary-600">Contact</a>
                </div>
            </div>
        </footer>
    </div>

    <!-- Global Toast System -->
    <div x-data="{ 
            messages: [],
            remove(id) {
                this.messages = this.messages.filter(m => m.id !== id)
            }
         }" @toast.window="
            const id = Date.now();
            messages.push({ id, text: $event.detail.text, type: $event.detail.type || 'info' });
            setTimeout(() => remove(id), 5000);
         " class="fixed top-10 right-10 z-[100] flex flex-col gap-3 pointer-events-none">

        <template x-for="message in messages" :key="message.id">
            <div x-show="true" x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-y-4 scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 scale-100"
                x-transition:leave-end="opacity-0 scale-95"
                class="pointer-events-auto px-6 py-4 rounded-2xl shadow-2xl glass-card flex items-center gap-4 min-w-[300px] border-l-4"
                :class="{
                    'border-primary-600': message.type === 'info',
                    'border-red-500': message.type === 'error',
                    'border-green-500': message.type === 'success'
                 }">
                <div class="flex-shrink-0">
                    <template x-if="message.type === 'error'">
                        <div class="w-8 h-8 rounded-lg bg-red-50 text-red-500 flex items-center justify-center">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                    d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z">
                                </path>
                            </svg>
                        </div>
                    </template>
                    <template x-if="message.type === 'info'">
                        <div class="w-8 h-8 rounded-lg bg-blue-50 text-blue-500 flex items-center justify-center">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                    d="M12 8v4m0 4h.01M21 12A9 9 0 1112 3a9 9 0 019 9z"></path>
                            </svg>
                        </div>
                    </template>
                    <template x-if="message.type === 'success'">
                        <div class="w-8 h-8 rounded-lg bg-green-50 text-green-500 flex items-center justify-center">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                    d="M5 13l4 4L19 7"></path>
                            </svg>
                        </div>
                    </template>
                </div>
                <p class="text-sm font-bold text-slate-900 tracking-tight" x-text="message.text"></p>
                <button @click="remove(message.id)" class="ml-auto p-1 text-slate-300 hover:text-slate-500">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                            d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
        </template>
    </div>

    @livewireScripts
    @stack('scripts')
</body>

</html>