# Frontend Live Runtime Design

## Goal

Make rendered forms feel premium by adding live conditional logic and client-side validation while keeping existing PHP validation as the source of truth.

## Behavior

- Forms include a compact JSON schema payload in a safe script tag.
- A frontend runtime initializes every `.bs23-form[data-bs23-form-id]`.
- Conditional fields hide/show as users change input values.
- Hidden fields are disabled so browser required validation does not block submission.
- Visible fields validate on blur/change and again on submit.
- Client-side errors render next to fields with the existing `bs23-form__error` class.

## Scope

The runtime supports the rules already implemented server-side for the common browser cases: required, email, url, min/max length, numeric min/max, regex/not_regex, in/not_in, starts_with/ends_with, alpha variants, digits, file extension, file size, and the visual validation settings.

Server validation remains authoritative for filter-backed rules and complex server-only rules.

## Assets

Add a vanilla JavaScript frontend bundle under `assets/frontend/build/index.js`, built by `@wordpress/scripts`. The shortcode enqueues this script along with existing frontend CSS.
