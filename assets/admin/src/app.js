import apiFetch from '@wordpress/api-fetch';
import { useEffect, useState } from '@wordpress/element';

import Canvas from './components/Canvas';
import Palette from './components/Palette';
import SaveBar from './components/SaveBar';
import SettingsPanel from './components/SettingsPanel';
import { addFieldToContainer, addFieldToRoot } from './schema';
import { defaultSettings, loadSettings, saveSettings, sendTestEmail } from './settings-api';

export default function App() {
  const [title, setTitle] = useState('Untitled Form');
  const [schema, setSchema] = useState({ version: 1, fields: [] });
  const [formId, setFormId] = useState(null);
  const [status, setStatus] = useState('');
  const [settings, setSettings] = useState(defaultSettings());
  const [settingsStatus, setSettingsStatus] = useState('');

  useEffect(() => {
    loadSettings(formId).then(setSettings).catch(() => setSettings(defaultSettings()));
  }, [formId]);

  const handleRootDrop = (type) => {
    if (!type) {
      return;
    }
    setSchema((currentSchema) => addFieldToRoot(currentSchema, type));
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
        />
        <div className="bs23-builder__side">
          <Palette />
          <SettingsPanel
            formId={formId}
            onChange={setSettings}
            onSave={saveFormSettings}
            onTest={testEmail}
            settings={settings}
            status={settingsStatus}
          />
        </div>
      </main>
    </div>
  );
}
