# Multi-Step Forms Design

## Goal

Add `Form Step` support so long forms can be split into a polished step-by-step experience with progress, Previous/Next navigation, and current-step validation.

## Builder Model

The existing `form_step` field type acts as a step marker. A form with no step marker renders as a normal single-page form. When at least one marker exists, each marker starts a new step. Fields before the first marker belong to the first step.

## Frontend Behavior

The renderer outputs `form_step` markers with stable `data-bs23-step-marker` attributes. The frontend runtime reads the schema and DOM sequence, groups fields between markers into steps, and adds:

- step progress text,
- step indicator buttons,
- Previous and Next buttons,
- Submit visible only on the final step.

Clicking Next validates only visible fields in the active step. Conditional fields hidden by logic are ignored. Previous never validates.

## Server Behavior

Server validation remains full-form validation on submit. Multi-step navigation is client-side only; no extra database tables or partial entry storage in this milestone.

## Styling

Frontend CSS adds compact progress UI, active step indicators, hidden step state, and responsive navigation. The style should look clean by default and still be easy for Elementor/CSS overrides.

## Testing

JavaScript tests cover grouping by step markers, navigation, current-step validation, and final submit visibility. PHP tests cover marker rendering.
