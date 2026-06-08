<x-filament-panels::page>
    <style>
        .lesson-rich-content,
        .lesson-rich-content * {
            color: rgb(51 65 85) !important;
        }

        .dark .lesson-rich-content,
        .dark .lesson-rich-content * {
            color: rgb(226 232 240) !important;
        }

        .lesson-rich-content a {
            color: rgb(3 105 161) !important;
        }

        .dark .lesson-rich-content a {
            color: rgb(103 232 249) !important;
        }

        .lesson-rich-content strong {
            color: rgb(15 23 42) !important;
        }

        .dark .lesson-rich-content strong {
            color: rgb(248 250 252) !important;
        }
    </style>

    @php
        $lesson = $this->getLesson();
        $objectives = $this->getObjectives();
        $contentBlocks = $this->getContentBlocks();
        $conclusions = $this->getConclusions();
        $references = $this->getReferences();
        $illustrations = $this->getIllustrationUrls();
        $sections = $this->getSectionNavigation();
        $iframe = $this->getVideoIframe();
        $embeddedVideoUrl = $this->getEmbeddedVideoUrl();
        $directVideoUrl = $this->getDirectVideoUrl();
        $externalVideoUrl = $this->getExternalVideoUrl();
        $heroImageUrl = $this->getHeroImageUrl();
    @endphp

    <div class="mx-auto flex w-full max-w-7xl flex-col gap-6">
        <section class="overflow-hidden rounded-[2rem] bg-gradient-to-br from-slate-950 via-sky-950 to-cyan-900 text-white shadow-2xl ring-1 ring-sky-200/20">
            <div class="grid gap-0 xl:grid-cols-[minmax(0,1.45fr)_minmax(320px,0.95fr)]">
                <div class="p-4 sm:p-6 lg:p-7">
                    <div class="aspect-video overflow-hidden rounded-[1.75rem] bg-slate-950/70 shadow-inner ring-1 ring-white/10">
                        @if ($iframe)
                            <div class="h-full w-full [&_iframe]:h-full [&_iframe]:w-full [&_iframe]:border-0">
                                {!! $iframe !!}
                            </div>
                        @elseif ($embeddedVideoUrl)
                            <iframe
                                src="{{ $embeddedVideoUrl }}"
                                title="Video de {{ $lesson->title }}"
                                class="h-full w-full border-0"
                                loading="lazy"
                                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                                allowfullscreen
                            ></iframe>
                        @elseif ($directVideoUrl)
                            <video
                                class="h-full w-full bg-slate-950 object-cover"
                                controls
                                preload="metadata"
                            >
                                <source src="{{ $directVideoUrl }}">
                                Tu navegador no soporta la reproduccion del video.
                            </video>
                        @elseif ($heroImageUrl)
                            <img
                                src="{{ $heroImageUrl }}"
                                alt="Portada de la leccion {{ $lesson->title }}"
                                class="h-full w-full object-cover"
                            >
                        @else
                            <div class="flex h-full w-full items-center justify-center bg-[radial-gradient(circle_at_top,_rgba(56,189,248,0.25),_transparent_45%),linear-gradient(135deg,_rgba(8,47,73,0.95),_rgba(15,23,42,0.98))] p-10 text-center">
                                <div class="max-w-md space-y-4">
                                    <span class="inline-flex rounded-full bg-white/10 px-4 py-1 text-xs font-semibold uppercase tracking-[0.3em] text-cyan-100">
                                        Leccion
                                    </span>
                                    <h2 class="text-2xl font-semibold text-white sm:text-3xl">
                                        {{ $lesson->title }}
                                    </h2>
                                    <p class="text-sm leading-7 text-slate-200">
                                        Esta leccion aun no tiene un recurso multimedia principal, pero su contenido esta listo para consulta estructurada.
                                    </p>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="flex flex-col justify-between gap-6 p-6 sm:p-8">
                    <div class="space-y-5">
                        <div class="flex flex-wrap gap-2">
                            <span class="inline-flex items-center rounded-full bg-white/10 px-3 py-1 text-xs font-medium text-cyan-50 ring-1 ring-white/10">
                                Leccion {{ $lesson->order }}
                            </span>
                            <span class="inline-flex items-center rounded-full bg-emerald-400/15 px-3 py-1 text-xs font-medium text-emerald-100 ring-1 ring-emerald-300/20">
                                {{ $this->getCompletionModeLabel() }}
                            </span>
                            <span class="inline-flex items-center rounded-full bg-white/10 px-3 py-1 text-xs font-medium text-slate-100 ring-1 ring-white/10">
                                {{ $lesson->active ? 'Activa' : 'Inactiva' }}
                            </span>
                        </div>

                        <div class="space-y-3">
                            <h1 class="text-3xl font-semibold leading-tight text-white sm:text-4xl">
                                {{ $lesson->title }}
                            </h1>

                            @if (filled($lesson->description))
                                <p class="max-w-2xl text-sm leading-7 text-slate-200 sm:text-base">
                                    {{ $lesson->description }}
                                </p>
                            @endif
                        </div>

                        <dl class="grid gap-3 sm:grid-cols-2">
                            <div class="rounded-2xl bg-white/8 p-4 ring-1 ring-white/10">
                                <dt class="text-xs uppercase tracking-[0.22em] text-slate-300">Curso</dt>
                                <dd class="mt-2 text-sm font-medium text-white">
                                    {{ $lesson->module?->course?->title ?? 'Sin curso asociado' }}
                                </dd>
                            </div>
                            <div class="rounded-2xl bg-white/8 p-4 ring-1 ring-white/10">
                                <dt class="text-xs uppercase tracking-[0.22em] text-slate-300">Modulo</dt>
                                <dd class="mt-2 text-sm font-medium text-white">
                                    {{ $lesson->module?->title ?? 'Sin modulo asociado' }}
                                </dd>
                            </div>
                            <div class="rounded-2xl bg-white/8 p-4 ring-1 ring-white/10">
                                <dt class="text-xs uppercase tracking-[0.22em] text-slate-300">Duracion</dt>
                                <dd class="mt-2 text-sm font-medium text-white">
                                    {{ $lesson->duration ? $lesson->duration . ' minutos' : 'No definida' }}
                                </dd>
                            </div>
                            <div class="rounded-2xl bg-white/8 p-4 ring-1 ring-white/10">
                                <dt class="text-xs uppercase tracking-[0.22em] text-slate-300">Ruta didactica</dt>
                                <dd class="mt-2 text-sm font-medium text-white">
                                    {{ count($sections) }} secciones listas para consulta
                                </dd>
                            </div>
                        </dl>
                    </div>

                    @if ($externalVideoUrl)
                        <div class="rounded-[1.5rem] bg-white/10 p-4 ring-1 ring-white/10">
                            <p class="text-sm leading-6 text-slate-100">
                                El video principal no es incrustable automaticamente desde esta URL, pero puedes abrirlo en una pestaña nueva.
                            </p>
                            <a
                                href="{{ $externalVideoUrl }}"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="mt-4 inline-flex items-center rounded-full bg-cyan-400 px-4 py-2 text-sm font-semibold text-slate-950 transition hover:bg-cyan-300"
                            >
                                Abrir video externo
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </section>

        <div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_320px]">
            <div class="space-y-6">
                @if (filled($this->getIntroduction()))
                    <section id="introduccion" class="overflow-hidden rounded-[2rem] bg-white shadow-sm ring-1 ring-slate-200 dark:bg-gray-900 dark:ring-white/10">
                        <div class="border-b border-slate-200 bg-slate-50 px-6 py-5 dark:border-white/10 dark:bg-white/5">
                            <p class="text-xs font-semibold uppercase tracking-[0.25em] text-sky-700 dark:text-cyan-300">Introduccion</p>
                            <h2 class="mt-2 text-2xl font-semibold text-slate-900 dark:text-white">Contexto inicial de la leccion</h2>
                        </div>
                        <div class="lesson-rich-content px-6 py-6 text-sm leading-7 text-slate-700 dark:text-slate-200 [&_a]:text-sky-700 [&_a]:underline [&_li]:mb-2 [&_ol]:ml-5 [&_ol]:list-decimal [&_p]:mb-4 [&_strong]:text-slate-900 [&_strong]:dark:text-white [&_ul]:ml-5 [&_ul]:list-disc">
                            {!! $this->getIntroduction() !!}
                        </div>
                    </section>
                @endif

                @if ($objectives !== [])
                    <section id="objetivos" class="rounded-[2rem] bg-white p-6 shadow-sm ring-1 ring-slate-200 dark:bg-gray-900 dark:ring-white/10">
                        <p class="text-xs font-semibold uppercase tracking-[0.25em] text-emerald-700 dark:text-emerald-300">Objetivos</p>
                        <h2 class="mt-2 text-2xl font-semibold text-slate-900 dark:text-white">Lo que esta leccion busca desarrollar</h2>
                        <div class="mt-6 grid gap-4 sm:grid-cols-2">
                            @foreach ($objectives as $objective)
                                <article class="rounded-[1.5rem] border border-emerald-100 bg-emerald-50/70 p-5 text-sm leading-7 text-emerald-950 shadow-sm dark:border-emerald-400/20 dark:bg-emerald-500/10 dark:text-emerald-50">
                                    <div class="mb-3 inline-flex h-10 w-10 items-center justify-center rounded-2xl bg-emerald-600/10 text-base font-semibold text-emerald-700 dark:bg-emerald-400/15 dark:text-emerald-200">
                                        {{ $loop->iteration }}
                                    </div>
                                    <p>{{ $objective }}</p>
                                </article>
                            @endforeach
                        </div>
                    </section>
                @endif

                @if ($contentBlocks !== [])
                    <section id="contenido" class="rounded-[2rem] bg-white p-6 shadow-sm ring-1 ring-slate-200 dark:bg-gray-900 dark:ring-white/10">
                        <p class="text-xs font-semibold uppercase tracking-[0.25em] text-indigo-700 dark:text-indigo-300">Desarrollo</p>
                        <h2 class="mt-2 text-2xl font-semibold text-slate-900 dark:text-white">Contenido estructurado para la ensenanza</h2>
                        <div class="mt-6 space-y-4">
                            @foreach ($contentBlocks as $block)
                                <article class="rounded-[1.5rem] border border-slate-200 bg-slate-50/80 p-5 shadow-sm dark:border-white/10 dark:bg-white/5">
                                    <div class="mb-4 flex items-center gap-3">
                                        <span class="inline-flex h-9 w-9 items-center justify-center rounded-full bg-slate-900 text-sm font-semibold text-white dark:bg-cyan-400 dark:text-slate-950">
                                            {{ $loop->iteration }}
                                        </span>
                                        <h3 class="text-base font-semibold text-slate-900 dark:text-white">
                                            Bloque {{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}
                                        </h3>
                                    </div>
                                    <div class="lesson-rich-content text-sm leading-7 text-slate-700 dark:text-slate-200 [&_a]:text-sky-700 [&_a]:underline [&_li]:mb-2 [&_ol]:ml-5 [&_ol]:list-decimal [&_p]:mb-4 [&_strong]:text-slate-900 [&_strong]:dark:text-white [&_ul]:ml-5 [&_ul]:list-disc">
                                        {!! $block !!}
                                    </div>
                                </article>
                            @endforeach
                        </div>
                    </section>
                @endif

                @if ($illustrations !== [])
                    <section id="recursos-visuales" class="rounded-[2rem] bg-white p-6 shadow-sm ring-1 ring-slate-200 dark:bg-gray-900 dark:ring-white/10">
                        <p class="text-xs font-semibold uppercase tracking-[0.25em] text-amber-700 dark:text-amber-300">Recursos visuales</p>
                        <h2 class="mt-2 text-2xl font-semibold text-slate-900 dark:text-white">Apoyos graficos de la leccion</h2>
                        <div class="mt-6 grid gap-4 md:grid-cols-2">
                            @foreach ($illustrations as $illustration)
                                <figure class="overflow-hidden rounded-[1.5rem] border border-slate-200 bg-slate-50 shadow-sm dark:border-white/10 dark:bg-white/5">
                                    <img
                                        src="{{ $illustration }}"
                                        alt="Ilustracion {{ $loop->iteration }} de la leccion {{ $lesson->title }}"
                                        class="aspect-[4/3] w-full object-cover"
                                    >
                                    <figcaption class="px-4 py-3 text-sm text-slate-600 dark:text-slate-300">
                                        Recurso visual {{ $loop->iteration }}
                                    </figcaption>
                                </figure>
                            @endforeach
                        </div>
                    </section>
                @endif

                @if ($conclusions !== [])
                    <section id="conclusiones" class="rounded-[2rem] bg-white p-6 shadow-sm ring-1 ring-slate-200 dark:bg-gray-900 dark:ring-white/10">
                        <p class="text-xs font-semibold uppercase tracking-[0.25em] text-rose-700 dark:text-rose-300">Conclusiones</p>
                        <h2 class="mt-2 text-2xl font-semibold text-slate-900 dark:text-white">Ideas clave para cerrar la leccion</h2>
                        <div class="mt-6 space-y-3">
                            @foreach ($conclusions as $conclusion)
                                <div class="flex items-start gap-4 rounded-[1.5rem] border border-rose-100 bg-rose-50/70 p-5 text-sm leading-7 text-rose-950 shadow-sm dark:border-rose-400/20 dark:bg-rose-500/10 dark:text-rose-50">
                                    <span class="mt-1 inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-rose-600/10 text-sm font-semibold text-rose-700 dark:bg-rose-400/15 dark:text-rose-200">
                                        {{ $loop->iteration }}
                                    </span>
                                    <p>{{ $conclusion }}</p>
                                </div>
                            @endforeach
                        </div>
                    </section>
                @endif

                @if ($references !== [])
                    <section id="referencias" class="rounded-[2rem] bg-white p-6 shadow-sm ring-1 ring-slate-200 dark:bg-gray-900 dark:ring-white/10">
                        <p class="text-xs font-semibold uppercase tracking-[0.25em] text-slate-700 dark:text-slate-300">Referencias</p>
                        <h2 class="mt-2 text-2xl font-semibold text-slate-900 dark:text-white">Material complementario y fuentes</h2>
                        <div class="mt-6 space-y-3">
                            @foreach ($references as $reference)
                                <article class="rounded-[1.5rem] border border-slate-200 bg-slate-50/80 p-5 text-sm leading-7 text-slate-700 shadow-sm dark:border-white/10 dark:bg-white/5 dark:text-slate-200">
                                    <p class="font-medium text-slate-900 dark:text-white">{{ $reference['text'] }}</p>
                                    @if ($reference['url'])
                                        <a
                                            href="{{ $reference['url'] }}"
                                            target="_blank"
                                            rel="noopener noreferrer"
                                            class="mt-2 inline-flex text-sky-700 underline decoration-sky-300 underline-offset-4 dark:text-cyan-300"
                                        >
                                            {{ $reference['url'] }}
                                        </a>
                                    @endif
                                </article>
                            @endforeach
                        </div>
                    </section>
                @endif

                @unless ($this->hasRichContent())
                    <section class="rounded-[2rem] border border-dashed border-slate-300 bg-white p-10 text-center shadow-sm dark:border-white/10 dark:bg-gray-900">
                        <p class="text-sm font-semibold uppercase tracking-[0.25em] text-slate-500 dark:text-slate-400">Sin contenido ampliado</p>
                        <h2 class="mt-3 text-2xl font-semibold text-slate-900 dark:text-white">La leccion todavia no tiene desarrollo didactico cargado</h2>
                        <p class="mx-auto mt-4 max-w-2xl text-sm leading-7 text-slate-600 dark:text-slate-300">
                            Puedes usar el boton de editar para agregar introduccion, objetivos, bloques de contenido, conclusiones, imagenes o referencias y convertir esta vista en una experiencia de aprendizaje completa.
                        </p>
                    </section>
                @endunless
            </div>

            <aside class="space-y-6 xl:sticky xl:top-24">
                <section class="rounded-[2rem] bg-white p-6 shadow-sm ring-1 ring-slate-200 dark:bg-gray-900 dark:ring-white/10">
                    <p class="text-xs font-semibold uppercase tracking-[0.25em] text-sky-700 dark:text-cyan-300">Mapa de lectura</p>
                    <h2 class="mt-2 text-xl font-semibold text-slate-900 dark:text-white">Recorrido sugerido</h2>

                    @if ($sections !== [])
                        <nav class="mt-5 space-y-3">
                            @foreach ($sections as $section)
                                <a
                                    href="#{{ $section['id'] }}"
                                    class="flex items-center justify-between rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-medium text-slate-700 transition hover:border-sky-300 hover:bg-sky-50 hover:text-sky-800 dark:border-white/10 dark:bg-white/5 dark:text-slate-200 dark:hover:border-cyan-400/30 dark:hover:bg-cyan-400/10 dark:hover:text-cyan-100"
                                >
                                    <span>{{ $section['label'] }}</span>
                                    <span class="text-xs uppercase tracking-[0.2em] text-slate-400 dark:text-slate-500">
                                        {{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}
                                    </span>
                                </a>
                            @endforeach
                        </nav>
                    @else
                        <p class="mt-5 text-sm leading-7 text-slate-600 dark:text-slate-300">
                            Aun no hay secciones estructuradas. La vista principal muestra solamente la informacion base de la leccion.
                        </p>
                    @endif
                </section>

                <section class="rounded-[2rem] bg-slate-900 p-6 text-white shadow-sm ring-1 ring-slate-800 dark:bg-slate-950 dark:ring-white/10">
                    <p class="text-xs font-semibold uppercase tracking-[0.25em] text-cyan-300">Resumen docente</p>
                    <h2 class="mt-2 text-xl font-semibold">Puntos rapidos de consulta</h2>
                    <div class="mt-5 space-y-4 text-sm text-slate-200">
                        <div>
                            <p class="text-xs uppercase tracking-[0.2em] text-slate-400">Descripcion breve</p>
                            <p class="mt-2 leading-7 text-slate-100">
                                {{ filled($lesson->description) ? $lesson->description : 'No se ha definido una descripcion corta para esta leccion.' }}
                            </p>
                        </div>

                        <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-1">
                            <div class="rounded-2xl bg-white/5 p-4 ring-1 ring-white/10">
                                <p class="text-xs uppercase tracking-[0.2em] text-slate-400">Objetivos</p>
                                <p class="mt-2 text-lg font-semibold text-white">{{ count($objectives) }}</p>
                            </div>
                            <div class="rounded-2xl bg-white/5 p-4 ring-1 ring-white/10">
                                <p class="text-xs uppercase tracking-[0.2em] text-slate-400">Bloques</p>
                                <p class="mt-2 text-lg font-semibold text-white">{{ count($contentBlocks) }}</p>
                            </div>
                            <div class="rounded-2xl bg-white/5 p-4 ring-1 ring-white/10">
                                <p class="text-xs uppercase tracking-[0.2em] text-slate-400">Recursos visuales</p>
                                <p class="mt-2 text-lg font-semibold text-white">{{ count($illustrations) }}</p>
                            </div>
                            <div class="rounded-2xl bg-white/5 p-4 ring-1 ring-white/10">
                                <p class="text-xs uppercase tracking-[0.2em] text-slate-400">Referencias</p>
                                <p class="mt-2 text-lg font-semibold text-white">{{ count($references) }}</p>
                            </div>
                        </div>
                    </div>
                </section>
            </aside>
        </div>
    </div>
</x-filament-panels::page>
