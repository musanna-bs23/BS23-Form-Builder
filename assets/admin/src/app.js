import apiFetch from '@wordpress/api-fetch';
import { useState } from '@wordpress/element';

import Canvas from './components/Canvas';
import Palette from './components/Palette';
import SaveBar from './components/SaveBar';
import { addFieldToContainer, addFieldToRoot } from './schema';

export default function App() {
  const [title, setTitle] = useState('Untitled Form');
  const [schema, setSchema] = useState({ version: 1, fields: [] });
  const [formId, setFormId] = useState(null);
  const [status, setStatus] = useState('');

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
        <Palette />
      </main>
    </div>
  );
}
