# Helene editor roadmap

Created: 2026-11-02. Status: proposed; implementation has not started.

These editor improvements are independent of the [security roadmap](security-roadmap.md).

## HEL-01: Show unsaved template changes

Status: open.

Show a clear status for the active template indicating whether the editor
contains changes relative to its last successfully saved content.

Acceptance:

- [ ] Opening a template shows it as saved.
- [ ] Editing its content shows unsaved changes immediately.
- [ ] Undoing all changes back to the saved content restores the saved status.
- [ ] A successful save updates the saved baseline; a failed save leaves the
  unsaved status intact. Edits made while a save is in progress remain marked
  unsaved unless they were included in that successful save.
- [ ] Switching between templates shows the correct status for each template.

## HEL-02: Compare SVN base with the current editor content

Status: open.

The local SVN changes view should compare the selected template's SVN BASE
revision with the current content in the editor, including unsaved edits.
The saved working-copy file must not substitute for the editor content.

Acceptance:

- [ ] The comparison includes both saved local changes and current unsaved edits
  relative to SVN BASE.
- [ ] Opening or refreshing the comparison uses the current editor content
  without requiring or triggering a save.
- [ ] Undoing edits and refreshing the comparison updates the displayed diff.
- [ ] Matching SVN BASE produces an empty diff even if the saved working-copy
  file differs from the editor content.
- [ ] The view identifies its baseline and explains when an SVN BASE revision
  is unavailable, such as for a new, unversioned template.
- [ ] Comparing content preserves the editor buffer and stored template bytes,
  including PINP CRLF line endings and the absence of whitespace after a final
  closing PINP tag.

Delivery order: HEL-01 first, then HEL-02. Both should use a consistent notion
of the selected template and its current editor content.
