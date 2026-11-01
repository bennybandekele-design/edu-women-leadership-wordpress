# Edu Women in Leadership Conference 2027

Static, single-page conference website (hash-routed: `#home`, `#about`, `#programme`, `#speakers`, `#call-for-papers`, `#awards`, `#partners`, `#register`, `#contact`). Originally designed in Claude Design.

## Structure
- `index.html` — all markup, styles and scripts
- `img/` — photography and logo

## Run locally
```bash
python3 -m http.server 8080
```
Then open http://localhost:8080.

## Deploy
No build step. Any static host works (GitHub Pages, Netlify, Vercel, Cloudflare Pages): publish the repository root.
