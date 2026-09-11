---
name: Vectra3D Graduation Workspace
description: Visual system extracted from the local administrative dashboard.
colors:
  ink: "#172f40"
  muted: "#536877"
  nav: "#163747"
  accent: "#096c60"
  accent-light: "#e5f4ef"
  line: "#d4dfe3"
  bg: "#f2f6f7"
  white: "#fff"
  warning: "#855006"
  warning-bg: "#fff2d7"
  danger: "#9b3333"
  button-border: "#b7c8cf"
  button-hover: "#eaf1f3"
  primary-hover: "#07554b"
  field-border: "#76909e"   # 3.36:1 on white; #9eb2bd was 2.20:1 and failed WCAG 1.4.11
  placeholder: "#60727d"
  focus: "#c57c0a"
  nav-text: "#d4e3e9"
  nav-secondary: "#c0d2d9"
  nav-hover: "#224857"
  nav-selected-bg: "#e6f4ef"
  nav-selected-text: "#16483f"
  tag-bg: "#e9eff3"
  tag-text: "#365461"
  good-text: "#165d4f"
  attention-text: "#65430d"
  attention-border: "#e4d1a9"
  table-heading: "#eaf0f3"
typography:
  headline:
    fontFamily: "Segoe UI, Arial, sans-serif"
    fontSize: "32px"
    fontWeight: 650
    lineHeight: 1.2
    letterSpacing: "-.7px"
  title:
    fontFamily: "Segoe UI, Arial, sans-serif"
    fontSize: "20px"
    fontWeight: 700
    lineHeight: 1.3
    letterSpacing: "-.2px"
  body:
    fontFamily: "Segoe UI, Arial, sans-serif"
    fontSize: "15px"
    fontWeight: 400
    lineHeight: 1.5
  label:
    fontFamily: "Segoe UI, Arial, sans-serif"
    fontSize: "14px"
    fontWeight: 600
  metadata:
    fontFamily: "Segoe UI, Arial, sans-serif"
    fontSize: "12px"
  code:
    fontFamily: "Consolas, monospace"
    fontSize: "12px"
rounded:
  tag: "3px"
  field: "4px"
  control: "5px"
  panel: "6px"
  feature: "7px"
spacing:
  label-gap: "6px"
  action-gap: "10px"
  form-gap: "16px"
  mobile-gutter: "18px"
  mobile-panel: "20px"
  panel: "24px"
  wide-panel: "28px"
  desktop-gutter: "40px"
components:
  button-primary:
    backgroundColor: "{colors.accent}"
    textColor: "{colors.white}"
    rounded: "{rounded.control}"
    padding: "9px 14px"
  button-primary-hover:
    backgroundColor: "{colors.primary-hover}"
  button-secondary:
    backgroundColor: "{colors.white}"
    textColor: "{colors.ink}"
    rounded: "{rounded.control}"
    padding: "9px 14px"
  button-secondary-hover:
    backgroundColor: "{colors.button-hover}"
  field:
    backgroundColor: "{colors.white}"
    textColor: "{colors.ink}"
    rounded: "{rounded.field}"
    padding: "9px 10px"
  panel:
    backgroundColor: "{colors.white}"
    rounded: "{rounded.panel}"
    padding: "24px"
  tag:
    backgroundColor: "{colors.tag-bg}"
    textColor: "{colors.tag-text}"
    rounded: "{rounded.tag}"
    padding: "4px 7px"
  nav-selected:
    backgroundColor: "{colors.nav-selected-bg}"
    textColor: "{colors.nav-selected-text}"
    rounded: "{rounded.control}"
    padding: "10px 13px"
---

# Design System: Vectra3D Graduation Workspace

## Overview

This document records the implemented dashboard in Dashboard/style.css, Dashboard/index.html and Dashboard/app.js. It describes an internal Operate surface built with native HTML controls and local system fonts. No additional visual metaphor or user preference is inferred.

The implementation uses deep blue navigation, a cool light background, white content surfaces, teal actions and fine separating borders. Lists, tables and editable fields provide most of the interface. PRODUCT.md supplies product boundaries; SURFACE.md records the page composition separately.

## Colors

### Primary

Accent identifies links, primary buttons, caret colour and progress accents. Accent-light supports positive tags. Primary-hover darkens the primary button on hover.

### Neutral

Ink is default text; muted is supporting text. Nav is the sidebar, current-week strip and notification background. Bg sits behind white panels. Line separates panels, rows and page regions. Navigation has distinct default, secondary, hover and selected colours. Fields use field-border and placeholder; secondary buttons use button-border and button-hover.

