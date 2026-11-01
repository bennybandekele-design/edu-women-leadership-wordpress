"""Defines the conference content model (ACF post types, taxonomy and field
groups) and writes one JSON payload per item into wordpress/model/payloads/.
The payloads are created on the site through Novamira's ACF abilities; ACF then
saves the canonical JSON into the plugin's acf-json folder."""
import json
import pathlib

OUT = pathlib.Path(__file__).parent / "payloads"
OUT.mkdir(exist_ok=True)
ACCENT = "Wrap words in *asterisks* to set them in the italic accent style."
TOKENS = "You can use {dates}, {venue}, {city}, {year}, {early_bird}, {abstract}, {bio}, {tracks} and {host}; they are filled in from Conference Settings."


def labels(singular, plural):
    return {
        "name": plural, "singular_name": singular, "menu_name": plural,
        "all_items": f"All {plural.lower()}", "add_new": f"Add {singular.lower()}",
        "add_new_item": f"Add {singular.lower()}", "edit_item": f"Edit {singular.lower()}",
        "new_item": f"New {singular.lower()}", "view_item": f"View {singular.lower()}",
        "search_items": f"Search {plural.lower()}", "not_found": f"No {plural.lower()} yet",
        "not_found_in_trash": f"No {plural.lower()} in the bin",
    }


def post_type(slug, singular, plural, icon, position, supports, title_ph, description, show_in_menu=True, taxonomies=()):
    return {
        "key": f"post_type_{slug}", "post_type": slug, "title": plural, "labels": labels(singular, plural),
        "description": description, "public": False, "publicly_queryable": False, "show_ui": True,
        "show_in_menu": show_in_menu, "show_in_nav_menus": False, "show_in_admin_bar": False,
        "exclude_from_search": True, "has_archive": False, "hierarchical": False, "rewrite": False,
        "query_var": False, "show_in_rest": True, "menu_position": position, "menu_icon": icon,
        "supports": supports, "taxonomies": list(taxonomies), "enter_title_here": title_ph,
        "advanced_configuration": True, "active": True, "delete_with_user": False,
    }


def f(group, name, label, ftype="text", **kw):
    field = {"key": f"field_ewil_{group}_{name}", "label": label, "name": name, "type": ftype}
    if ftype == "textarea":
        field.setdefault("rows", 3)
        field.setdefault("new_lines", "")
    if ftype == "image":
        field.update({"return_format": "id", "preview_size": "medium", "library": "all"})
    if ftype == "link":
        field["return_format"] = "array"
    if ftype == "true_false":
        field["ui"] = 1
    if ftype == "date_time_picker":
        field.update({"display_format": "j F Y H:i", "return_format": "Y-m-d H:i:s", "first_day": 1})
    if ftype == "date_picker":
        field.update({"display_format": "j F Y", "return_format": "Ymd", "first_day": 1})
    field.update(kw)
    return field


def tab(group, name, label):
    return {"key": f"field_ewil_{group}_tab_{name}", "label": label, "name": "", "type": "tab", "placement": "top", "endpoint": 0}


def grp(group, name, label, subs, **kw):
    sub_fields = []
    for s in subs:
        s = dict(s)
        s["key"] = f"field_ewil_{group}_{name}_{s['name']}"
        sub_fields.append(s)
    return {"key": f"field_ewil_{group}_{name}", "label": label, "name": name, "type": "group", "layout": "block", "sub_fields": sub_fields, **kw}


def field_group(key, title, fields, location, menu_order=0, position="normal", hide=None, description=""):
    return {
        "key": f"group_ewil_{key}", "title": title, "fields": fields, "location": location,
        "menu_order": menu_order, "position": position, "style": "default", "label_placement": "top",
        "instruction_placement": "label", "hide_on_screen": hide or [], "active": True,
        "description": description, "show_in_rest": False,
    }


def loc_type(pt):
    return [[{"param": "post_type", "operator": "==", "value": pt}]]


def loc_tpl(tpl):
    return [[{"param": "page_template", "operator": "==", "value": f"page-templates/{tpl}.php"}]]


def heading_block(g, prefix, eyebrow=True, heading=True, lede=True, lede_label="Intro text"):
    out = []
    if eyebrow:
        out.append(f(g, f"{prefix}_eyebrow", "Eyebrow", instructions="Small label above the heading."))
    if heading:
        out.append(f(g, f"{prefix}_heading", "Heading", instructions=ACCENT))
    if lede:
        out.append(f(g, f"{prefix}_lede", lede_label, "textarea"))
    return out


