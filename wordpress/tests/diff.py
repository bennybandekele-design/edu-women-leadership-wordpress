"""Pixel diff of the screenshot pairs made by compare.js.
Usage: python3 -I diff.py <out-dir>
Writes <name>-diff.png (changed pixels in red) and prints the share of pixels
that differ for each page and width."""
import json
import sys
import pathlib
from PIL import Image, ImageChops

out = pathlib.Path(sys.argv[1])
rows = []
for shot in json.loads((out / "shots.json").read_text()):
    name = f"{shot['route']}-{shot['width']}"
    a = Image.open(out / f"{name}-static.png").convert("RGB")
    b = Image.open(out / f"{name}-wp.png").convert("RGB")
    h = max(a.height, b.height)
    pa = Image.new("RGB", (a.width, h), "white"); pa.paste(a, (0, 0))
    pb = Image.new("RGB", (a.width, h), "white"); pb.paste(b, (0, 0))
    diff = ImageChops.difference(pa, pb).convert("L").point(lambda v: 255 if v > 24 else 0)
    changed = sum(1 for v in diff.getdata() if v)
    share = changed / (a.width * h) * 100
    first = None
    bbox = diff.getbbox()
    if bbox:
        first = bbox[1]
        overlay = pb.copy()
        red = Image.new("RGB", pb.size, (255, 0, 0))
        overlay.paste(red, (0, 0), diff)
        overlay.save(out / f"{name}-diff.png")
    rows.append((name, a.height, b.height, round(share, 3), first))
for r in rows:
    print(f"{r[0]:28} static {r[1]:6}px  wp {r[2]:6}px  diff {r[3]:7}%  first change at y={r[4]}")
