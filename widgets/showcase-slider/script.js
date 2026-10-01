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
		this.pauseBeiHover = root.dataset.pauseHover !== '0';
		this.aktiv    = 0;
		this.timer    = null;
		this.anim     = null;
		this.sichtbar = new Set();
		this.sanft    = !matchMedia('(prefers-reduced-motion: reduce)').matches;
		this.imEditor = document.body.classList.contains('elementor-editor-active');

		this.#pruefeModus();
		new ResizeObserver(() => this.#pruefeModus()).observe(this.track);
		this.#beobachteAktiv();
		this.#navigation();
		this.#autoplay();

		['pointerdown', 'wheel', 'touchstart'].forEach((ereignis) => {
			this.track.addEventListener(ereignis, () => this.#animationStopp(), {passive: true});
		});
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
		if (!this.sanft) {
			this.track.scrollTo({left: links});
			return;
		}
		this.#animiere(links);
	}

	// Eigene Scroll-Animation statt behavior:'smooth' — Chrome bricht
	// natives sanftes Scrollen bei aktivem Scroll-Snap teils sofort ab.
	// Während der Animation ist das Einrasten aus, danach wieder an.
	#animiere(ziel) {
		this.#animationStopp();
		const start = this.track.scrollLeft;
		const weg   = Math.min(ziel, this.track.scrollWidth - this.track.clientWidth) - start;
		if (Math.abs(weg) < 1) return;
		const dauer = 500;
		const t0    = performance.now();
		this.track.style.scrollSnapType = 'none';
		const schritt = (t) => {
			const p = Math.min(1, (t - t0) / dauer);
			this.track.scrollLeft = start + weg * (1 - Math.pow(1 - p, 3));   // easeOutCubic
			if (p < 1) {
				this.anim = requestAnimationFrame(schritt);
			} else {
				this.anim = null;
				this.track.style.scrollSnapType = '';
			}
		};
		this.anim = requestAnimationFrame(schritt);
	}

	// Greift der Nutzer ein (Wischen, Mausrad), gewinnt der Nutzer
	#animationStopp() {
		if (!this.anim) return;
		cancelAnimationFrame(this.anim);
		this.anim = null;
		this.track.style.scrollSnapType = '';
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

	// Rewind statt geklonter Karten: Am Ende geht es vorne wieder los.
	// Hält DOM und Screenreader sauber (kein Inhalt doppelt).
	#amEnde() {
		return this.track.scrollLeft >= this.track.scrollWidth - this.track.clientWidth - 1;
	}

	#weiter() {
		this.#amEnde() ? this.#scrolleZu(0) : this.#scrolleZu(this.aktiv + 1);
	}

	#zurueck() {
		this.track.scrollLeft <= 1 ? this.#scrolleZu(this.karten.length - 1) : this.#scrolleZu(this.aktiv - 1);
	}

	#navigation() {
		this.punkte.forEach((punkt, i) => {
			punkt.addEventListener('click', () => {this.#scrolleZu(i); this.#neustart();});
		});
		this.root.querySelector('.mmk-showcase-slider__slider-pfeil.zurueck')
			?.addEventListener('click', () => {this.#zurueck(); this.#neustart();});
		this.root.querySelector('.mmk-showcase-slider__slider-pfeil.weiter')
			?.addEventListener('click', () => {this.#weiter(); this.#neustart();});
	}

	#autoplay() {
		if (!this.sekunden || !this.sanft || this.imEditor) return;
		this.#start();
		if (this.pauseBeiHover) {
			this.root.addEventListener('pointerenter', () => this.#stopp());
			this.root.addEventListener('pointerleave', () => this.#start());
		}
		// Fokus-Pause bleibt immer an: Tastatur-Nutzer müssen bewegte
		// Inhalte anhalten können (WCAG 2.2.2)
		this.root.addEventListener('focusin', () => this.#stopp());
		this.root.addEventListener('focusout', () => this.#start());
		document.addEventListener('visibilitychange', () => {
			document.hidden ? this.#stopp() : this.#start();
		});
	}

	#start() {
		if (this.timer || !this.sekunden || !this.sanft || this.imEditor) return;
		if (this.root.classList.contains('ist-statisch')) return;
		this.timer = setInterval(() => this.#weiter(), this.sekunden * 1000);
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