Warning and warning-bg mark attention states; danger identifies overdue or problematic values. Positive tags use good-text. Table-heading separates column labels from rows. The amber focus token is a keyboard outline, independent of these status roles.

## Typography

Segoe UI falls back to Arial and sans-serif; no font download is required. Headline is the page heading; title is the section heading; body supplies controls and paragraphs. Labels use a smaller semibold role, and metadata uses the smallest shared text size. Code references use Consolas with a monospace fallback.

Third-level headings are 16px. Table body text is 14px; column labels are 12px and weight 600. Tags use 11px with line-height 1.4. Dates and supporting list text commonly use 13px. Week numbers use 36px with line-height 1.1. Numeric week and hours values use tabular figures. Body paragraphs are capped at 74ch; page leads at 76ch.

At the mobile breakpoint, page headings become 27px, leads 14px, and selected section headings 18px. These are observed responsive overrides rather than a separate type family.

## Layout

Desktop navigation is fixed at 244px wide. The content shell offsets by the same amount. The header is 78px tall. Main content has a 1450px maximum width and 34px top, 40px side and 60px bottom padding. Two-column content uses minmax(0, 1.45fr) and minmax(280px, 1fr), with a 24px gap. Panels repeat that gap and padding. The current-week strip uses 90px, flexible and automatic columns with a 24px gap.

At 1150px and below, navigation narrows to 210px, main padding becomes 28px, content columns stack, and the week action moves beneath its text. At 720px and below, navigation becomes an in-flow horizontally scrolling row, the shell loses its offset, the header is 65px tall, main padding becomes 25px 18px 40px, panels use 20px padding, and forms and deliverables stack. Tables retain horizontal scrolling within their bordered container. At 1600px and above, main top padding is 45px and panel padding is 28px.

Print hides navigation, header, footer, buttons, toolbars and notices; removes the shell offset; stacks columns; and uses a white background with dark links. Content containers avoid internal page breaks where possible.

## Elevation & Depth

Panels and tables use borders and background contrast without shadows. The fixed notification alone uses the shadow recorded in the sidecar. Restore uses a native modal dialog with a translucent backdrop. No authored animation or transition declarations are present.

## Shapes

The radius roles progress from small tags through fields, controls, panels and feature containers. Borders are generally 1px. Lists and tables clip content at rounded outer boundaries. The inline Vectra3D mark is an angular two-path SVG; it does not require an icon font. The interface does not use a general illustration system.

## Components

Administrative reliability updates (10 September 2026): a persistent warning below the top bar exposes failed or conflicting saves and offers export. It uses the existing attention colours and button style. The timeline includes a milestone table showing edited and original dates. Deliverables and weekly review show evidence-gap counts. Mobile input, select and textarea text is 16px to maintain readable editing; long administrative references wrap within their containers.

Buttons use weight 600, thin borders and the control radius. Primary buttons have teal fill and white text; secondary buttons have white fill and ink text. Small actions use 12px text and 6px 10px padding. Disabled buttons have opacity .6 and a not-allowed cursor. All focus-visible elements receive a 3px amber outline with 3px offset. No custom active-state animation is defined.

Inputs, selects and textareas use the field radius and a visible border. Labels sit above fields with the label-gap spacing. Textareas resize vertically, with an 86px minimum height; weekly review uses 280px. Native number, date and select controls retain browser behaviour.

Navigation links use 14px type and the control radius. Hover and aria-current selection receive distinct fills; selected links use weight 650. A keyboard skip link becomes visible on focus. Route changes update aria-current and focus the main content region.

Tags use compact padding and three observed treatments: neutral, warning and positive. Warning notices add an attention border and 15px 18px padding. Status wording accompanies colour.

Panels use a white fill and line border. Tables use 13px 16px column-heading padding and 15px 16px cell padding. Completed task titles are struck through and muted. Task details use native disclosure elements. Deliverable rows pair descriptive content with editable status and evidence fields.

The week strip uses the navigation fill, a separated large week number and a white secondary action. Notifications occupy the bottom-right corner, capped at min(420px, 90vw), and announce through a live status region. The restore dialog uses 28px padding, 90vw width and a 470px maximum width.

## Do's and Don'ts

- Do reuse the extracted colours, type roles and radius vocabulary when extending this dashboard.
- Do retain visible labels, keyboard focus, current navigation state and status wording.
- Do preserve the mobile stacking and horizontally contained table overflow.
- Don't communicate task completion only through colour; the existing system includes status text and a struck-through title.
- Don't add shadows to ordinary panels as if they were an existing convention; the implemented shadow is specific to notifications.
- Don't treat this internal dashboard's visual system as an approved identity for the separate public project webpage.