def btn(g, name, label):
    return f(g, name, label, "link", instructions="Button text and where it goes. Leave empty to hide the button.")


items = []

# ---------- post types ----------
items.append(("post-type", post_type("ewil_speaker", "Speaker", "Speakers", "dashicons-microphone", 25, ["title", "thumbnail", "page-attributes"], "Speaker’s name", "Confirmed speakers. The featured image is the portrait. Order sets the display order; the first six marked for the homepage appear there.")))
items.append(("post-type", post_type("ewil_track", "Track", "Tracks", "dashicons-flag", 26, ["title", "page-attributes"], "Track name", "Conference tracks (themes). Their order sets the track numbers.")))
items.append(("post-type", post_type("ewil_rate", "Registration rate", "Registration rates", "dashicons-tickets-alt", 27, ["title", "page-attributes"], "Rate name, e.g. Standard fee", "Registration prices shown on the homepage and the Register page, and the choices in the registration form.")))
items.append(("post-type", post_type("ewil_opportunity", "Partnership opportunity", "Partnership opportunities", "dashicons-groups", 28, ["title", "page-attributes"], "Opportunity name", "Ways to partner, shown on the homepage and the Partners page, and in the enquiry form.")))
items.append(("post-type", post_type("ewil_award_category", "Award category", "Award categories", "dashicons-awards", 29, ["title", "page-attributes"], "Category name", "Award categories. Once any exist they show on the Awards page and in the nomination form.")))
items.append(("post-type", post_type("ewil_highlight", "Highlight", "Highlights", "dashicons-star-filled", 30, ["title", "page-attributes"], "Short title", "Short items with an icon: the “Why attend” benefits and the “Who should attend” groups.", taxonomies=["ewil_highlight_group"])))
items.append(("post-type", post_type("ewil_settings", "Conference Settings", "Conference Settings", "dashicons-admin-generic", 58, ["title"], "Conference Settings", "Holds the site-wide conference settings. Opened from the Conference Settings menu.", show_in_menu=False)))

# ---------- taxonomy ----------
items.append(("taxonomy", {
    "key": "taxonomy_ewil_highlight_group", "taxonomy": "ewil_highlight_group", "title": "Highlight groups",
    "labels": {"name": "Highlight groups", "singular_name": "Highlight group", "menu_name": "Groups", "all_items": "All groups", "edit_item": "Edit group", "add_new_item": "Add group", "search_items": "Search groups", "not_found": "No groups yet"},
    "description": "Where a highlight appears: Why attend, or Who should attend.",
    "object_type": ["ewil_highlight"], "hierarchical": True, "public": False, "publicly_queryable": False,
    "show_ui": True, "show_in_menu": True, "show_in_nav_menus": False, "show_tagcloud": False,
    "show_in_quick_edit": True, "show_admin_column": True, "show_in_rest": True, "rewrite": False,
    "query_var": False, "advanced_configuration": True, "active": True,
}))

# ---------- field groups: post types ----------
g = "speaker"
items.append(("field-group", field_group(g, "Speaker details", [
    f(g, "role", "Role", instructions="For example Principal, or Director of Learning."),
    f(g, "organisation", "Organisation"),
    f(g, "session", "Session title", instructions="Leave empty until the session is confirmed."),
    f(g, "bio", "Short biography", "textarea", maxlength=320, instructions="Up to about 40 words. Shown when visitors hover over the portrait."),
    f(g, "linkedin", "LinkedIn profile", "url"),
    f(g, "show_on_home", "Show on the homepage", "true_false", default_value=1, instructions="The first six speakers with this switched on appear on the homepage."),
], loc_type("ewil_speaker"), description="Portrait: use the Featured image box.")))

g = "track"
items.append(("field-group", field_group(g, "Track details", [
    f(g, "description", "Description", "textarea", instructions="One or two sentences."),
], loc_type("ewil_track"))))

g = "rate"
items.append(("field-group", field_group(g, "Rate details", [
    f(g, "price", "Price", instructions="As it should appear, e.g. R3,500.", required=1),
    f(g, "unit", "Price unit", default_value="per delegate"),
    f(g, "featured", "Featured rate", "true_false", instructions="Highlights this card and gives it the orange button."),
    f(g, "badge", "Badge", instructions="Small label on the card, e.g. Gala dinner included."),
    f(g, "description", "Description", "textarea"),
    f(g, "includes", "What’s included", "textarea", instructions="One item per line."),
    f(g, "note", "Note", "textarea", instructions="Shown on the card, and in the registration form when this rate is chosen."),
    f(g, "button_label", "Button text", default_value="Register"),
], loc_type("ewil_rate"), description="The rate's web address name (slug) is used in links like /register/?rate=standard.")))

