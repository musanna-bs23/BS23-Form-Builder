export default function FieldCard({ field, selected, onSelect, onDelete, onDuplicate, onMove }) {
  const activate = () => onSelect(field.id);

  return (
    <article
      className={`bs23-field-card${selected ? ' is-selected' : ''}`}
      onClick={activate}
      onKeyDown={(event) => {
        if (event.key === 'Enter' || event.key === ' ') {
          event.preventDefault();
          activate();
        }
      }}
      role="button"
      tabIndex="0"
    >
      <div className="bs23-field-card__preview">
        <label>{field.label}</label>
        <div className="bs23-field-card__input">{field.settings?.placeholder || `Enter ${field.label}`}</div>
      </div>
      <div className="bs23-field-card__toolbar" aria-label={`${field.label} field actions`}>
        <button aria-label={`Move ${field.label} up`} onClick={(event) => { event.stopPropagation(); onMove(field.id, 'up'); }} type="button">↑</button>
        <button aria-label={`Edit ${field.label}`} onClick={(event) => { event.stopPropagation(); activate(); }} type="button">✎</button>
        <button aria-label={`Duplicate ${field.label}`} onClick={(event) => { event.stopPropagation(); onDuplicate(field.id); }} type="button">⧉</button>
        <button aria-label={`Delete ${field.label}`} onClick={(event) => { event.stopPropagation(); onDelete(field.id); }} type="button">×</button>
      </div>
    </article>
  );
}
