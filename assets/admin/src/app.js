import apiFetch from '@wordpress/api-fetch';
import { useEffect, useState } from '@wordpress/element';

import Canvas from './components/Canvas';
import FormLibrary from './components/FormLibrary';
import InspectorPanel from './components/InspectorPanel';
import SaveBar from './components/SaveBar';
import {
  addFieldToContainer,
  addFieldToRoot,
  deleteField,
  duplicateField,
  findField,
  moveField,
  updateField,
  updateFieldSettings,
} from './schema';
import { defaultSettings, loadSettings, saveSettings, sendTestEmail } from './settings-api';
import { listForms, loadForm } from './forms-api';

export default function App() {
  const [title, setTitle] = useState('Untitled Form');
  const [schema, setSchema] = useState({ version: 1, fields: [] });
  const [formId, setFormId] = useState(null);
  const [status, setStatus] = useState('');
  const [selectedFieldId, setSelectedFieldId] = useState(null);
  const [settings, setSettings] = useState(defaultSettings());
  const [settingsStatus, setSettingsStatus] = useState('');
  const [forms, setForms] = useState([]);

  useEffect(() => {
    if (!formId) {
      setSettings(defaultSettings());
      return;
    }
    loadSettings(formId).then(setSettings).catch(() => setSettings(defaultSettings()));
  }, [formId]);

  useEffect(() => {
    listForms().then((items) => setForms(Array.isArray(items) ? items : [])).catch(() => setForms([]));
  }, []);

  const handleRootDrop = (type) => {
    if (!type) {
      return;
    }
    setSchema((currentSchema) => {
      const next = addFieldToRoot(currentSchema, type);
      setSelectedFieldId(next.fields[next.fields.length - 1]?.id || null);
      return next;
    });
  };

  const selectedField = selectedFieldId ? findField(schema, selectedFieldId) : null;

  const resetDraft = () => {
    setTitle('Untitled Form');
    setSchema({ version: 1, fields: [] });
    setFormId(null);
    setSelectedFieldId(null);
    setStatus('');
    setSettings(defaultSettings());
  };

  const selectForm = async (nextFormId) => {
    setStatus('Loading...');
    try {
      const form = await loadForm(nextFormId);
      setFormId(form.id);
      setTitle(form.title || 'Untitled Form');
      setSchema(form.schema || { version: 1, fields: [] });
      setSelectedFieldId(null);
      setStatus('Loaded');
    } catch (error) {
      setStatus(error?.message || 'Load failed');
    }
  };

  const handleContainerDrop = (containerId, columnIndex, type) => {
    if (!type) {
      return;
    }
    setSchema((currentSchema) => addFieldToContainer(currentSchema, containerId, columnIndex, type));
  };

  const saveForm = async () => {
    setStatus('Saving...');

    try {
      const savedForm = await apiFetch({
        path: formId === null ? '/bs23-form-builder/v1/forms' : `/bs23-form-builder/v1/forms/${formId}`,
        method: formId === null ? 'POST' : 'PUT',
        data: { title, schema },
      });

      if (savedForm?.id) {
        setFormId(savedForm.id);
      }
      setStatus('Saved');
    } catch (error) {
      setStatus(error?.message || 'Save failed');
    }
  };

  const saveFormSettings = async () => {
    if (!formId) {
      return;
    }
    setSettingsStatus('Saving...');
    try {
      const saved = await saveSettings(formId, settings);
      setSettings(saved);
      setSettingsStatus('Settings saved');
    } catch (error) {
      setSettingsStatus(error?.message || 'Settings save failed');
    }
  };

  const testEmail = async () => {
    if (!formId) {
      return;
    }
    setSettingsStatus('Sending test...');
    try {
      const response = await sendTestEmail(formId, settings);
      setSettingsStatus(response?.sent ? 'Test email sent' : 'Test email failed');
    } catch (error) {
      setSettingsStatus(error?.message || 'Test email failed');
    }
  };

  return (
    <div className="bs23-builder">
      <FormLibrary
        activeFormId={formId}
        forms={forms}
        onNewForm={resetDraft}
        onSelectForm={selectForm}
      />
      <SaveBar
        onSave={saveForm}
        onTitleChange={setTitle}
        status={status}
        title={title}
      />
      <main className="bs23-builder__workspace">
        <Canvas
          fields={schema.fields}
          onDropContainer={handleContainerDrop}
          onDropRoot={handleRootDrop}
          onSelectField={setSelectedFieldId}
          selectedFieldId={selectedFieldId}
        />
        <div className="bs23-builder__side">
          <InspectorPanel
            field={selectedField}
            fields={schema.fields}
            formId={formId}
            onAddField={handleRootDrop}
            onChangeSettings={setSettings}
            onDelete={(fieldId) => {
              setSchema((currentSchema) => deleteField(currentSchema, fieldId));
              setSelectedFieldId(null);
            }}
            onDuplicate={(fieldId) => setSchema((currentSchema) => duplicateField(currentSchema, fieldId))}
            onMove={(fieldId, direction) => setSchema((currentSchema) => moveField(currentSchema, fieldId, direction))}
            onSaveSettings={saveFormSettings}
            onSendTest={testEmail}
            onUpdate={(fieldId, updates) => setSchema((currentSchema) => updateField(currentSchema, fieldId, updates))}
            onUpdateSettings={(fieldId, updates) => setSchema((currentSchema) => updateFieldSettings(currentSchema, fieldId, updates))}
            settings={settings}
            settingsStatus={settingsStatus}
          />
        </div>
      </main>
    </div>
  );
}