g = "opportunity"
items.append(("field-group", field_group(g, "Opportunity details", [
    f(g, "description", "Description", "textarea"),
], loc_type("ewil_opportunity"))))

g = "award_category"
items.append(("field-group", field_group(g, "Category details", [
    f(g, "description", "Description", "textarea", instructions="Optional."),
], loc_type("ewil_award_category"))))

g = "highlight"
items.append(("field-group", field_group(g, "Highlight details", [
    f(g, "description", "Description", "textarea", instructions="Used for “Why attend” items. Leave empty for “Who should attend” groups."),
    f(g, "icon", "Icon", "select", choices={"i-school": "School", "i-users": "People", "i-book": "Book", "i-landmark": "Government building", "i-search": "Research (magnifier)", "i-heart": "Heart", "i-cpu": "Technology (chip)", "i-handshake": "Handshake"}, default_value="i-school", return_format="value", ui=0),
], loc_type("ewil_highlight"), description="Choose the group (Why attend / Who should attend) in the Highlight groups box.")))

# ---------- conference settings ----------
g = "settings"
items.append(("field-group", field_group(g, "Conference Settings", [
    tab(g, "general", "General"),
    f(g, "event_name", "Conference name", instructions="Shown in the header, footer and page titles."),
    f(g, "event_year", "Year"),
    f(g, "event_theme", "Conference theme", instructions=ACCENT),
    f(g, "event_start", "Conference starts", "date_time_picker", instructions="Used by the homepage countdown."),
    f(g, "event_end", "Conference ends", "date_time_picker", instructions="After this time the countdown shows a thank-you message."),
    f(g, "date_text", "Dates as written", instructions="E.g. 27–28 August 2027."),
    f(g, "date_short", "Short dates", instructions="E.g. 27–28 Aug, used in the hero tag."),
    f(g, "venue", "Venue"),
    f(g, "city", "City"),
    f(g, "host", "Host organisation"),
    f(g, "early_bird_deadline", "Early-bird deadline", instructions="As written, e.g. 28 February 2027."),
    f(g, "photo_credit", "Photo credit line", instructions="Small note under the homepage hero."),
    f(g, "meta_description", "Search engine description", "textarea", instructions="The summary search engines may show."),
    tab(g, "announce", "Announcement bar"),
    f(g, "announce_show", "Show the announcement bar", "true_false", default_value=1),
    f(g, "announce_text", "Announcement"),
    f(g, "announce_link", "Link", "link"),
    tab(g, "registration", "Registration"),
    f(g, "payment_note", "Payment note", "textarea", instructions="Shown above the registration form."),
    tab(g, "cfp", "Call for Papers"),
    f(g, "cfp_deadline", "Submission deadline", instructions="As written, e.g. 31 March 2027. Leave empty to show “To be announced”."),
    f(g, "cfp_abstract_min", "Abstract: minimum words", "number", wrapper={"width": "50"}),
    f(g, "cfp_abstract_max", "Abstract: maximum words", "number", wrapper={"width": "50"}),
    f(g, "cfp_bio_min", "Biography: minimum words", "number", wrapper={"width": "50"}),
    f(g, "cfp_bio_max", "Biography: maximum words", "number", wrapper={"width": "50"}),
    f(g, "cfp_file_types", "Allowed file types", instructions="Comma-separated extensions, e.g. .pdf,.doc,.docx", wrapper={"width": "50"}),
    f(g, "cfp_file_label", "File types as written", instructions="E.g. PDF or Word.", wrapper={"width": "50"}),
    f(g, "cfp_max_mb", "Maximum file size (MB)", "number"),
    f(g, "cfp_formats", "Presentation formats", "textarea", rows=5, instructions="One per line."),
    tab(g, "awards", "Awards"),
    f(g, "awards_open", "Nominations open", "date_time_picker", wrapper={"width": "50"}),
    f(g, "awards_close", "Nominations close", "date_time_picker", wrapper={"width": "50"}),
    f(g, "awards_ceremony", "Awards ceremony date", "date_picker", wrapper={"width": "50"}),
    f(g, "awards_ceremony_note", "Ceremony note", wrapper={"width": "50"}),
    f(g, "awards_eligibility", "Eligibility", "textarea", instructions="Leave empty to show “will be published when nominations open”."),
    f(g, "awards_judging", "Judging", "textarea"),
    f(g, "awards_guidelines", "Nomination guidelines", "textarea", instructions="Shown under “Nominate a leader”."),
    tab(g, "contact", "Contact & social"),
    f(g, "contact_email", "Email", "email"),
    f(g, "contact_phone", "Phone"),
    f(g, "contact_address", "Address", "textarea"),
    f(g, "social_linkedin", "LinkedIn", "url"),
    f(g, "social_facebook", "Facebook", "url"),
    f(g, "social_instagram", "Instagram", "url"),
    f(g, "social_x", "X (Twitter)", "url"),
    f(g, "social_youtube", "YouTube", "url"),
    tab(g, "forms", "Forms"),
    f(g, "notify_email", "Send new submissions to", instructions="One or more email addresses, comma-separated. Empty uses the site admin email."),
    f(g, "msg_registration", "Registration: thank-you message", "textarea", rows=2),
    f(g, "msg_paper", "Paper proposal: thank-you message", "textarea", rows=2),
    f(g, "msg_nomination", "Nomination: thank-you message", "textarea", rows=2),
    f(g, "msg_partnership", "Partnership enquiry: thank-you message", "textarea", rows=2),
    f(g, "msg_newsletter", "Community sign-up: thank-you message", "textarea", rows=2),
    f(g, "msg_contact", "Contact message: thank-you message", "textarea", rows=2),
    f(g, "list_roles", "Professional roles", "textarea", rows=6, instructions="Dropdown choices, one per line."),
    f(g, "list_countries", "Countries", "textarea", rows=6, instructions="Dropdown choices, one per line."),
    f(g, "list_provinces", "Provinces", "textarea", rows=6, instructions="Dropdown choices, one per line."),
    f(g, "list_topics", "Contact topics", "textarea", rows=6, instructions="Dropdown choices, one per line."),
    f(g, "retention_months", "Delete submissions after (months)", "number", instructions="0 keeps them until deleted by hand."),
], loc_type("ewil_settings"), hide=["permalink", "slug", "author", "revisions"])))

