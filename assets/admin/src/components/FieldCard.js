export default function FieldCard({ field, selected, onSelect }) {
  return (
    <article className={`bs23-field-card${selected ? ' is-selected' : ''}`}>
      <strong className="bs23-field-card__label">{field.label}</strong>
      <span className="bs23-field-card__type">{field.type}</span>
      <button type="button" onClick={() => onSelect(field.id)}>Edit</button>
    </article>
  );
}
