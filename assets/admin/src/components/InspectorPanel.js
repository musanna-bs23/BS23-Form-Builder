import { useState } from '@wordpress/element';

import FieldSettingsPanel from './FieldSettingsPanel';
import Palette from './Palette';
import SettingsPanel from './SettingsPanel';

const tabs = [
  { id: 'fields', label: 'Fields' },
  { id: 'field', label: 'Field' },
  { id: 'form', label: 'Form' },
  { id: 'email', label: 'Email' },
  { id: 'style', label: 'Style' },
  { id: 'security', label: 'Security' },
];

export default function InspectorPanel({
  field,
  fields,
  formId,
  onAddField,
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
  const [activeTab, setActiveTab] = useState('fields');

  return (
    <aside className="bs23-inspector" aria-label="Builder inspector">
      <div className="bs23-inspector__tabs" role="tablist" aria-label="Builder tools">
        {tabs.map((tab) => (
          <button
            aria-selected={activeTab === tab.id}
            className={activeTab === tab.id ? 'is-active' : ''}
            key={tab.id}
            onClick={() => setActiveTab(tab.id)}
            role="tab"
            type="button"
          >
            {tab.label}
          </button>
        ))}
      </div>

      <div className="bs23-inspector__body">
        {activeTab === 'fields' && <Palette onAddField={onAddField} />}
        {activeTab === 'field' && (
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
        {activeTab === 'form' && (
          <SettingsPanel
            formId={formId}
            mode="form"
            onChange={onChangeSettings}
            onSave={onSaveSettings}
            onTest={onSendTest}
            settings={settings}
            status={settingsStatus}
          />
        )}
        {activeTab === 'email' && (
          <SettingsPanel
            formId={formId}
            mode="email"
            onChange={onChangeSettings}
            onSave={onSaveSettings}
            onTest={onSendTest}
            settings={settings}
            status={settingsStatus}
          />
        )}
        {activeTab === 'style' && (
          <SettingsPanel
            formId={formId}
            mode="style"
            onChange={onChangeSettings}
            onSave={onSaveSettings}
            onTest={onSendTest}
            settings={settings}
            status={settingsStatus}
          />
        )}
        {activeTab === 'security' && (
          <SettingsPanel
            formId={formId}
            mode="security"
            onChange={onChangeSettings}
            onSave={onSaveSettings}
            onTest={onSendTest}
            settings={settings}
            status={settingsStatus}
          />
        )}
      </div>
    </aside>
  );
}
