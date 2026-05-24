import FieldSettingsPanel from './FieldSettingsPanel';
import SettingsPanel from './SettingsPanel';

const tabs = [
  { id: 'fields', label: 'Edit Fields' },
  { id: 'settings', label: 'Settings & Integrations' },
  { id: 'entries', label: 'Entries' },
];

export default function InspectorPanel({
  activeTab,
  field,
  fields,
  formId,
  onChangeTab,
  onChangeSettings,
  onDelete,
  onDuplicate,
  onMove,
  onSaveSettings,
  onSendTest,
  onUpdate,
  onUpdateSettings,
  settings,
  settingsStatus,
}) {
  return (
    <aside className="bs23-inspector" aria-label="Builder inspector">
      <div className="bs23-inspector__tabs" role="tablist" aria-label="Builder tools">
        {tabs.map((tab) => (
          <button
            aria-selected={activeTab === tab.id}
            className={activeTab === tab.id ? 'is-active' : ''}
            key={tab.id}
            onClick={() => onChangeTab(tab.id)}
            role="tab"
            type="button"
          >
            {tab.label}
          </button>
        ))}
      </div>

      <div className="bs23-inspector__body">
        {activeTab === 'fields' && (
          <FieldSettingsPanel
            field={field}
            fields={fields}
            onDelete={onDelete}
            onDuplicate={onDuplicate}
            onMove={onMove}
            onUpdate={onUpdate}
            onUpdateSettings={onUpdateSettings}
          />
        )}
        {activeTab === 'settings' && (
          <SettingsPanel
            formId={formId}
            mode="all"
            onChange={onChangeSettings}
            onSave={onSaveSettings}
            onTest={onSendTest}
            settings={settings}
            status={settingsStatus}
          />
        )}
        {activeTab === 'entries' && (
          <div className="bs23-inspector__empty">
            Entries will appear after submissions are received.
          </div>
        )}
      </div>
    </aside>
  );
}
