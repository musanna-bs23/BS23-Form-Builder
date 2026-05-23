export default function TrendChart({ trend }) {
  const max = Math.max(1, ...trend.map((item) => item.total));

  return (
    <article className="bs23-panel">
      <header>
        <h2>Submission trend</h2>
        <span>Recent 14 days</span>
      </header>
      <div className="bs23-trend">
        {trend.map((item) => (
          <div className="bs23-trend__bar" key={item.date}>
            <span style={{ height: `${Math.max(6, (item.total / max) * 100)}%` }} />
            <small>{item.total}</small>
          </div>
        ))}
      </div>
    </article>
  );
}