# ---------- pages ----------
g = "page_header"
items.append(("field-group", field_group(g, "Page header", [
    f(g, "page_eyebrow", "Eyebrow", instructions="Small label above the page heading."),
    f(g, "page_heading", "Heading", instructions="Leave empty to use the page title."),
    f(g, "page_lede", "Intro text", "textarea"),
    btn(g, "page_button", "Button"),
], [[{"param": "post_type", "operator": "==", "value": "page"}, {"param": "page_type", "operator": "!=", "value": "front_page"}]], menu_order=0, position="acf_after_title")))

g = "front"
front = [
    tab(g, "hero", "Hero"),
    f(g, "hero_kicker", "Label above the title"),
    f(g, "hero_title", "Title"),
    f(g, "hero_title_sub", "Title second line", instructions="Shown in small capitals, e.g. CONFERENCE 2027."),
    f(g, "hero_lede", "Intro text", "textarea"),
    btn(g, "hero_button_1", "Main button"), btn(g, "hero_button_2", "Second button"), btn(g, "hero_button_3", "Text link button"),
    f(g, "hero_image", "Main photo", "image"), f(g, "hero_image_inset", "Small photo", "image"),
    tab(g, "countdown", "Countdown"),
    f(g, "cd_eyebrow", "Label"), f(g, "cd_label", "Place and date"),
    f(g, "cd_eb_title", "Early-bird title"), f(g, "cd_eb_text", "Early-bird text", instructions=TOKENS), btn(g, "cd_eb_link", "Early-bird link"),
    tab(g, "facts", "Key facts"),
]
for i in range(1, 5):
    front.append(grp(g, f"fact_{i}", f"Fact {i}", [{"label": "Big text", "name": "value", "type": "text"}, {"label": "Small text", "name": "label", "type": "text"}], instructions=TOKENS if i == 1 else ""))
