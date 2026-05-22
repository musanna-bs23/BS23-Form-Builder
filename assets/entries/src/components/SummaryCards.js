const cards = [
  ['total', 'Total entries'],
  ['today', 'Today'],
  ['week', 'This week'],
  ['last_submission', 'Last submission'],
];

export default function SummaryCards({ summary, loading }) {
  return (
    <section className="bs23-summary">
      {cards.map(([key, label]) => (
        <article className="bs23-summary__card" key={key}>
          <span>{label}</span>
          <strong>{loading ? '...' : formatValue(summary?.[key])}</strong>
        </article>
      ))}
    </section>
  );
}

function formatValue(value) {
  if (!value) {
    return '0';
  }
  return String(value);
}
