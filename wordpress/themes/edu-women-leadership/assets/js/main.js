/* Edu Women in Leadership: site behaviour. Ported from the original static
   site: wave and contour drawing, countdown, menu, scroll reveal, copy buttons.
   The one-page router is replaced by real pages; "jump to form" links and
   rate/interest pre-selection work across pages through ?rate= and #anchors. */
(function () {
  "use strict";
  const $ = (s, r = document) => r.querySelector(s);
  const $$ = (s, r = document) => Array.from(r.querySelectorAll(s));

  /* ---------- links shared before the move (#about, #register …) ---------- */
  const routes = (window.EWIL && window.EWIL.routes) || {};
  const legacy = location.hash.slice(1);
  if (legacy && routes[legacy] && !document.getElementById(legacy)) {
    const target = new URL(routes[legacy], location.href);
    if (target.pathname !== location.pathname) { location.replace(target.href); return; }
  }

  /* ---------- decorative graphics (identical maths to the original) ---------- */
  function wavePath(w, y, amp, f, phase, x0 = 0) {
    let d = "";
    for (let x = x0; x <= w + 40; x += 10) {
      const yy = y + amp * Math.sin(x * f + phase) + amp * 0.35 * Math.sin(x * f * 2.3 + phase * 1.7);
      d += (d ? " L" : "M") + x + " " + yy.toFixed(1);
    }
    return d;
  }
  function drawTrackBands() {
    $$("svg[data-tk]").forEach(svg => {
      const i = +svg.dataset.tk;
      svg.innerHTML = Array.from({ length: 6 }, (_, k) => `<path class="${k === 3 ? "hot" : ""}" d="${wavePath(340, 18 + k * 12, 7 + (k % 3) * 2, 0.018 + i * 0.002, i * 1.3 + k * 0.5, -20)}"/>`).join("");
    });
  }
  function drawSpeakerEdges() {
    $$("svg[data-spk-edge]").forEach(svg => {
      const i = +svg.dataset.spkEdge;
      let d = "M0 34 L0 18"; let l = "";
      for (let x = 0; x <= 400; x += 10) { const y = 18 + 7 * Math.sin(x * 0.022 + i * 1.4); d += ` L${x} ${y.toFixed(1)}`; l += (l ? " L" : "M") + x + " " + y.toFixed(1); }
      svg.innerHTML = `<path d="${d} L400 34 Z"/><path class="ln" d="${l}"/>`;
    });
  }
  function drawTimeline() {
    $$("svg.tl-wave path").forEach(p => {
      let d = "M0 20"; for (let x = 0; x <= 300; x += 4) d += ` L${x} ${(20 + 9 * Math.sin(Math.PI * x / 103.4)).toFixed(1)}`;
      p.setAttribute("d", d);
    });
  }
  function drawContours() {
    $$("svg[data-contours]").forEach(svg => {
      if (svg.dataset.drawn) return;
      const n = +svg.dataset.contours, amp = +svg.dataset.amp || 24, seed = +svg.dataset.seed || 1, W = 1600, H = 800;
      svg.setAttribute("viewBox", `0 0 1440 ${H}`); svg.setAttribute("preserveAspectRatio", "none");
      let g = `<g class="drift">`;
      for (let i = 0; i < n; i++) g += `<path d="${wavePath(W, (H / (n + 1)) * (i + 1), amp * (0.7 + ((i * 7 + seed) % 5) / 8), 0.004 + seed * 0.0002, seed + i * 0.42, -40)}"/>`;
      svg.innerHTML = g + "</g>"; svg.dataset.drawn = 1;
    });
  }
  function drawEdges() {
    $$("path[data-edge]").forEach(p => {
      const s = +p.dataset.edge, flip = p.dataset.flip === "1";
      const pts = [];
      for (let x = 0; x <= 1440; x += 24) pts.push([x, 36 + 14 * Math.sin(x * 0.0042 + s * 1.1) + 7 * Math.sin(x * 0.011 + s * 2.3)]);
      const line = pts.map((q, i) => (i ? "L" : "") + q[0] + " " + q[1].toFixed(1)).join(" ");
      p.setAttribute("d", flip ? `M0 0 L${line} L1440 0 Z` : `M0 72 L${line} L1440 72 Z`);
    });
  }
  function reveal() {
    if (matchMedia("(prefers-reduced-motion: reduce)").matches || !("IntersectionObserver" in window)) return;
    const io = new IntersectionObserver(es => es.forEach(e => { if (e.isIntersecting) { e.target.classList.remove("rv-pre"); io.unobserve(e.target); } }), { rootMargin: "0px 0px -8% 0px" });
    $$(".rv").forEach(el => { const r = el.getBoundingClientRect(); if (r.height && r.top > innerHeight) { el.classList.add("rv-pre"); io.observe(el); } });
  }

  /* ---------- countdown ---------- */
  function tickCountdown() {
    const now = Date.now();
    $$("[data-countdown]").forEach(el => {
      const start = new Date(el.dataset.start).getTime(), end = new Date(el.dataset.end).getTime();
      const labels = (el.dataset.labels || "Days,Hours,Minutes,Seconds").split(",");
      if (!isNaN(end) && now >= end) { el.innerHTML = `<p class="cd-live">${esc(el.dataset.msgAfter)}</p>`; return; }
      if (now >= start) { el.innerHTML = `<p class="cd-live"><span class="dot" aria-hidden="true"></span>${esc(el.dataset.msgLive)}</p>`; return; }
      let s = Math.floor((start - now) / 1000);
      const d = Math.floor(s / 86400), h = Math.floor(s % 86400 / 3600), m = Math.floor(s % 3600 / 60); s = s % 60;
      const p = n => String(n).padStart(2, "0");
      const units = [[d, labels[0]], [p(h), labels[1]], [p(m), labels[2]], [p(s), labels[3]]];
      if (!el.firstChild || !el.querySelector(".cd")) el.innerHTML = `<div class="cd" role="timer"></div>`;
      const cd = el.querySelector(".cd");
      cd.setAttribute("aria-label", `${d} days, ${h} hours and ${m} minutes until the conference`);
      cd.innerHTML = units.map(u => `<div class="cd-unit" aria-hidden="true"><span class="cd-num">${u[0]}</span><span class="cd-lab">${esc(u[1])}</span></div>`).join("");
    });
  }
  const esc = s => String(s ?? "").replace(/[&<>"']/g, c => ({ "&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;", "'": "&#39;" }[c]));

  /* ---------- menu ---------- */
  const burger = $("#burger"), menu = $("#menu");
  function closeMenu() {
    if (!burger) return;
    menu.classList.remove("open"); burger.setAttribute("aria-expanded", "false"); burger.setAttribute("aria-label", burger.dataset.labelOpen || "Open menu"); burger.innerHTML = '<svg><use href="#i-menu"/></svg>';
  }
  if (burger && menu) {
    burger.addEventListener("click", () => {
      const open = !menu.classList.contains("open"); if (!open) return closeMenu();
      menu.classList.add("open"); burger.setAttribute("aria-expanded", "true"); burger.setAttribute("aria-label", burger.dataset.labelClose || "Close menu"); burger.innerHTML = '<svg><use href="#i-x"/></svg>';
    });
    document.addEventListener("keydown", e => { if (e.key === "Escape" && menu.classList.contains("open")) { closeMenu(); burger.focus(); } });
    // On narrow screens the menu scrolls sideways: centre the current page.
    // Measured again once the web fonts have loaded, as text widths change.
    const centreCurrent = () => {
      const cur = $('.menu a[aria-current="page"]');
      if (cur && menu.scrollWidth > menu.clientWidth) menu.scrollLeft = cur.offsetLeft - menu.offsetLeft - menu.clientWidth / 2 + cur.offsetWidth / 2;
    };
    centreCurrent();
    if (document.fonts && document.fonts.ready) document.fonts.ready.then(centreCurrent);
  }

  /* ---------- rate and interest pre-selection, jump-to-form links ---------- */
  function selectRate(rate) {
    const i = rate && $(`#reg-form input[name="rate"][value="${CSS.escape(rate)}"]`);
    if (i) { i.checked = true; i.dispatchEvent(new Event("input", { bubbles: true })); }
  }
  function selectInterest(interest) {
    const s = interest && $('#partner-form select[name="interest"]');
    if (s) s.value = interest;
  }
  document.addEventListener("click", e => {
    const a = e.target.closest("a[href]"); if (!a) return;
    const url = new URL(a.href, location.href);
    if (url.origin !== location.origin || url.pathname !== location.pathname) return;
    const target = a.dataset.scroll && document.getElementById(a.dataset.scroll);
    if (a.dataset.rate) selectRate(a.dataset.rate);
    if (a.dataset.interest) selectInterest(a.dataset.interest);
    if (target) { e.preventDefault(); history.replaceState(null, "", "#" + a.dataset.scroll); target.scrollIntoView({ behavior: "smooth", block: "start" }); closeMenu(); }
  });
  const params = new URLSearchParams(location.search);
  if (params.get("rate")) selectRate(params.get("rate"));
  if (params.get("interest")) selectInterest(params.get("interest"));

  /* ---------- copy buttons (Contact) ---------- */
  document.addEventListener("click", async e => {
    const b = e.target.closest("[data-copy]"); if (!b) return;
    try { await navigator.clipboard.writeText(b.dataset.copy); b.textContent = b.dataset.copied || "Copied"; } catch { const c = b.previousElementSibling; const s = getSelection(); const rg = document.createRange(); rg.selectNodeContents(c); s.removeAllRanges(); s.addRange(rg); }
  });

  drawTrackBands(); drawSpeakerEdges(); drawTimeline(); drawContours(); drawEdges();
  tickCountdown(); reveal();
  setInterval(tickCountdown, 1000);
})();
