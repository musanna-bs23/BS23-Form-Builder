function placeholderFor(field) {
  if (field.settings?.placeholder) {
    return field.settings.placeholder;
  }
  return field.label ? `${field.label}${field.required ? '*' : ''}` : '';
}

function PreviewField({ field }) {
  if (field.type === 'container') {
    return (
      <div className="bs23-preview__columns" style={{ '--bs23-preview-columns': field.columns }}>
        {(field.children || []).map((column, index) => (
          <div className="bs23-preview__column" key={`${field.id}-${index}`}>
            {column.map((child) => <PreviewField field={child} key={child.id} />)}
          </div>
        ))}
      </div>
    );
  }

  if (field.type === 'textarea') {
    return (
      <label className="bs23-preview__field bs23-preview__field--wide">
        <span>{field.label}{field.required ? ' *' : ''}</span>
        <textarea placeholder={placeholderFor(field)} rows="4" />
      </label>
    );
  }

  if (['dropdown', 'country', 'radio', 'multiple_choice'].includes(field.type)) {
    return (
      <label className="bs23-preview__field">
        <span>{field.label}{field.required ? ' *' : ''}</span>
        <select defaultValue="">
          <option value="">{placeholderFor(field) || `Select ${field.label}`}</option>
          {(field.settings?.choices || ['Option 1', 'Option 2']).map((choice) => (
            <option key={choice} value={choice}>{choice}</option>
          ))}
        </select>
      </label>
    );
  }

  return (
    <label className="bs23-preview__field">
      <span>{field.type === 'section_break' ? field.label : ''}</span>
      <input placeholder={placeholderFor(field)} type={field.type === 'email' ? 'email' : 'text'} />
    </label>
  );
}

export default function FormPreview({ title, schema }) {
  const fields = schema?.fields || [];

  return (
    <main className="bs23-preview-page">
      <nav className="bs23-preview-tabs">
        <strong>{title}</strong>
        <span>Edit Fields</span>
        <span>Settings & Integrations</span>
        <span>Entries</span>
      </nav>
      <section className="bs23-preview-frame" aria-label="Form preview">
        <header>
          <span className="is-red" />
          <span className="is-yellow" />
          <span className="is-green" />
          <div className="bs23-preview-frame__devices">
            <button className="is-active" type="button">Desktop</button>
            <button type="button">Tablet</button>
            <button type="button">Mobile</button>
          </div>
        </header>
        <div className="bs23-preview-form">
          {fields.length === 0 ? (
            <p>No fields added yet.</p>
          ) : fields.map((field) => <PreviewField field={field} key={field.id} />)}
          <button type="button">Submit</button>
        </div>
      </section>
    </main>
  );
}
