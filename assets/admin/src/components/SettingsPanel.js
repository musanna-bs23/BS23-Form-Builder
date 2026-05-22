export default function SettingsPanel({ formId, settings, status, onChange, onSave, onTest }) {
  const notification = settings.notification;
  const confirmation = settings.confirmation;

  const updateNotification = (key, value) => onChange({
    ...settings,
    notification: { ...notification, [key]: value },
  });
  const updateConfirmation = (key, value) => onChange({
    ...settings,
    confirmation: { ...confirmation, [key]: value },
  });

  return (
    <aside className="bs23-settings-panel">
      <header>
        <h2>Form Settings</h2>
        <span>{status}</span>
      </header>
      <section>
        <h3>Email notification</h3>
        <label className="bs23-settings-panel__toggle">
          <input
            type="checkbox"
            checked={notification.enabled}
            onChange={(event) => updateNotification('enabled', event.target.checked)}
          />
          Send admin notification
        </label>
        <label>Send to
          <input value={notification.to} onChange={(event) => updateNotification('to', event.target.value)} />
        </label>
        <label>Subject
          <input value={notification.subject} onChange={(event) => updateNotification('subject', event.target.value)} />
        </label>
        <label>Message
          <textarea rows="5" value={notification.message} onChange={(event) => updateNotification('message', event.target.value)} />
        </label>
        <label>Reply-to field
          <input value={notification.reply_to} placeholder="email" onChange={(event) => updateNotification('reply_to', event.target.value)} />
        </label>
      </section>
      <section>
        <h3>Confirmation</h3>
        <label>Success message
          <textarea rows="3" value={confirmation.message} onChange={(event) => updateConfirmation('message', event.target.value)} />
        </label>
        <label>Redirect URL
          <input value={confirmation.redirect_url} onChange={(event) => updateConfirmation('redirect_url', event.target.value)} />
        </label>
      </section>
      <footer>
        <button type="button" className="button button-primary" disabled={!formId} onClick={onSave}>Save Settings</button>
        <button type="button" className="button" disabled={!formId} onClick={onTest}>Send Test</button>
      </footer>
    </aside>
  );
}
