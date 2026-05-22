export default function FieldCard({ field }) {
  return (
    <article className="bs23-field-card">
      <strong className="bs23-field-card__label">{field.label}</strong>
      <span className="bs23-field-card__type">{field.type}</span>
    </article>
  );
}
