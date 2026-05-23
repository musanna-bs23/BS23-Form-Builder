import { FIELD_GROUPS } from '../fields';

export default function Palette({ onAddField = () => {} }) {
  const handleDragStart = (event, type) => {
    event.dataTransfer.setData('text/plain', type);
  };

  return (
    <aside className="bs23-builder__palette" aria-label="Field palette">
      {FIELD_GROUPS.map((group) => (
        <section className="bs23-palette-group" key={group.id}>
          <h2 className="bs23-palette-group__title">{group.label}</h2>
          <div className="bs23-palette-group__items">
            {group.fields.map(([type, label]) => (
              <button
                className="bs23-palette-item"
                draggable
                key={type}
                onDoubleClick={() => onAddField(type)}
                onDragStart={(event) => handleDragStart(event, type)}
                type="button"
              >
                {label}
              </button>
            ))}
          </div>
        </section>
      ))}
    </aside>
  );
}
