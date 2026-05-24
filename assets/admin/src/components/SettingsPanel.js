export default function SettingsPanel({ formId, mode = 'all', settings, status, onChange, onSave, onTest }) {
  const notification = settings.notification || {};
  const confirmation = settings.confirmation;
  const security = settings.security || {};
  const style = settings.style || {};
  const emailEnabled = notification.enabled !== false;

  const updateNotification = (key, value) => onChange({
    ...settings,
    notification: { ...notification, [key]: value },
  });
  const updateConfirmation = (key, value) => onChange({
    ...settings,
    confirmation: { ...confirmation, [key]: value },
  });
  const updateStyle = (key, value) => onChange({
    ...settings,
    style: { ...style, [key]: value },
  });
  const updateSecurity = (key, value) => onChange({
    ...settings,
    security: { ...security, [key]: value },
  });

  const showForm = mode === 'all' || mode === 'form';
  const showEmail = mode === 'all' || mode === 'email';
  const showStyle = mode === 'all' || mode === 'style';
  const showSecurity = mode === 'all' || mode === 'security';
  const showActions = mode === 'all' || mode === 'email';

  return (
    <aside className={`bs23-settings-panel bs23-settings-panel--${mode}`}>
      <header>
        <h2>Form Settings</h2>
        <span>{status}</span>
      </header>
      {showEmail && (
      <section>
        <h3>Email notification</h3>
        <label className="bs23-settings-panel__toggle bs23-settings-panel__toggle--primary">
          <input
            type="checkbox"
            checked={emailEnabled}
            onChange={(event) => updateNotification('enabled', event.target.checked)}
          />
          Send email notifications
        </label>
        <label>Send to
          <input disabled={!emailEnabled} value={notification.to || ''} onChange={(event) => updateNotification('to', event.target.value)} />
        </label>
        <label>Subject
          <input disabled={!emailEnabled} value={notification.subject || ''} onChange={(event) => updateNotification('subject', event.target.value)} />
        </label>
        <label>Message
          <textarea disabled={!emailEnabled} rows="5" value={notification.message || ''} onChange={(event) => updateNotification('message', event.target.value)} />
        </label>
        <label>Reply-to field
          <input disabled={!emailEnabled} value={notification.reply_to || ''} placeholder="email" onChange={(event) => updateNotification('reply_to', event.target.value)} />
        </label>
      </section>
      )}
      {showForm && (
      <section>
        <h3>Confirmation</h3>
        <label>Success message
          <textarea rows="3" value={confirmation.message} onChange={(event) => updateConfirmation('message', event.target.value)} />
        </label>
        <label>Redirect URL
          <input value={confirmation.redirect_url} onChange={(event) => updateConfirmation('redirect_url', event.target.value)} />
        </label>
      </section>
      )}
      {showStyle && (
      <section>
        <h3>Style</h3>
        <label>Form width
          <input value={style.max_width || ''} onChange={(event) => updateStyle('max_width', event.target.value)} />
        </label>
        <label>Field gap
          <input value={style.field_gap || ''} onChange={(event) => updateStyle('field_gap', event.target.value)} />
        </label>
        <label>Label color
          <input type="color" value={style.label_color || '#0f172a'} onChange={(event) => updateStyle('label_color', event.target.value)} />
        </label>
        <label>Label size
          <input value={style.label_size || ''} onChange={(event) => updateStyle('label_size', event.target.value)} />
        </label>
        <label>Input background
          <input type="color" value={style.input_background || '#ffffff'} onChange={(event) => updateStyle('input_background', event.target.value)} />
        </label>
        <label>Input border
          <input type="color" value={style.input_border || '#cbd5e1'} onChange={(event) => updateStyle('input_border', event.target.value)} />
        </label>
        <label>Input radius
          <input value={style.input_radius || ''} onChange={(event) => updateStyle('input_radius', event.target.value)} />
        </label>
        <label>Button background
          <input type="color" value={style.button_background || '#2563eb'} onChange={(event) => updateStyle('button_background', event.target.value)} />
        </label>
        <label>Button text
          <input type="color" value={style.button_text || '#ffffff'} onChange={(event) => updateStyle('button_text', event.target.value)} />
        </label>
        <label>Button radius
          <input value={style.button_radius || ''} onChange={(event) => updateStyle('button_radius', event.target.value)} />
        </label>
        <label>Error color
          <input type="color" value={style.error_color || '#b42318'} onChange={(event) => updateStyle('error_color', event.target.value)} />
        </label>
        <label>Success color
          <input type="color" value={style.success_color || '#027a48'} onChange={(event) => updateStyle('success_color', event.target.value)} />
        </label>
        <label>Step active
          <input type="color" value={style.step_active || '#2563eb'} onChange={(event) => updateStyle('step_active', event.target.value)} />
        </label>
      </section>
      )}
      {showSecurity && (
      <section>
        <h3>Security</h3>
        <label className="bs23-settings-panel__toggle">
          <input
            type="checkbox"
            checked={security.enabled !== false}
            onChange={(event) => updateSecurity('enabled', event.target.checked)}
          />
          Enable anti-spam
        </label>
        <label className="bs23-settings-panel__toggle">
          <input
            type="checkbox"
            checked={security.honeypot !== false}
            onChange={(event) => updateSecurity('honeypot', event.target.checked)}
          />
          Honeypot field
        </label>
        <label>Minimum submit time
          <input type="number" min="1" max="30" value={security.minimum_time || 3} onChange={(event) => updateSecurity('minimum_time', event.target.value)} />
        </label>
        <label>Rate limit count
          <input type="number" min="1" max="100" value={security.rate_limit_count || 5} onChange={(event) => updateSecurity('rate_limit_count', event.target.value)} />
        </label>
        <label>Rate limit window
          <input type="number" min="60" max="3600" value={security.rate_limit_window || 300} onChange={(event) => updateSecurity('rate_limit_window', event.target.value)} />
        </label>
      </section>
      )}
      {showActions && (
      <footer>
        <button type="button" className="button button-primary" disabled={!formId} onClick={onSave}>Save Settings</button>
        <button type="button" className="button" disabled={!formId || !emailEnabled} onClick={onTest}>Send Test</button>
      </footer>
      )}
    </aside>
  );
}
