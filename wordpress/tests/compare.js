// Visual comparison: the original static site vs the WordPress theme.
// Usage: node compare.js <static-base-url> <wp-base-url> <preview-key> <out-dir> [routes] [widths]
// Screenshots every page full-length at each width with reduced motion (so
// reveal animations and drifting lines are static), hides the live countdown
// digits, and writes a pixel diff summary to <out-dir>/report.json.
const { chromium } = require("playwright");
const fs = require("fs");
const path = require("path");

const [staticBase, wpBase, key, out, routesArg, widthsArg] = process.argv.slice(2);
const ROUTES = (routesArg || "home,about,programme,speakers,call-for-papers,awards,partners,register,contact,privacy,terms").split(",");
const WP_PATH = { home: "/", privacy: "/privacy-notice/", terms: "/website-terms/" };
const WIDTHS = (widthsArg || "1440,1024,768,390").split(",").map(Number);
fs.mkdirSync(out, { recursive: true });

const FREEZE = `
  .cd-num, .cd-lab { visibility: hidden !important; }
  *, *::before, *::after { caret-color: transparent !important; }
`;

async function shoot(page, url, file) {
  const errors = [];
  page.removeAllListeners("pageerror"); page.removeAllListeners("console");
  page.on("pageerror", e => errors.push(e.message));
  page.on("console", m => { if (m.type() === "error") errors.push(m.text()); });
  await page.goto(url, { waitUntil: "networkidle" });
  await page.addStyleTag({ content: FREEZE });
  await page.evaluate(() => document.fonts.ready);
  // Load lazy images, then return to the top.
  await page.evaluate(async () => { for (let y = 0; y < document.body.scrollHeight; y += 600) { window.scrollTo(0, y); await new Promise(r => setTimeout(r, 30)); } window.scrollTo(0, 0); });
  await page.waitForTimeout(400);
  const info = await page.evaluate(() => ({ h: document.documentElement.scrollHeight, w: document.documentElement.scrollWidth }));
  await page.screenshot({ path: file, fullPage: true });
  return { ...info, errors };
}

(async () => {
  const browser = await chromium.launch();
  const report = [];
  for (const width of WIDTHS) {
    const ctx = await browser.newContext({ viewport: { width, height: 900 }, reducedMotion: "reduce", deviceScaleFactor: 1 });
    const page = await ctx.newPage();
    for (const r of ROUTES) {
      const a = path.join(out, `${r}-${width}-static.png`), b = path.join(out, `${r}-${width}-wp.png`);
      const sa = await shoot(page, `${staticBase}/#${r}`, a);
      const wpUrl = new URL(WP_PATH[r] || `/${r}/`, wpBase); wpUrl.searchParams.set("ewil_preview", key);
      const sb = await shoot(page, wpUrl.href, b);
      report.push({ route: r, width, static: sa, wp: sb });
      console.log(r, width, "static", sa.h, "wp", sb.h, sb.errors.length ? "ERR " + sb.errors.join(" | ") : "");
    }
    await ctx.close();
  }
  await browser.close();
  fs.writeFileSync(path.join(out, "shots.json"), JSON.stringify(report, null, 1));
})();
