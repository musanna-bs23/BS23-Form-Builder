const choiceTypes = ['dropdown', 'radio', 'checkbox', 'multiple_choice'];

export default function FieldSettingsPanel({ field, onUpdate, onUpdateSettings, onDelete, onDuplicate, onMove }) {
  if (!field) {
    return (
      <aside className="bs23-field-settings">
        <h2>Field Settings</h2>
        <p>Select a field from the canvas to edit it.</p>
      </aside>
    );
  }

  const settings = field.settings || {};
  const setField = (key, value) => onUpdate(field.id, { [key]: value });
  const setSetting = (key, value) => onUpdateSettings(field.id, { [key]: value });

  return (
    <aside className="bs23-field-settings">
      <header>
        <div>
          <span>{field.type}</span>
          <h2>Field Settings</h2>
        </div>
      </header>

      <label>Label
        <input value={field.label || ''} onChange={(event) => setField('label', event.target.value)} />
      </label>
      <label>Name / Key
        <input value={field.name || ''} onChange={(event) => setField('name', event.target.value)} />
      </label>
      {field.type !== 'container' && field.type !== 'section_break' && field.type !== 'html' && (
        <>
          <label>Placeholder
            <input value={settings.placeholder || ''} onChange={(event) => setSetting('placeholder', event.target.value)} />
          </label>
          <label>Default value
            <input value={settings.default || ''} onChange={(event) => setSetting('default', event.target.value)} />
          </label>
          <label>Help text
            <textarea rows="2" value={settings.help || ''} onChange={(event) => setSetting('help', event.target.value)} />
          </label>
          <label>CSS class
            <input value={settings.className || ''} onChange={(event) => setSetting('className', event.target.value)} />
          </label>
          <label className="bs23-field-settings__toggle">
            <input type="checkbox" checked={!!field.required} onChange={(event) => setField('required', event.target.checked)} />
            Required
          </label>
        </>
      )}

      {choiceTypes.includes(field.type) && (
        <label>Choices
          <textarea
            rows="5"
            value={(settings.choices || []).join('\n')}
            onChange={(event) => setSetting('choices', event.target.value.split(/\r?\n/).filter(Boolean))}
          />
        </label>
      )}

      {field.type === 'html' && (
        <label>HTML content
          <textarea rows="6" value={settings.content || ''} onChange={(event) => setSetting('content', event.target.value)} />
        </label>
      )}

      {field.type === 'section_break' && (
        <label>Description
          <textarea rows="3" value={settings.description || ''} onChange={(event) => setSetting('description', event.target.value)} />
        </label>
      )}

      <footer>
        <button type="button" onClick={() => onMove(field.id, 'up')}>Up</button>
        <button type="button" onClick={() => onMove(field.id, 'down')}>Down</button>
        <button type="button" onClick={() => onDuplicate(field.id)}>Duplicate</button>
        <button type="button" className="is-danger" onClick={() => onDelete(field.id)}>Delete</button>
      </footer>
    </aside>
  );
}
