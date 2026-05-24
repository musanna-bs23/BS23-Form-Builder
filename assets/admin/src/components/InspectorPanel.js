import { useState } from '@wordpress/element';

import FieldSettingsPanel from './FieldSettingsPanel';
import Palette from './Palette';
import SettingsPanel from './SettingsPanel';

const sideTabs = [
  { id: 'fields', label: 'Input Fields' },
  { id: 'customize', label: 'Input Customization' },
];

export default function InspectorPanel({
  activeTab,
  field,
  fields,
  formId,
  onAddField,
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
  const [sideTab, setSideTab] = useState('fields');

  return (
    <aside className="bs23-inspector" aria-label="Builder inspector">
      {activeTab === 'editor' && (
      <div className="bs23-inspector__tabs" role="tablist" aria-label="Builder tools">
        {sideTabs.map((tab) => (
          <button
            aria-selected={sideTab === tab.id}
            className={sideTab === tab.id ? 'is-active' : ''}
            key={tab.id}
            onClick={() => setSideTab(tab.id)}
            role="tab"
            type="button"
          >
            {tab.label}
          </button>
        ))}
      </div>
      )}

      <div className="bs23-inspector__body">
        {activeTab === 'editor' && sideTab === 'fields' && (
          <Palette onAddField={onAddField} />
        )}
        {activeTab === 'editor' && sideTab === 'customize' && (
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
