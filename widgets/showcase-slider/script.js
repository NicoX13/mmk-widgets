// ═══════════════════════════════════════════════════════════════════════
// MMK SHOWCASE-SLIDER — Logik
// Entscheidet statisch vs. Slider (passen alle Karten rein?), hält die
// Punkte synchron zum Scroll (IntersectionObserver), bedient Pfeile und
// Autoplay. Autoplay pausiert bei Hover/Fokus, respektiert reduzierte
// Bewegung und läuft nicht im Elementor-Editor.
// ═══════════════════════════════════════════════════════════════════════

class MmkShowcaseSlider {

	constructor(root) {
		this.root     = root;
		this.track    = root.querySelector('.mmk-showcase-slider__track');
		this.karten   = [...this.track.children];
		this.punkte   = [...root.querySelectorAll('.mmk-showcase-slider__punkt')];
		this.sekunden = parseFloat(root.dataset.autoplay) || 0;
		this.aktiv    = 0;
		this.timer    = null;
		this.sichtbar = new Set();
		this.sanft    = !matchMedia('(prefers-reduced-motion: reduce)').matches;
		this.imEditor = document.body.classList.contains('elementor-editor-active');

		this.#pruefeModus();
		new ResizeObserver(() => this.#pruefeModus()).observe(this.track);
		this.#beobachteAktiv();
		this.#navigation();
		this.#autoplay();
	}

	// Statisch, wenn nicht mehr Karten da sind als Plätze
	#pruefeModus() {
		const plaetze  = parseFloat(getComputedStyle(this.root).getPropertyValue('--ss-sichtbar')) || 3;
		const statisch = this.karten.length <= plaetze;
		this.root.classList.toggle('ist-statisch', statisch);
		this.root.classList.toggle('ist-slider', !statisch);
	}

	#scrolleZu(index) {
		const ziel = this.karten[(index + this.karten.length) % this.karten.length];
		const links = ziel.getBoundingClientRect().left - this.track.getBoundingClientRect().left + this.track.scrollLeft;
		this.track.scrollTo({left: links, behavior: this.sanft ? 'smooth' : 'auto'});
	}

	// Aktiv = erste (fast) voll sichtbare Karte; Punkte folgen dem Scroll
	#beobachteAktiv() {
		const beobachter = new IntersectionObserver((eintraege) => {
			eintraege.forEach((eintrag) => {
				const index = this.karten.indexOf(eintrag.target);
				if (eintrag.intersectionRatio >= 0.6) this.sichtbar.add(index);
				else this.sichtbar.delete(index);
			});
			if (this.sichtbar.size) {
				this.aktiv = Math.min(...this.sichtbar);
				this.punkte.forEach((punkt, i) => {
					if (i === this.aktiv) punkt.setAttribute('aria-current', 'true');
					else punkt.removeAttribute('aria-current');
				});
			}
		}, {root: this.track, threshold: 0.6});
		this.karten.forEach((karte) => beobachter.observe(karte));
	}

	#navigation() {
		this.punkte.forEach((punkt, i) => {
			punkt.addEventListener('click', () => {this.#scrolleZu(i); this.#neustart();});
		});
		this.root.querySelector('.mmk-showcase-slider__slider-pfeil.zurueck')
			?.addEventListener('click', () => {this.#scrolleZu(this.aktiv - 1); this.#neustart();});
		this.root.querySelector('.mmk-showcase-slider__slider-pfeil.weiter')
			?.addEventListener('click', () => {this.#scrolleZu(this.aktiv + 1); this.#neustart();});
	}

	#autoplay() {
		if (!this.sekunden || !this.sanft || this.imEditor) return;
		this.#start();
		this.root.addEventListener('pointerenter', () => this.#stopp());
		this.root.addEventListener('pointerleave', () => this.#start());
		this.root.addEventListener('focusin', () => this.#stopp());
		this.root.addEventListener('focusout', () => this.#start());
		document.addEventListener('visibilitychange', () => {
			document.hidden ? this.#stopp() : this.#start();
		});
	}

	#start() {
		if (this.timer || !this.sekunden || !this.sanft || this.imEditor) return;
		if (this.root.classList.contains('ist-statisch')) return;
		this.timer = setInterval(() => this.#scrolleZu(this.aktiv + 1), this.sekunden * 1000);
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

function initAlle(bereich = document) {
	bereich.querySelectorAll('.mmk-showcase-slider').forEach((el) => {
		if (!el.mmkSlider) el.mmkSlider = new MmkShowcaseSlider(el);
	});
}

// Elementor rendert Widgets auch nachträglich (Editor, Popups)
window.addEventListener('elementor/frontend/init', () => {
	window.elementorFrontend.hooks.addAction('frontend/element_ready/mmk-showcase-slider.default', (element) => {
		initAlle(element[0] ?? element);
	});
});

if (document.readyState === 'loading') {
	document.addEventListener('DOMContentLoaded', () => initAlle());
} else {
	initAlle();
}
