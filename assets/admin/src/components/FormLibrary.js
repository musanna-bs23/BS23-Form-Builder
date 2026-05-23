export default function FormLibrary({ forms, activeFormId, onNewForm, onSelectForm }) {
  return (
    <aside className="bs23-form-library" aria-label="Forms library">
      <header>
        <div>
          <span>Workspace</span>
          <h2>All Forms</h2>
        </div>
        <button type="button" onClick={onNewForm}>New Form</button>
      </header>
      <div className="bs23-form-library__search">
        <input aria-label="Search forms" placeholder="Search forms" type="search" />
      </div>
      <div className="bs23-form-library__list">
        {forms.length === 0 ? (
          <p>No forms yet.</p>
        ) : forms.map((form) => (
          <button
            className={activeFormId === form.id ? 'is-active' : ''}
            key={form.id}
            onClick={() => onSelectForm(form.id)}
            type="button"
          >
            <strong>{form.title}</strong>
            <span>{form.field_count || 0} fields</span>
          </button>
        ))}
      </div>
    </aside>
  );
}