front += [tab(g, "why", "Why attend"), *heading_block(g, "why"), f(g, "why_image", "Photo", "image"),
          {**tab(g, "whyinfo", ""), "type": "message", "name": "", "label": "", "message": "The list comes from Highlights in the “Why attend” group."},
          tab(g, "who", "Who should attend"), *heading_block(g, "who"),
          tab(g, "tracks", "Tracks"), *heading_block(g, "tracks"),
          tab(g, "cfp", "Call for Papers"), *heading_block(g, "cfp"),
          f(g, "cfp_step_1", "Step 1", instructions=TOKENS), f(g, "cfp_step_2", "Step 2", instructions=TOKENS), f(g, "cfp_step_3", "Step 3", instructions=TOKENS),
          f(g, "cfp_deadline_label", "Deadline label"),
          btn(g, "cfp_button_1", "Main button"), btn(g, "cfp_button_2", "Second button"), f(g, "cfp_image", "Photo", "image"),
          tab(g, "speakers", "Speakers"), *heading_block(g, "spk"), btn(g, "spk_button_1", "Main button"), btn(g, "spk_button_2", "Second button"),
          tab(g, "awards", "Awards"), *heading_block(g, "aw"), btn(g, "aw_button_1", "Main button"), btn(g, "aw_button_2", "Second button"), f(g, "aw_image", "Photo", "image"),
          tab(g, "partners", "Partners"), *heading_block(g, "pt"), btn(g, "pt_button", "Button"),
          tab(g, "registration", "Registration"), *heading_block(g, "reg", lede=False),
          tab(g, "community", "Community"), *heading_block(g, "nl", lede_label="Text"), f(g, "nl_image", "Photo", "image")]
front = [x for x in front if x.get("type") != "message"]
items.append(("field-group", field_group(g, "Homepage sections", front, [[{"param": "page_type", "operator": "==", "value": "front_page"}]], hide=["the_content"])))

g = "about"
items.append(("field-group", field_group(g, "About page", [
    f(g, "about_image", "Photo", "image"),
    f(g, "about_heading", "Section heading", instructions="The text below it is written in the page editor."),
    btn(g, "about_button_1", "Main button"), btn(g, "about_button_2", "Second button"),
    f(g, "aud_eyebrow", "Audience: eyebrow"), f(g, "aud_heading", "Audience: heading"),
], loc_tpl("about"), menu_order=1)))

g = "programme"
items.append(("field-group", field_group(g, "Programme page", [
    *[grp(g, f"box_{i}", f"Info box {i}", [{"label": "Label", "name": "label", "type": "text"}, {"label": "Main text", "name": "value", "type": "text"}, {"label": "Note", "name": "note", "type": "text"}], instructions=TOKENS if i == 1 else "") for i in range(1, 4)],
    f(g, "tracks_eyebrow", "Tracks: eyebrow"), f(g, "tracks_heading", "Tracks: heading"),
    btn(g, "prog_button", "Button below the tracks"),
], loc_tpl("programme"), menu_order=1)))

g = "speakers_page"
items.append(("field-group", field_group(g, "Speakers page", [
    f(g, "nl_eyebrow", "Sign-up box: eyebrow"), f(g, "nl_heading", "Sign-up box: heading"),
], loc_tpl("speakers"), menu_order=1)))

g = "cfp_page"
items.append(("field-group", field_group(g, "Call for Papers page", [
    f(g, "glance_heading", "Summary box heading"),
    f(g, "guide_heading", "Guidelines heading", instructions="The guidelines list itself is written in the page editor."),
    f(g, "form_heading", "Form heading"),
], loc_tpl("call-for-papers"), menu_order=1)))

g = "awards_page"
items.append(("field-group", field_group(g, "Awards page", [
    f(g, "cat_heading", "Categories box heading"), f(g, "elig_heading", "Eligibility box heading"),
    f(g, "judg_heading", "Judging box heading"), f(g, "form_heading", "Form heading"),
], loc_tpl("awards"), menu_order=1, description="Dates, eligibility, judging and guidelines are in Conference Settings → Awards.")))

g = "partners_page"
items.append(("field-group", field_group(g, "Partners page", [
    f(g, "opp_eyebrow", "Opportunities: eyebrow"), f(g, "opp_heading", "Opportunities: heading"),
    f(g, "conv_heading", "Enquiry section heading", instructions="The text below it is written in the page editor."),
], loc_tpl("partners"), menu_order=1)))

g = "register_page"
items.append(("field-group", field_group(g, "Register page", [
    f(g, "form_heading", "Form heading"),
], loc_tpl("register"), menu_order=1)))

g = "contact_page"
items.append(("field-group", field_group(g, "Contact page", [
    f(g, "box_heading", "Contact box heading", instructions="Leave empty to use the host organisation's name."),
], loc_tpl("contact"), menu_order=1, description="Email, phone and address are in Conference Settings → Contact & social.")))

for i, (kind, payload) in enumerate(items):
    (OUT / f"{i:02d}-{kind}-{payload['key']}.json").write_text(json.dumps(payload, ensure_ascii=False, indent=1))
print(len(items), "payloads")
