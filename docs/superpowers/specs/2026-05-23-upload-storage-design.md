# Upload Storage Design

## Goal

Make `file_upload` and `image_upload` real production fields: render upload controls, validate files server-side, store them safely in WordPress uploads, save file metadata in entries, show file links/previews in the entries UI, and export useful file data in CSV.

## Server Flow

Submission handling merges `$_POST` and `$_FILES` by field name. The validator checks upload rules already supported by advanced validation: required, file/image, extensions, mimes/mimetypes, min/max file size, and visual validation settings.

After validation, an upload storage service moves accepted files into `wp-content/uploads/bs23-form-builder/{form_id}/`. Stored entry values are metadata arrays:

```json
{
  "name": "resume.pdf",
  "url": "https://example.com/wp-content/uploads/bs23-form-builder/12/resume.pdf",
  "path": "/server/path/resume.pdf",
  "type": "application/pdf",
  "size": 123456,
  "attachment_id": 123
}
```

Attachment creation is best-effort: if WordPress media functions are available, create an attachment; if not, the file is still stored and linked.

## Frontend

Renderer outputs `enctype="multipart/form-data"` when the schema contains upload fields. Upload fields render as `<input type="file">` with `accept` attributes derived from allowed extensions where possible.

## Entries And Export

Entries drawer renders file metadata as clickable links and image thumbnails for image uploads. CSV export converts file metadata to the file URL, with filename included when available.

## Security

The server sanitizes filenames, checks extension and MIME metadata, enforces size limits, and does not store raw temp names. Server validation remains authoritative even if browser checks are bypassed.
