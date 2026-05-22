export default function EntriesToolbar({ filters, forms, onChange, onBulkDelete, selectedCount }) {
  return (
    <div className="bs23-toolbar">
      <input
        aria-label="Search entries"
        placeholder="Search entries"
        type="search"
        value={filters.search}
        onChange={(event) => onChange({ search: event.target.value })}
      />
      <select
        aria-label="Filter by form"
        value={filters.form_id}
        onChange={(event) => onChange({ form_id: event.target.value })}
      >
        <option value="">All forms</option>
        {forms.map((form) => <option key={form.form_id} value={form.form_id}>{form.form_title}</option>)}
      </select>
      <input aria-label="Date from" type="date" value={filters.date_from} onChange={(event) => onChange({ date_from: event.target.value })} />
      <input aria-label="Date to" type="date" value={filters.date_to} onChange={(event) => onChange({ date_to: event.target.value })} />
      <select aria-label="Sort entries" value={filters.order} onChange={(event) => onChange({ order: event.target.value })}>
        <option value="DESC">Newest</option>
        <option value="ASC">Oldest</option>
      </select>
      <button type="button" disabled={selectedCount === 0} onClick={onBulkDelete}>
        Delete selected{selectedCount ? ` (${selectedCount})` : ''}
      </button>
    </div>
  );
}
