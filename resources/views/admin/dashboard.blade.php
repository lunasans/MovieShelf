<x-admin-layout>
    @section('header_title', 'Dashboard')

    <div x-data="dashboardGrid()" class="space-y-8">

        {{-- Header --}}
        <div class="flex items-center justify-between gap-4 flex-wrap">
            <div>
                <h2 class="text-2xl font-black text-white tracking-tight">
                    Willkommen, <span class="text-rose-400">{{ Auth::user()->name }}</span>
                </h2>
                <p class="text-white/30 text-sm font-medium mt-1">{{ now()->isoFormat('dddd, D. MMMM YYYY') }}</p>
            </div>

            <div class="flex items-center gap-4">
                {{-- Anpassen-Modus --}}
                <template x-if="!editing">
                    <button type="button" @click="startEditing()"
                            class="flex items-center gap-2 text-xs font-black text-white/30 uppercase tracking-widest hover:text-white transition-colors">
                        <i class="bi bi-grid-1x2"></i> Anpassen
                    </button>
                </template>
                <template x-if="editing">
                    <div class="flex items-center gap-3">
                        <button type="button" @click="save()" :disabled="busy"
                                class="flex items-center gap-2 px-4 py-2 rounded-xl bg-rose-600 text-white text-xs font-black uppercase tracking-widest hover:bg-rose-500 disabled:opacity-50">
                            <i class="bi" :class="busy ? 'bi-hourglass-split' : 'bi-check-lg'"></i> Speichern
                        </button>
                        <button type="button" @click="cancel()"
                                class="text-xs font-black text-white/30 uppercase tracking-widest hover:text-white transition-colors">
                            Abbrechen
                        </button>
                        <button type="button" @click="resetLayout()" :disabled="busy"
                                class="text-xs font-black text-white/30 uppercase tracking-widest hover:text-rose-400 transition-colors disabled:opacity-50">
                            Zurücksetzen
                        </button>
                    </div>
                </template>

                <a href="{{ route('dashboard') }}" target="_blank"
                   class="flex items-center gap-2 text-xs font-black text-white/30 uppercase tracking-widest hover:text-white transition-colors">
                    <i class="bi bi-box-arrow-up-right"></i> Frontend
                </a>
            </div>
        </div>

        {{-- Hinweis + ausgeblendete Kacheln --}}
        <div x-show="editing" x-cloak class="glass rounded-2xl border border-rose-500/20 p-5 space-y-4">
            <p class="text-xs font-bold text-white/50">
                Kacheln lassen sich frei verschieben und an der rechten unteren Ecke in der Größe ändern.
                Mit dem Kreuz blendest du eine Kachel aus.
            </p>
            <div class="flex flex-wrap items-center gap-2">
                <span class="text-[10px] font-black uppercase tracking-widest text-white/30 me-2">Ausgeblendet:</span>
                <template x-for="widget in hidden" :key="widget.key">
                    <button type="button" @click="show(widget.key)"
                            class="flex items-center gap-2 px-3 py-1.5 rounded-xl bg-white/5 border border-white/10 text-[10px] font-black uppercase tracking-widest text-white/60 hover:text-white hover:border-rose-500/40">
                        <i class="bi" :class="widget.icon"></i>
                        <span x-text="widget.label"></span>
                        <i class="bi bi-plus-lg text-rose-400"></i>
                    </button>
                </template>
                <span x-show="hidden.length === 0" class="text-[10px] font-bold text-white/20 italic">keine</span>
            </div>
        </div>

        {{-- Raster --}}
        <div class="grid-stack">
            @foreach($widgets as $key => $widget)
                <div class="grid-stack-item"
                     gs-id="{{ $key }}"
                     gs-x="{{ $widget['x'] }}" gs-y="{{ $widget['y'] }}"
                     gs-w="{{ $widget['w'] }}" gs-h="{{ $widget['h'] }}"
                     data-label="{{ $widget['label'] }}"
                     data-icon="{{ $widget['icon'] }}"
                     @style(['display: none' => ! $widget['visible']])>
                    <div class="grid-stack-item-content">
                        <button type="button" @click="hide('{{ $key }}')" x-show="editing" x-cloak
                                class="absolute top-3 right-3 z-20 w-7 h-7 rounded-lg bg-black/60 border border-white/10 text-white/50 hover:text-rose-400 flex items-center justify-center"
                                title="Kachel ausblenden">
                            <i class="bi bi-x-lg text-xs"></i>
                        </button>
                        <div class="dashboard-widget @if(! $widget['bare']) glass p-8 rounded-[2.5rem] border-white/5 @endif">
                            @include('admin.dashboard.widgets.' . $key)
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    @push('scripts')
    <script>
        function dashboardGrid() {
            // Die GridStack-Instanz bewusst ausserhalb des Alpine-Zustands halten:
            // in Alpines reaktivem Proxy schlagen die internen Identitaetsvergleiche
            // von GridStack fehl (Knoten finden ihr Element nicht mehr wieder), das
            // Ablegen einer Kachel wird dann nie sauber abgeschlossen.
            let grid = null;
            let narrow = false;

            // Direkt aus Blade wie in den uebrigen Admin-Ansichten, statt aus dem
            // meta-Tag: unabhaengig davon, ob die Seite aus einem Cache kommt.
            const token = '{{ csrf_token() }}';

            return {
                editing: false,
                busy: false,
                hidden: [],

                init() {
                    grid = window.GridStack.init({
                        column: {{ \App\Support\DashboardWidgets::COLUMNS }},
                        cellHeight: 70,
                        margin: 12,
                        animate: false,
                        staticGrid: true,
                        handle: '.dashboard-widget',
                        // Griffe dauerhaft zeigen: der Autohide-Default blendet sie
                        // bis zum Hover aus, dann findet sie niemand.
                        alwaysShowResizeHandle: true,
                        resizable: { handles: 'se' },
                        // Auf schmalen Bildschirmen untereinander statt nebeneinander;
                        // die gespeicherte Anordnung bleibt davon unberuehrt.
                        columnOpts: {
                            breakpointForWindow: true,
                            breakpoints: [
                                { w: 768, c: 1 },
                                { w: 1280, c: 6 },
                            ],
                        },
                    });

                    this.refreshHidden();
                    this.syncSizeToContent();
                    window.addEventListener('resize', () => this.syncSizeToContent());
                },

                /**
                 * In den schmalen Layouts (weniger Spalten) reicht die gespeicherte
                 * Hoehe nicht mehr aus – dort waechst jede Kachel mit ihrem Inhalt,
                 * statt ihn abzuschneiden. Im vollen Raster gilt die eigene Hoehe.
                 */
                syncSizeToContent() {
                    const isNarrow = grid.getColumn() < {{ \App\Support\DashboardWidgets::COLUMNS }};
                    if (isNarrow === narrow) return;
                    narrow = isNarrow;

                    grid.batchUpdate();
                    grid.engine.nodes.slice().forEach(node => {
                        grid.update(node.el, { sizeToContent: narrow });
                    });
                    grid.batchUpdate(false);
                },

                /** Kacheln, die aktuell ausgeblendet sind – fuer die Chips im Anpassen-Modus. */
                refreshHidden() {
                    this.hidden = Array.from(document.querySelectorAll('.grid-stack-item'))
                        .filter(el => el.style.display === 'none')
                        .map(el => ({
                            key: el.getAttribute('gs-id'),
                            label: el.dataset.label,
                            icon: el.dataset.icon,
                        }));
                },

                startEditing() {
                    // Bei reduzierter Spaltenzahl (schmales Fenster) wuerde das Speichern
                    // die Anordnung auf die Ersatzspalten eindampfen – lieber blocken.
                    if (grid.getColumn() < {{ \App\Support\DashboardWidgets::COLUMNS }}) {
                        alert('Zum Anpassen bitte ein breiteres Fenster verwenden – im schmalen Layout stehen die Kacheln untereinander.');
                        return;
                    }

                    this.editing = true;
                    grid.setStatic(false);
                },

                cancel() {
                    window.location.reload();
                },

                hide(key) {
                    const el = this.itemEl(key);
                    if (!el) return;

                    // Position merken, damit die Kachel beim Einblenden zurueckkommt
                    const node = el.gridstackNode;
                    el.dataset.lastPos = JSON.stringify({ x: node.x, y: node.y, w: node.w, h: node.h });

                    grid.removeWidget(el, false);
                    el.style.display = 'none';
                    this.refreshHidden();
                },

                show(key) {
                    const el = this.itemEl(key);
                    if (!el) return;

                    el.style.display = '';
                    const pos = JSON.parse(el.dataset.lastPos || '{}');
                    grid.makeWidget(el);
                    if (pos.w) {
                        grid.update(el, pos);
                    }
                    this.refreshHidden();
                },

                itemEl(key) {
                    return document.querySelector(`.grid-stack-item[gs-id="${key}"]`);
                },

                /** Sichtbare Kacheln aus dem Raster, ausgeblendete mit ihrer letzten Position. */
                collect() {
                    const layout = {};

                    grid.save(false).forEach(node => {
                        layout[node.id] = { x: node.x, y: node.y, w: node.w, h: node.h, visible: true };
                    });

                    this.hidden.forEach(widget => {
                        const el = this.itemEl(widget.key);
                        const pos = JSON.parse(el?.dataset.lastPos || '{}');
                        layout[widget.key] = {
                            x: pos.x ?? 0, y: pos.y ?? 0, w: pos.w ?? 4, h: pos.h ?? 4, visible: false,
                        };
                    });

                    return layout;
                },

                async save() {
                    this.busy = true;
                    try {
                        // Relative URL: bleibt garantiert auf der Domain des Regals,
                        // damit das Sitzungs-Cookie mitgeht.
                        const response = await fetch('{{ route('admin.dashboard.layout', [], false) }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': token,
                            },
                            credentials: 'same-origin',
                            // Das Token zusaetzlich im Body: Laravel liest es von dort
                            // zuerst, und anders als der Header ueberlebt es jeden
                            // Proxy, der unbekannte X-Header entfernt.
                            body: JSON.stringify({ _token: token, layout: this.collect() }),
                        });

                        if (response.status === 419) {
                            if (confirm('Das Layout konnte nicht gespeichert werden, die Sitzung wurde nicht akzeptiert. Seite jetzt neu laden?')) {
                                window.location.reload();
                            }
                            return;
                        }

                        if (!response.ok) throw new Error('HTTP ' + response.status);

                        this.editing = false;
                        grid.setStatic(true);
                    } catch (error) {
                        alert('Das Layout konnte nicht gespeichert werden: ' + error.message);
                    } finally {
                        this.busy = false;
                    }
                },

                async resetLayout() {
                    if (!confirm('Standard-Anordnung wiederherstellen?')) return;

                    this.busy = true;
                    try {
                        const response = await fetch('{{ route('admin.dashboard.layout.reset', [], false) }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': token,
                            },
                            credentials: 'same-origin',
                            body: JSON.stringify({ _token: token }),
                        });

                        if (response.status === 419) {
                            alert('Die Sitzung wurde nicht akzeptiert. Die Seite wird neu geladen.');
                            window.location.reload();
                            return;
                        }

                        if (!response.ok) throw new Error('HTTP ' + response.status);

                        window.location.reload();
                    } catch (error) {
                        this.busy = false;
                        alert('Zuruecksetzen fehlgeschlagen: ' + error.message);
                    }
                },
            };
        }
    </script>
    @endpush
</x-admin-layout>
