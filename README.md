# Bulk restore courses (tool_bulkrestore)

A Moodle admin tool that restores **many course backups at once**. Instead of restoring
`.mbz` files one at a time through *Restore*, an administrator uploads several backups (or a
single `.zip` bundling many) and each is restored into a **new course** in the background —
no per-course settings wizard, no manual steps in between.

- **Plugin type:** `admin/tool`
- **Component:** `tool_bulkrestore`
- **Maturity:** ALPHA (`v0.2.0-alpha`)
- **Requires:** Moodle **4.5** or later
- **Author:** bdecent GmbH — <https://bdecent.de>
- **License:** [GNU GPL v3 or later](https://www.gnu.org/copyleft/gpl.html)

---

## Features

- Upload **multiple `.mbz` backup files** at once.
- Upload one or more **`.zip` archives** that each bundle several `.mbz` files — they are
  unpacked automatically and every `.mbz` inside (searched recursively) is restored.
- Each backup is restored into a **brand new course** in a category you choose.
- Restores run **asynchronously** as background tasks — large courses never hit the web
  server's time or memory limits, and you can close the page once everything is queued.
- A **status page** tracks every restore (queued → restoring → done / failed), auto-refreshes
  while work is in progress, and links straight to each newly created course.
- **Per-file isolation:** one backup failing does not affect the others, and the failure
  reason is recorded against that file.

---

## Requirements

- Moodle 4.5+ (tested through Moodle 5.0).
- **Cron must be running.** Restores are queued as ad-hoc tasks and processed by Moodle cron.
  If cron is not running, uploads will sit in the *Queued* state indefinitely.
- The backups must have been created on the **same or an older** Moodle version than this
  site. Moodle refuses to restore a backup made on a *newer* version (see Troubleshooting).

---

## Installation

1. Copy the plugin into your Moodle install so the files live at:

   ```
   admin/tool/bulkrestore/
   ```

   Via git:

   ```bash
   git clone git@github.com:bdecentgmbh/moodle-tool_bulkrestore.git admin/tool/bulkrestore
   ```

2. Log in as an administrator and visit **Site administration → Notifications**, or run:

   ```bash
   php admin/cli/upgrade.php
   ```

   This registers the capability, installs the tracking table, and adds the admin page.

---

## Usage

1. Go to **Site administration → Courses → Bulk restore courses**.
2. Drag in one or more `.mbz` files and/or `.zip` archives.
3. Choose the **target category** for the new courses.
4. Select **Restore**.

Each backup is queued for a background restore. The page returns immediately and shows a
**Recent bulk restores** table. As cron processes the queue, rows move from *Queued* to
*Restoring* to *Done* (with a link to the new course) or *Failed* (with the reason).

To process the queue immediately instead of waiting for scheduled cron:

```bash
php admin/cli/adhoc_task.php --execute
```

---

## How it works

The plugin is deliberately thin glue around Moodle's own backup/restore subsystem.

1. **Upload** — [index.php](index.php) reads the uploaded files from the form's draft area.
   Any `.zip` is unpacked with the core zip packer and every `.mbz` inside is collected
   ([classes/helper.php](classes/helper.php) → `extract_mbz_from_zip()`).
2. **Queue** — for each `.mbz`, `helper::queue_backup()` inserts a tracking row in
   `tool_bulkrestore_task`, copies the backup into the plugin's own file area (keyed on the
   row id), and queues one **`\tool_bulkrestore\task\restore_course_task`** ad-hoc task.
3. **Restore (background)** —
   [classes/task/restore_course_task.php](classes/task/restore_course_task.php) extracts the
   backup, creates a new skeleton course with `restore_dbops::create_new_course()`, then runs
   a core `restore_controller` with `TARGET_NEW_COURSE` (`execute_precheck()` →
   `execute_plan()`). The course name comes from the backup, de-duplicated by core against
   existing course names. On success the row is marked *Done*; on failure the reason is
   recorded and the half-created course is rolled back.

One ad-hoc task per file means failures stay isolated and long restores are spread across
cron runs.

### Data

- Table **`tool_bulkrestore_task`** — one row per queued restore: filename, target category,
  resulting course id, task id, status, detail, owner, timestamps. Used to render the status
  page.
- Uploaded backups are stored transiently in the `tool_bulkrestore / backup` file area and
  deleted once their restore completes successfully.

---

## Permissions

The plugin defines one capability:

| Capability | Context | Default |
| --- | --- | --- |
| `tool/bulkrestore:restore` | System | Manager |

It carries `RISK_DATALOSS` and `RISK_SPAM` (restoring creates courses and content). The admin
page is gated by this capability.

---

## Languages

Ships with **English**, **German** (`de`) and **Spanish** (`es`) language packs.

---

## Troubleshooting

**Everything stays "Queued" and nothing restores.**
Cron is not running. Confirm scheduled/ad-hoc task processing is configured, or run
`php admin/cli/adhoc_task.php --execute` to drain the queue once.

**A backup shows "Failed" with a version message.**
Moodle does not allow restoring a backup created on a *newer* Moodle version than the target
site (for example a 5.2 backup onto a 5.0 site). Restore it on a site of the same or a newer
version, or re-export from a compatible version.

**A restore failed and left an empty placeholder course behind.**
On failure the plugin tries to delete the half-created course. If a third-party
`before_course_deleted` hook on your site throws an error, that deletion can fail and leave an
empty course. The restore is still recorded as *Failed* with the reason; the empty course can
be removed manually once the faulty hook is fixed.

---

## Limitations (ALPHA)

- Backups are always restored into a **new course** (no merge/overwrite into an existing one).
- Restore settings (users, groups, etc.) use the site's **default restore configuration**;
  there is no per-upload settings wizard — that is the point of the hands-off flow.
- `.zip` archives are unpacked during the upload request; extremely large archives could
  exceed the web request limit (the restores themselves are always asynchronous).

---

## Support

Issues and contributions: <https://github.com/bdecentgmbh/moodle-tool_bulkrestore>
