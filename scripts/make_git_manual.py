"""
Generates a Git manual as a .docx file using ONLY the Python standard library.
A .docx is an Office Open XML package (a zip containing XML parts), so we build
the minimal set of parts by hand. No third-party packages required.
"""

import os
import zipfile
from xml.sax.saxutils import escape

# Always write to the project root (the parent of this scripts/ folder),
# regardless of the current working directory.
_ROOT = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
OUT = os.path.join(_ROOT, "Git_Manual_Hotel_Booking_Ops.docx")

W = "http://schemas.openxmlformats.org/wordprocessingml/2006/main"


# Shared palette
INK = "1F3864"       # deep navy for titles
ACCENT = "2E74B5"    # medium blue for headings / rules
MUTED = "595959"     # soft grey for secondary text
CODE_FILL = "F4F6F8" # very light blue-grey for code boxes
CODE_TEXT = "24292E" # near-black for code
NOTE_FILL = "EAF1FB" # light blue tint for info callout
WARN_FILL = "FCE9E9" # light red tint for caution callout


def run(text, *, bold=False, mono=False, color=None, italic=False, size=None):
    rpr = []
    if bold:
        rpr.append("<w:b/>")
    if italic:
        rpr.append("<w:i/>")
    if mono:
        rpr.append('<w:rFonts w:ascii="Consolas" w:hAnsi="Consolas" w:cs="Consolas"/>')
        rpr.append('<w:sz w:val="20"/>')
        if not color:
            color = CODE_TEXT
    if size:
        rpr.append(f'<w:sz w:val="{size}"/>')
    if color:
        rpr.append(f'<w:color w:val="{color}"/>')
    rpr_xml = f"<w:rPr>{''.join(rpr)}</w:rPr>" if rpr else ""
    return f'<w:r>{rpr_xml}<w:t xml:space="preserve">{escape(text)}</w:t></w:r>'


def br():
    return "<w:r><w:br/></w:r>"


def para(runs="", style=None, *, spacing_after=160, spacing_before=0, line=276):
    ppr = []
    if style:
        ppr.append(f'<w:pStyle w:val="{style}"/>')
    ppr.append(
        f'<w:spacing w:before="{spacing_before}" w:after="{spacing_after}" '
        f'w:line="{line}" w:lineRule="auto"/>'
    )
    ppr_xml = f"<w:pPr>{''.join(ppr)}</w:pPr>"
    if isinstance(runs, str):
        # Already-built run XML (e.g. from run()/heading()) is passed through
        # as-is; plain text gets wrapped in a single run.
        if runs and not runs.startswith("<w:"):
            runs = run(runs)
    return f"<w:p>{ppr_xml}{runs}</w:p>"


def heading(text, level=1):
    # Accent rule under each section heading for visual separation.
    bdr = (
        '<w:pBdr><w:bottom w:val="single" w:sz="6" w:space="4" '
        f'w:color="{ACCENT}"/></w:pBdr>'
    )
    ppr = (
        f'<w:pPr><w:pStyle w:val="Heading{level}"/>{bdr}'
        '<w:spacing w:before="360" w:after="140" w:line="276" w:lineRule="auto"/>'
        '</w:pPr>'
    )
    return f"<w:p>{ppr}{run(text, bold=True)}</w:p>"


def title(text):
    return para(run(text, bold=True), style="Title", spacing_after=60)


def subtitle(text):
    return para(run(text, italic=True, color=MUTED, size=24), spacing_after=240)


def bullet(text):
    body = "".join(text) if isinstance(text, list) else run(text)
    return (
        '<w:p><w:pPr><w:pStyle w:val="ListParagraph"/>'
        '<w:numPr><w:ilvl w:val="0"/><w:numId w:val="1"/></w:numPr>'
        '<w:spacing w:after="90" w:line="276" w:lineRule="auto"/></w:pPr>'
        f"{body}</w:p>"
    )


def callout(runs, *, fill, accent):
    """A padded, shaded, left-accented box for notes and warnings."""
    body = "".join(runs) if isinstance(runs, list) else (
        runs if runs.startswith("<w:") else run(runs)
    )
    ppr = (
        "<w:pPr>"
        '<w:pBdr>'
        f'<w:top w:val="single" w:sz="4" w:space="6" w:color="{fill}"/>'
        f'<w:left w:val="single" w:sz="24" w:space="8" w:color="{accent}"/>'
        f'<w:bottom w:val="single" w:sz="4" w:space="6" w:color="{fill}"/>'
        f'<w:right w:val="single" w:sz="4" w:space="6" w:color="{fill}"/>'
        '</w:pBdr>'
        f'<w:shd w:val="clear" w:color="auto" w:fill="{fill}"/>'
        '<w:spacing w:before="120" w:after="200" w:line="276" w:lineRule="auto"/>'
        '<w:ind w:left="144" w:right="144"/>'
        "</w:pPr>"
    )
    return f"<w:p>{ppr}{body}</w:p>"


