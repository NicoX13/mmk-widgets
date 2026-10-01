// ═══════════════════════════════════════════════════════════════════════
// MMK TAB-SLIDER — Logik
// Schaltet Folien per Tab, Pfeil, Punkt, Tastatur (Pfeiltasten im
// Tablist, WAI-ARIA-Muster) oder automatisch. Autoplay pausiert bei
// Hover (abschaltbar) und Fokus (immer), respektiert reduzierte
// Bewegung und läuft nicht im Elementor-Editor. Der Folienwechsel
// selbst ist reines CSS (Klasse .ist-aktiv).
// ═══════════════════════════════════════════════════════════════════════

class MmkTabSlider {

	constructor(root) {
		this.root     = root;
		this.folien   = [...root.querySelectorAll('.mmk-tab-slider__folie')];
		this.tabs     = [...root.querySelectorAll('.mmk-tab-slider__tab')];
		this.punkte   = [...root.querySelectorAll('.mmk-tab-slider__punkt')];
		this.sekunden = parseFloat(root.dataset.autoplay) || 0;
		this.pauseBeiHover = root.dataset.pauseHover !== '0';
		this.aktiv    = 0;
		this.timer    = null;
		this.sanft    = !matchMedia('(prefers-reduced-motion: reduce)').matches;
		this.imEditor = document.body.classList.contains('elementor-editor-active');

		this.#navigation();
		this.#tastatur();
		this.#autoplay();
	}

	#zeige(index, fokus = false) {
		const i = (index + this.folien.length) % this.folien.length;
		this.aktiv = i;
		this.folien.forEach((folie, n) => folie.classList.toggle('ist-aktiv', n === i));
		this.tabs.forEach((tab, n) => {
			tab.setAttribute('aria-selected', n === i ? 'true' : 'false');
			tab.tabIndex = n === i ? 0 : -1;
		});
		this.punkte.forEach((punkt, n) => {
			if (n === i) punkt.setAttribute('aria-current', 'true');
			else punkt.removeAttribute('aria-current');
		});
		if (fokus) this.tabs[i]?.focus();
	}

	#navigation() {
		this.tabs.forEach((tab, i) => {
			tab.addEventListener('click', () => {this.#zeige(i); this.#neustart();});
		});
		this.punkte.forEach((punkt, i) => {
			punkt.addEventListener('click', () => {this.#zeige(i); this.#neustart();});
		});
		this.root.querySelector('.mmk-tab-slider__pfeil.zurueck')
			?.addEventListener('click', () => {this.#zeige(this.aktiv - 1); this.#neustart();});
		this.root.querySelector('.mmk-tab-slider__pfeil.weiter')
			?.addEventListener('click', () => {this.#zeige(this.aktiv + 1); this.#neustart();});
	}

	// Pfeiltasten/Pos1/Ende im Tablist (WAI-ARIA Tabs-Muster)
	#tastatur() {
		this.root.querySelector('.mmk-tab-slider__tabs')?.addEventListener('keydown', (e) => {
			const ziele = {
				ArrowRight: this.aktiv + 1,
				ArrowDown:  this.aktiv + 1,
				ArrowLeft:  this.aktiv - 1,
				ArrowUp:    this.aktiv - 1,
				Home:       0,
				End:        this.folien.length - 1,
			};
			if (!(e.key in ziele)) return;
			e.preventDefault();
			this.#zeige(ziele[e.key], true);
			this.#neustart();
		});
	}

	#autoplay() {
		if (!this.sekunden || !this.sanft || this.imEditor) return;
		this.#start();
		if (this.pauseBeiHover) {
			this.root.addEventListener('pointerenter', () => this.#stopp());
			this.root.addEventListener('pointerleave', () => this.#start());
		}
		// Fokus-Pause bleibt immer an (WCAG 2.2.2)
		this.root.addEventListener('focusin', () => this.#stopp());
		this.root.addEventListener('focusout', () => this.#start());
		document.addEventListener('visibilitychange', () => {
			document.hidden ? this.#stopp() : this.#start();
		});
	}

	#start() {
		if (this.timer || !this.sekunden || !this.sanft || this.imEditor) return;
		if (document.hidden) return;   // unsichtbarer Tab: erst bei Rückkehr
		this.timer = setInterval(() => this.#zeige(this.aktiv + 1), this.sekunden * 1000);
	}

	#stopp() {
		clearInterval(this.timer);
		this.timer = null;
	}

	#neustart() {
		if (!this.timer) return;
		this.#stopp();
		this.#start();
	}
}

function initAlleTabSlider(bereich = document) {
	bereich.querySelectorAll('.mmk-tab-slider').forEach((el) => {
		if (!el.mmkTabSlider) el.mmkTabSlider = new MmkTabSlider(el);
	});
}

window.addEventListener('elementor/frontend/init', () => {
	window.elementorFrontend.hooks.addAction('frontend/element_ready/mmk-tab-slider.default', (element) => {
		initAlleTabSlider(element[0] ?? element);
	});
});

if (document.readyState === 'loading') {
	document.addEventListener('DOMContentLoaded', () => initAlleTabSlider());
} else {
	initAlleTabSlider();
}
