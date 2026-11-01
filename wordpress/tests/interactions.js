// Behaviour checks on the WordPress version.
// Usage: node interactions.js <wp-base-url> <preview-key> <pdf-file>
// Note: the two submission tests create real submissions (and notification
// emails) marked TEST; delete them afterwards.
const { chromium } = require("playwright");
const [base, key, pdf] = process.argv.slice(2);
const u = (p, extra = "") => `${base}${p}${p.includes("?") ? "&" : "?"}ewil_preview=${key}${extra}`;
const results = [];
const check = (name, ok, detail = "") => { results.push({ name, ok, detail }); console.log(ok ? "PASS" : "FAIL", name, detail); };

(async () => {
  const browser = await chromium.launch();
  const ctx = await browser.newContext({ viewport: { width: 1280, height: 900 } });
  const page = await ctx.newPage();
  const errors = [];
  page.on("pageerror", e => errors.push(e.message));

  // Countdown ticks.
  await page.goto(u("/"), { waitUntil: "networkidle" });
  const t1 = await page.textContent(".cd");
  await page.waitForTimeout(1500);
  const t2 = await page.textContent(".cd");
  check("countdown shows and ticks", !!t1 && t1 !== t2, `${t1} → ${t2}`);
  check("wave edges and contours drawn", await page.evaluate(() => document.querySelectorAll("path[data-edge][d]").length === 9 && document.querySelectorAll("svg[data-contours] path").length > 20));
  check("track bands drawn", await page.evaluate(() => document.querySelectorAll("svg[data-tk] path").length === 36));

  // Newsletter: empty submit shows errors.
  await page.click('form[data-kind="newsletter"] button[type=submit]');
  const status = await page.textContent('form[data-kind="newsletter"] .status');
  const invalid = await page.$$eval('form[data-kind="newsletter"] .field.invalid', els => els.length);
  check("empty form shows errors", status.includes("Please check the form") && invalid === 4, `${invalid} fields flagged`);

  // Rate links: from the homepage to the register page with the rate chosen.
  await page.click('a[data-rate="no-fee-school"]');
  await page.waitForLoadState("networkidle");
  const chosen = await page.$eval('#reg-form input[name="rate"]:checked', i => i.value);
  const noteShown = await page.$eval("[data-rate-note]", n => !n.hidden);
  check("price button pre-selects the rate", chosen === "no-fee-school" && page.url().includes("#reg-form"), chosen);
  check("rate note appears for the no-fee rate", noteShown);
  // Same-page rate click.
  await page.click('.prices a[data-rate="standard"]');
  check("same-page rate click updates the form", (await page.$eval('#reg-form input[name="rate"]:checked', i => i.value)) === "standard");

  // Partners: Enquire pre-selects the interest.
  await page.goto(u("/partners/"), { waitUntil: "networkidle" });
  await page.click('a[data-interest="Delegate scholarships"]');
  check("Enquire pre-selects the interest", (await page.$eval('#partner-form select[name="interest"]', s => s.value)) === "Delegate scholarships");

  // Awards: nominations not yet open.
  await page.goto(u("/awards/"), { waitUntil: "networkidle" });
  const warn = await page.textContent('form[data-kind="nomination"] .status.warn').catch(() => "");
  const disabled = await page.$eval('form[data-kind="nomination"] button[type=submit]', b => b.disabled);
  check("nomination form locked before opening date", warn.includes("Nominations open on 9 November 2026") && disabled, warn.trim().slice(0, 60));

  // Old one-page links go to the new pages.
  await page.goto(u("/", "#about"), { waitUntil: "networkidle" });
  check("old #about link redirects", new URL(page.url()).pathname === "/about/", page.url().replace(key, "KEY"));

  // Word counter.
  await page.goto(u("/call-for-papers/"), { waitUntil: "networkidle" });
  await page.fill('textarea[name="abstract"]', "one two three");
  check("word counter updates", (await page.textContent('textarea[name="abstract"] ~ .hint [data-wc]')).startsWith("3 / 300–500"));

  // Real submission: contact form.
  await page.goto(u("/contact/"), { waitUntil: "networkidle" });
  await page.fill('form[data-kind="contact"] [name="name"]', "TEST Website check");
  await page.fill('form[data-kind="contact"] [name="email"]', "test@example.com");
  await page.selectOption('form[data-kind="contact"] [name="topic"]', "Something else");
  await page.fill('form[data-kind="contact"] [name="message"]', "TEST message from the automated website check. Please ignore.");
  await page.check('form[data-kind="contact"] [name="processing_consent"]');
  await page.waitForTimeout(3200);
  await page.click('form[data-kind="contact"] button[type=submit]');
  await page.waitForSelector('form[data-kind="contact"] .status:not([hidden])');
  const cs = await page.textContent('form[data-kind="contact"] .status');
  check("contact form submits", cs.startsWith("Received."), cs.trim().slice(0, 80));

  // Real submission: paper with a supporting document.
  await page.goto(u("/call-for-papers/"), { waitUntil: "networkidle" });
  const f = 'form[data-kind="paper"] ';
  await page.fill(f + '[name="title"]', "TEST proposal");
  await page.selectOption(f + '[name="track"]', { index: 1 });
  await page.selectOption(f + '[name="format"]', { index: 1 });
  await page.fill(f + '[name="abstract"]', Array(320).fill("word").join(" "));
  await page.setInputFiles(f + '[name="document"]', pdf);
  await page.fill(f + '[name="name"]', "TEST Website check");
  await page.fill(f + '[name="role"]', "Tester");
  await page.fill(f + '[name="organisation"]', "Test");
  await page.fill(f + '[name="email"]', "test@example.com");
  await page.selectOption(f + '[name="country"]', "South Africa");
  await page.fill(f + '[name="bio"]', Array(120).fill("bio").join(" "));
  await page.check(f + '[name="processing_consent"]');
  await page.waitForTimeout(3200);
  await page.click(f + "button[type=submit]");
  await page.waitForSelector(f + ".status:not([hidden])");
  const ps = await page.textContent(f + ".status");
  check("paper form submits with a file", ps.startsWith("Received."), ps.trim().slice(0, 80));

  check("no JavaScript errors", errors.length === 0, errors.join(" | "));
  await browser.close();
  process.exit(results.every(r => r.ok) ? 0 : 1);
})();