def code_block(lines):
    """A single bordered, padded box with each command on its own line."""
    out = []
    n = len(lines)
    for i, line in enumerate(lines):
        shd = f'<w:shd w:val="clear" w:color="auto" w:fill="{CODE_FILL}"/>'
        # Build a continuous border: top on first line, bottom on last,
        # sides on every line so it reads as one box.
        borders = ['<w:pBdr>']
        if i == 0:
            borders.append('<w:top w:val="single" w:sz="4" w:space="6" w:color="D5DCE4"/>')
        borders.append('<w:left w:val="single" w:sz="4" w:space="8" w:color="D5DCE4"/>')
        borders.append('<w:right w:val="single" w:sz="4" w:space="8" w:color="D5DCE4"/>')
        if i == n - 1:
            borders.append('<w:bottom w:val="single" w:sz="4" w:space="6" w:color="D5DCE4"/>')
        borders.append('</w:pBdr>')
        before = "120" if i == 0 else "0"
        after = "120" if i == n - 1 else "0"
        ppr = (
            f"<w:pPr>{''.join(borders)}"
            f"<w:spacing w:before='{before}' w:after='{after}' w:line='264' w:lineRule='auto'/>"
            f'<w:ind w:left="144" w:right="144"/>'
            f"{shd}</w:pPr>"
        )
        out.append(f"<w:p>{ppr}{run(line, mono=True)}</w:p>")
    # A small spacer paragraph after the box so following text isn't cramped.
    return "".join(out)


body_parts = []
B = body_parts.append

B(title("Git Manual"))
B(subtitle("Everyday workflow for keeping your local folder in sync with GitHub"))
B(callout(
    [
        run("Project:  ", bold=True), run("Hotel Booking Ops"), br(),
        run("Remote:  ", bold=True), run("https://github.com/MamSanora/hotel-booking-ops.git"),
    ],
    fill=NOTE_FILL, accent=ACCENT,
))

# 1
B(heading("1. One-Time Setup (already done for this project)", 1))
B(para("Your folder is already connected to GitHub. You only need these commands when starting a brand-new project or on a new computer."))
B(para(run("Tell Git who you are (first time on a machine):", bold=True)))
B(code_block([
    'git config --global user.name "MamSanora"',
    'git config --global user.email "you@example.com"',
]))
B(para(run("Connect a folder to a GitHub repo (skip if already connected):", bold=True)))
B(code_block([
    "git init",
    "git remote add origin https://github.com/MamSanora/hotel-booking-ops.git",
]))
B(para(run("Check what remote you are connected to:", bold=True)))
B(code_block(["git remote -v"]))

# 2
B(heading("2. The Daily Workflow (the 4 commands you use most)", 1))
B(para("Whenever you make changes, repeat this cycle:"))
B(bullet([run("1. See what changed: ", bold=True), run("git status", mono=True)]))
B(bullet([run("2. Stage your changes: ", bold=True), run("git add .", mono=True)]))
B(bullet([run("3. Save a snapshot with a message: ", bold=True), run('git commit -m "message"', mono=True)]))
B(bullet([run("4. Upload to GitHub: ", bold=True), run("git push", mono=True)]))
B(para(run("Full example:", bold=True)))
B(code_block([
    "git status",
    "git add .",
    'git commit -m "Remove unused Jetstream views to fix optimize"',
    "git push",
]))

# 3
B(heading("3. Checking Status & History", 1))
B(code_block([
    "git status                 # what is changed / staged",
    "git log --oneline          # short commit history",
    "git log --oneline -10      # last 10 commits",
    "git diff                   # see unstaged changes",
    "git diff --staged          # see staged changes",
]))

# 4
B(heading("4. Staging Changes", 1))
B(code_block([
    "git add .                  # stage everything",
    "git add path/to/file.php   # stage one file",
    "git restore --staged file  # unstage a file (keep changes)",
]))

# 5
B(heading("5. Committing", 1))
B(para("A commit is a saved snapshot. Always write a short, clear message describing WHY."))
B(code_block([
    'git commit -m "Fix payment callback validation"',
    'git commit -am "Quick fix"     # add + commit tracked files in one step',
]))

# 6
B(heading("6. Pushing & Pulling (syncing with GitHub)", 1))
B(code_block([
    "git push                   # upload your commits to GitHub",
    "git push -u origin master  # first push of a new branch",
    "git pull                   # download + merge others' changes",
    "git fetch                  # download changes without merging",
]))

# 7
B(heading("7. Branches", 1))
B(code_block([
    "git branch                 # list branches",
    "git checkout -b feature-x  # create + switch to a new branch",
    "git checkout master        # switch back to master",
    "git merge feature-x        # merge feature-x into current branch",
    "git branch -d feature-x    # delete a merged branch",
]))

# 8
B(heading("8. Undoing Mistakes", 1))
B(code_block([
    "git restore file.php             # discard changes in a file",
    "git restore --staged file.php    # unstage (keep edits)",
    "git revert <commit>              # safely undo a pushed commit",
    "git reset --soft HEAD~1          # undo last commit, keep changes staged",
]))
B(callout(
    [
        run("Caution:  ", bold=True, color="C00000"),
        run('Avoid "git reset --hard" and "git push --force" unless you are sure '
            "— they can permanently delete work."),
    ],
    fill=WARN_FILL, accent="C00000",
))

