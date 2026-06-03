// ── SESIONES and POR_PAGINA injected by historial.php ──
const POR_PAGINA = 10;

const { createApp, ref, computed, watch, nextTick } = Vue;

createApp({
    setup() {
        // ── Estado filtros ──
        const filtroRutina = ref('');
        const filtroMes    = ref('');
        const paginaActual = ref(1);

        // ── Estado acordeones ──
        const abiertos    = ref([]);   // ids de sesiones abiertas
        const alturas     = ref({});   // altura en px de cada detalle
        const detalleRefs = ref({});   // refs a los elementos DOM

        // ── Filtrado ──
        const sesionesFiltradas = computed(() => {
            return SESIONES.filter(s => {
                const okRutina = !filtroRutina.value || String(s.rutina_id) === String(filtroRutina.value);
                const okMes    = !filtroMes.value    || s.mes_key === filtroMes.value;
                return okRutina && okMes;
            });
        });

        // ── Resetear página y acordeones al cambiar cualquier filtro ──
        watch([filtroRutina, filtroMes], () => {
            paginaActual.value = 1;
            abiertos.value     = [];
        });

        // ── Paginación ──
        const totalPaginas = computed(() =>
            Math.max(1, Math.ceil(sesionesFiltradas.value.length / POR_PAGINA))
        );

        const sesionesPagina = computed(() => {
            const inicio = (paginaActual.value - 1) * POR_PAGINA;
            return sesionesFiltradas.value.slice(inicio, inicio + POR_PAGINA);
        });

        const paginasVisibles = computed(() => {
            const total  = totalPaginas.value;
            const actual = paginaActual.value;
            const paginas = [];
            for (let i = 1; i <= total; i++) {
                if (i === 1 || i === total || Math.abs(i - actual) <= 1) {
                    paginas.push(i);
                } else if (Math.abs(i - actual) === 2) {
                    paginas.push('...');
                }
            }
            // Eliminar duplicados de '...'
            return paginas.filter((p, idx) => p !== '...' || paginas[idx - 1] !== '...');
        });

        function limpiarFiltros() {
            filtroRutina.value = '';
            filtroMes.value    = '';
            // watch handles paginaActual and abiertos reset
        }

        function irPagina(p) {
            if (p < 1 || p > totalPaginas.value) return;
            paginaActual.value = p;
            abiertos.value     = [];
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }

        // ── Acordeón ──
        // scrollHeight must be read from the inner div (.sesion-detalle-inner),
        // because the outer wrapper starts at max-height:0 and has overflow:hidden,
        // which makes its own scrollHeight unreliable before the transition runs.
        async function toggleSesion(id) {
            const idx = abiertos.value.indexOf(id);
            if (idx === -1) {
                abiertos.value.push(id);
                await nextTick();
                const outer = detalleRefs.value[id];
                if (outer) {
                    const inner = outer.querySelector('.sesion-detalle-inner');
                    alturas.value[id] = inner ? inner.scrollHeight : outer.scrollHeight;
                }
            } else {
                abiertos.value.splice(idx, 1);
            }
        }

        // ── Utilidad formato número ──
        function formatNum(n) {
            return Math.round(n).toLocaleString('es-ES');
        }

        return {
            filtroRutina, filtroMes, paginaActual,
            abiertos, alturas, detalleRefs,
            sesionesFiltradas, sesionesPagina, totalPaginas, paginasVisibles,
            limpiarFiltros, irPagina, toggleSesion, formatNum,
        };
    }
}).mount('#app');