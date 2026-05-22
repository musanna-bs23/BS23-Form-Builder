export default function EntryDrawer({ entry, onClose, onDelete }) {
  if (!entry) {
    return null;
  }

  return (
    <aside className="bs23-drawer" aria-label="Entry details">
      <div className="bs23-drawer__panel">
        <header>
          <div>
            <span>Entry #{entry.id}</span>
            <h2>{entry.form_title}</h2>
          </div>
          <button type="button" onClick={onClose}>Close</button>
        </header>
        <dl className="bs23-drawer__meta">
          <dt>Submitted</dt><dd>{entry.created_at}</dd>
          <dt>User ID</dt><dd>{entry.user_id || 'Guest'}</dd>
          <dt>IP</dt><dd>{entry.user_ip || '-'}</dd>
          <dt>User agent</dt><dd>{entry.user_agent || '-'}</dd>
        </dl>
        <div className="bs23-drawer__fields">
          {Object.entries(entry.entry_data || {}).map(([key, value]) => (
            <div key={key}>
              <span>{key}</span>
              <strong>{Array.isArray(value) ? value.join(', ') : String(value)}</strong>
            </div>
          ))}
        </div>
        <footer>
          <button type="button" className="is-danger" onClick={() => onDelete(entry.id)}>Delete entry</button>
        </footer>
      </div>
    </aside>
  );
}