# 9
B(heading("9. Recommended Workflow for This Project", 1))
B(bullet("Pull before you start working so you have the latest code."))
B(bullet("Make small, focused changes."))
B(bullet("Commit often with clear messages."))
B(bullet("Push at the end of a work session."))
B(code_block([
    "git pull",
    "# ... make your changes ...",
    "git add .",
    'git commit -m "Describe what you changed"',
    "git push",
]))

# 10
B(heading("10. Quick Cheat Sheet", 1))
B(bullet([run("git status", mono=True), run("  — what changed")]))
B(bullet([run("git add .", mono=True), run("  — stage everything")]))
B(bullet([run('git commit -m "msg"', mono=True), run("  — save a snapshot")]))
B(bullet([run("git push", mono=True), run("  — upload to GitHub")]))
B(bullet([run("git pull", mono=True), run("  — download latest")]))
B(bullet([run("git log --oneline", mono=True), run("  — view history")]))

document_xml = (
    '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
    f'<w:document xmlns:w="{W}">'
    f"<w:body>{''.join(body_parts)}"
    '<w:sectPr><w:pgSz w:w="12240" w:h="15840"/>'
    '<w:pgMar w:top="1440" w:right="1440" w:bottom="1440" w:left="1440" '
    'w:header="720" w:footer="720" w:gutter="0"/></w:sectPr>'
    "</w:body></w:document>"
)

styles_xml = (
    '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
    f'<w:styles xmlns:w="{W}">'
    '<w:docDefaults><w:rPrDefault><w:rPr>'
    '<w:rFonts w:ascii="Calibri" w:hAnsi="Calibri"/><w:sz w:val="22"/>'
    f'<w:color w:val="333333"/>'
    '</w:rPr></w:rPrDefault>'
    '<w:pPrDefault><w:pPr>'
    '<w:spacing w:after="160" w:line="276" w:lineRule="auto"/>'
    '</w:pPr></w:pPrDefault></w:docDefaults>'
    '<w:style w:type="paragraph" w:default="1" w:styleId="Normal"><w:name w:val="Normal"/></w:style>'
    '<w:style w:type="paragraph" w:styleId="Title"><w:name w:val="Title"/>'
    f'<w:qFormat/><w:rPr><w:b/><w:color w:val="{INK}"/><w:sz w:val="60"/></w:rPr></w:style>'
    '<w:style w:type="paragraph" w:styleId="Heading1"><w:name w:val="heading 1"/>'
    '<w:basedOn w:val="Normal"/><w:next w:val="Normal"/><w:qFormat/>'
    f'<w:rPr><w:b/><w:color w:val="{INK}"/><w:sz w:val="30"/></w:rPr></w:style>'
    '<w:style w:type="paragraph" w:styleId="Heading2"><w:name w:val="heading 2"/>'
    '<w:basedOn w:val="Normal"/><w:next w:val="Normal"/><w:qFormat/>'
    '<w:rPr><w:b/><w:color w:val="2E74B5"/><w:sz w:val="26"/></w:rPr></w:style>'
    '<w:style w:type="paragraph" w:styleId="ListParagraph"><w:name w:val="List Paragraph"/></w:style>'
    "</w:styles>"
)

numbering_xml = (
    '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
    f'<w:numbering xmlns:w="{W}">'
    '<w:abstractNum w:abstractNumId="0"><w:lvl w:ilvl="0">'
    '<w:start w:val="1"/><w:numFmt w:val="bullet"/><w:lvlText w:val="\u2022"/>'
    '<w:lvlJc w:val="left"/><w:pPr><w:ind w:left="720" w:hanging="360"/></w:pPr>'
    '<w:rPr><w:rFonts w:ascii="Symbol" w:hAnsi="Symbol"/></w:rPr>'
    "</w:lvl></w:abstractNum>"
    '<w:num w:numId="1"><w:abstractNumId w:val="0"/></w:num>'
    "</w:numbering>"
)

content_types = (
    '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
    '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
    '<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
    '<Default Extension="xml" ContentType="application/xml"/>'
    '<Override PartName="/word/document.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.document.main+xml"/>'
    '<Override PartName="/word/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.styles+xml"/>'
    '<Override PartName="/word/numbering.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.numbering+xml"/>'
    "</Types>"
)

root_rels = (
    '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
    '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
    '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="word/document.xml"/>'
    "</Relationships>"
)

doc_rels = (
    '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
    '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
    '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>'
    '<Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/numbering" Target="numbering.xml"/>'
    "</Relationships>"
)

with zipfile.ZipFile(OUT, "w", zipfile.ZIP_DEFLATED) as z:
    z.writestr("[Content_Types].xml", content_types)
    z.writestr("_rels/.rels", root_rels)
    z.writestr("word/document.xml", document_xml)
    z.writestr("word/styles.xml", styles_xml)
    z.writestr("word/numbering.xml", numbering_xml)
    z.writestr("word/_rels/document.xml.rels", doc_rels)

print(f"Created {OUT}")
