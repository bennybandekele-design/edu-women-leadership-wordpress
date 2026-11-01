/* EWIL Conference Core: form behaviour (validation, word counts, nomination
   window, rate notes, sending). Ported unchanged from the original static site;
   only the sending step now posts to WordPress. */
(function () {
  "use strict";
  const C = window.EWIL_FORMS || { i18n: {} };
  const T = C.i18n;
  const $$ = (s, r = document) => Array.from(r.querySelectorAll(s));
  const esc = s => String(s ?? "").replace(/[&<>"']/g, c => ({ "&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;", "'": "&#39;" }[c]));
  const words = s => (s || "").trim() ? s.trim().split(/\s+/).length : 0;
  const fmt = (s, ...a) => { let i = 0; return s.replace(/%(\d\$)?s/g, (m, n) => a[n ? parseInt(n, 10) - 1 : i++]); };
  const loadedAt = Date.now();

  function validateField(el) {
    const f = el.closest(".field"); if (!f) return true;
    const err = f.querySelector(".err"); let msg = "";
    const label = f.querySelector("label").childNodes[0].textContent.trim();
    if (el.required && !el.value.trim()) msg = el.tagName === "SELECT" ? fmt(T.choose, label.toLowerCase()) : fmt(T.enter, label.toLowerCase());
    else if (el.type === "email" && el.value && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(el.value)) msg = T.email;
    else if (el.dataset.min && el.value.trim()) { const n = words(el.value); if (n < +el.dataset.min || n > +el.dataset.max) msg = fmt(T.words, el.dataset.min, el.dataset.max, n); }
    else if (el.type === "file" && el.files[0]) {
      const ext = "." + el.files[0].name.split(".").pop().toLowerCase();
      if (!el.accept.split(",").map(x => x.trim()).includes(ext)) msg = fmt(T.fileType, C.fileLabel);
      else if (el.files[0].size > +el.dataset.maxmb * 1048576) msg = fmt(T.fileSize, el.dataset.maxmb);
    }
    showError(f, el, msg);
    return !msg;
  }
  function showError(f, el, msg) {
    const err = f.querySelector(".err");
    f.classList.toggle("invalid", !!msg); err.hidden = !msg; err.textContent = msg; el.setAttribute("aria-invalid", msg ? "true" : "false");
  }

  function gateNominations(form) {
    const now = Date.now(), open = new Date(form.dataset.open).getTime(), close = new Date(form.dataset.close).getTime();
    if ((isNaN(open) || now >= open) && (isNaN(close) || now <= close)) return;
    const before = now < open;
    form.insertAdjacentHTML("afterbegin", `<div class="status warn"><strong>${esc(before ? form.dataset.msgBefore : form.dataset.msgAfter)}</strong><span>${esc(before ? form.dataset.msgBefore2 : form.dataset.msgAfter2)}</span></div>`);
    $$("input, select, textarea, button", form).forEach(el => el.disabled = true);
  }

  function showRateNote(form) {
    const checked = form.querySelector('input[name="rate"]:checked');
    $$("[data-rate-note]", form).forEach(n => n.hidden = !checked || n.dataset.rateNote !== checked.value);
  }

  async function handleSubmit(form, kind) {
    const status = form.querySelector(".status");
    let ok = true, first = null;
    $$(".field .input", form).forEach(el => { if (!validateField(el)) { ok = false; first = first || el; } });
    const req = $$("input[type=checkbox][required]", form).filter(c => !c.checked);
    if (!ok || req.length) {
      status.hidden = false; status.className = "status bad";
      status.innerHTML = `<strong>${esc(T.checkForm)}</strong><span>${esc(!ok ? T.needFix : T.needConsent)}</span>`;
      (first || req[0]).focus(); return;
    }
    const body = new FormData(form);
    body.set("_ewil_ts", String(loadedAt));
    const btn = form.querySelector("button[type=submit]"); btn.disabled = true;
    let res;
    try {
      const r = await fetch(form.action, { method: "POST", body, credentials: "same-origin" });
      res = await r.json().catch(() => ({ ok: false, message: T.offline }));
    } catch (e) { res = { ok: false, message: T.offline }; }
    btn.disabled = false; status.hidden = false;
    if (res.ok) {
      status.className = "status ok";
      status.innerHTML = `<strong>${esc(T.received)}</strong><span>${esc(res.message)}</span>${res.note ? `<span class="small">${esc(res.note)}</span>` : ""}`;
      form.reset(); $$("[data-wc]", form).forEach(w => w.textContent = w.textContent.replace(/^\d+/, "0"));
      showRateNote(form);
    } else {
      // Show the server's field messages next to the fields, as the browser check does.
      let firstBad = null;
      Object.entries(res.errors || {}).forEach(([name, msg]) => {
        const el = form.querySelector(`[name="${CSS.escape(name)}"]`); const f = el && el.closest(".field");
        if (f) { showError(f, el, msg); firstBad = firstBad || el; }
      });
      status.className = "status bad";
      status.innerHTML = `<strong>${esc(T.failed)}</strong><span>${esc(res.message || T.offline)}</span>`;
      if (firstBad) firstBad.focus();
    }
    status.scrollIntoView({ block: "nearest", behavior: "smooth" });
  }

  $$("form[data-kind]").forEach(form => {
    const kind = form.dataset.kind;
    if (kind === "nomination") gateNominations(form);
    form.addEventListener("input", e => {
      const t = e.target;
      if (t.dataset.min) { const n = words(t.value), wc = t.closest(".field").querySelector("[data-wc]"); wc.textContent = `${n} / ${t.dataset.min}–${t.dataset.max} ${T.wordsUnit}`; wc.classList.toggle("bad", n > 0 && (n < +t.dataset.min || n > +t.dataset.max)); }
      if (t.closest(".field.invalid")) validateField(t);
      if (t.name === "rate") showRateNote(form);
    });
    form.addEventListener("submit", e => { e.preventDefault(); handleSubmit(form, kind); });
  });
})();
