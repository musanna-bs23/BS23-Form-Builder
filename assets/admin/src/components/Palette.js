import { useMemo, useState } from '@wordpress/element';

import { FIELD_GROUPS } from '../fields';

export default function Palette({ onAddField = () => {} }) {
  const [search, setSearch] = useState('');
  const [openGroups, setOpenGroups] = useState({ general: true, advanced: false, container: false });
  const groups = useMemo(() => {
    const needle = search.trim().toLowerCase();
    return FIELD_GROUPS.map((group) => ({
      ...group,
      fields: group.fields.filter(([, label]) => !needle || label.toLowerCase().includes(needle)),
    }));
  }, [search]);

  const handleDragStart = (event, type) => {
    event.dataTransfer.setData('text/plain', type);
  };

  return (
    <aside className="bs23-builder__palette" aria-label="Field palette">
      <label className="bs23-builder__palette-search">
        <span>Search fields</span>
        <input
          onChange={(event) => setSearch(event.target.value)}
          placeholder="Search (press '/' to focus)"
          type="search"
          value={search}
        />
      </label>
      {groups.map((group) => (
        <section className="bs23-palette-group" key={group.id}>
          <button
            className="bs23-palette-group__title"
            onClick={() => setOpenGroups((current) => ({ ...current, [group.id]: !current[group.id] }))}
            type="button"
          >
            <span>{group.label}</span>
            <span aria-hidden="true">{openGroups[group.id] ? '^' : 'v'}</span>
          </button>
          {openGroups[group.id] && (
          <div className="bs23-palette-group__items">
            {group.fields.length === 0 ? (
              <p className="bs23-palette-group__empty">No matching fields</p>
            ) : group.fields.map(([type, label]) => (
              <button
                className="bs23-palette-item"
                draggable
                key={type}
                onClick={() => onAddField(type)}
                onDoubleClick={() => onAddField(type)}
                onDragStart={(event) => handleDragStart(event, type)}
                type="button"
              >
                <span aria-hidden="true">{label.slice(0, 1)}</span>
                {label}
              </button>
            ))}
          </div>
          )}
        </section>
      ))}
    </aside>
  );
}
