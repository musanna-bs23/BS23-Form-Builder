import { useMemo, useState } from '@wordpress/element';

import { FIELD_GROUPS } from '../fields';

const tabs = [
  { id: 'recent', label: 'Recent' },
  { id: 'general', label: 'General' },
  { id: 'advanced', label: 'Advanced' },
  { id: 'container', label: 'Container' },
];

const recentTypes = ['name', 'email', 'text', 'textarea', 'dropdown', 'container_2'];

function getGroupFields(activeTab) {
  if (activeTab === 'recent') {
    return FIELD_GROUPS.flatMap((group) => group.fields).filter(([type]) => recentTypes.includes(type));
  }

  return FIELD_GROUPS.find((group) => group.id === activeTab)?.fields || [];
}

export default function BlockInserter({ onAddField, onClose }) {
  const [activeTab, setActiveTab] = useState('general');
  const [query, setQuery] = useState('');
  const fields = useMemo(() => {
    const needle = query.trim().toLowerCase();
    return getGroupFields(activeTab).filter(([, label]) => !needle || label.toLowerCase().includes(needle));
  }, [activeTab, query]);

  const addField = (type) => {
    onAddField(type);
    onClose();
  };

  return (
    <div className="bs23-block-inserter" aria-label="Block inserter">
      <div className="bs23-block-inserter__search">
        <input
          autoFocus
          onChange={(event) => setQuery(event.target.value)}
          placeholder="Search for a block"
          type="search"
          value={query}
        />
        <button aria-label="Close block inserter" onClick={onClose} type="button">x</button>
      </div>
      <div className="bs23-block-inserter__tabs" role="tablist" aria-label="Block categories">
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
      <div className="bs23-block-inserter__grid">
        {fields.map(([type, label]) => (
          <button
            className="bs23-block-inserter__item"
            draggable
            key={type}
            onClick={() => addField(type)}
            onDoubleClick={() => addField(type)}
            onDragStart={(event) => event.dataTransfer.setData('text/plain', type)}
            type="button"
          >
            <span aria-hidden="true">{label.slice(0, 1)}</span>
            {label}
          </button>
        ))}
      </div>
    </div>
  );
}
