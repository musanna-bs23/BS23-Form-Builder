export default function FormBreakdown({ forms }) {
  const max = Math.max(1, ...forms.map((form) => form.total));

  return (
    <article className="bs23-panel">
      <header>
        <h2>Entries by form</h2>
        <span>Top active forms</span>
      </header>
      <div className="bs23-breakdown">
        {forms.length === 0 && <p>No entries yet.</p>}
        {forms.map((form) => (
          <div className="bs23-breakdown__row" key={form.form_id}>
            <div>
              <strong>{form.form_title}</strong>
              <span>{form.total} entries</span>
            </div>
            <em><i style={{ width: `${(form.total / max) * 100}%` }} /></em>
          </div>
        ))}
      </div>
    </article>
  );
}
