export default function EntriesTable({ entries, loading, selected, onSelect, onOpen, pagination }) {
  const toggle = (id) => {
    onSelect(selected.includes(id) ? selected.filter((item) => item !== id) : [...selected, id]);
  };

  if (loading) {
    return <div className="bs23-table-state">Loading entries...</div>;
  }

  if (entries.length === 0) {
    return <div className="bs23-table-state">No entries match these filters.</div>;
  }

  return (
    <div className="bs23-table-wrap">
      <table className="bs23-table">
        <thead>
          <tr>
            <th>Select</th>
            <th>Entry</th>
            <th>Form</th>
            <th>Preview</th>
            <th>Submitted</th>
          </tr>
        </thead>
        <tbody>
          {entries.map((entry) => (
            <tr key={entry.id}>
              <td><input type="checkbox" checked={selected.includes(entry.id)} onChange={() => toggle(entry.id)} /></td>
              <td><button type="button" onClick={() => onOpen(entry.id)}>#{entry.id}</button></td>
              <td>{entry.form_title}</td>
              <td className="bs23-table__preview">{preview(entry.entry_data)}</td>
              <td>{entry.created_at}</td>
            </tr>
          ))}
        </tbody>
      </table>
      <footer className="bs23-table__footer">
        {pagination.total} entries, page {pagination.page} of {pagination.total_pages}
      </footer>
    </div>
  );
}

function preview(data) {
  return Object.entries(data || {}).slice(0, 3).map(([, value]) => (
    Array.isArray(value) ? value.join(', ') : value
  )).join(' · ');
}
