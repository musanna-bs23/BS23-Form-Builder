import { useMemo, useState } from '@wordpress/element';

function adminPageUrl(page, params = {}) {
  const base = window.bs23FormBuilder?.adminUrl || 'admin.php';
  const url = new URL(base, 'https://bs23.local');
  url.searchParams.set('page', page);
  Object.entries(params).forEach(([key, value]) => {
    if (value !== undefined && value !== null && value !== '') {
      url.searchParams.set(key, value);
    }
  });

  return `${url.pathname}${url.search}`;
}

function formatDate(value) {
  if (!value) {
    return 'Not available';
  }
  const date = new Date(value);
  if (Number.isNaN(date.getTime())) {
    return 'Not available';
  }

  return date.toLocaleDateString(undefined, { month: 'short', day: 'numeric', year: 'numeric' });
}

function StatIcon({ type }) {
  const paths = {
    submissions: (
      <>
        <path d="M4 9h16" />
        <path d="M7 9l2.2-4h5.6L17 9" />
        <path d="M6 9v8a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V9" />
        <path d="M9 14h6" />
      </>
    ),
    month: (
      <>
        <path d="M7 3v4" />
        <path d="M17 3v4" />
        <path d="M4 8h16" />
        <rect x="4" y="5" width="16" height="15" rx="2" />
        <path d="M8 12h2" />
        <path d="M12 12h2" />
        <path d="M16 12h1" />
        <path d="M8 16h2" />
        <path d="M12 16h2" />
      </>
    ),
    today: (
      <>
        <path d="M4 17l5-5 3 3 7-7" />
        <path d="M15 8h4v4" />
        <path d="M4 20h16" />
      </>
    ),
  };

  return (
    <span className="bs23-stat-card__icon" aria-hidden="true">
      <svg viewBox="0 0 24 24" focusable="false">
        {paths[type]}
      </svg>
    </span>
  );
}

export default function AllFormsDashboard({ forms, onDeleteForm }) {
  const [openMenu, setOpenMenu] = useState(null);
  const [query, setQuery] = useState('');
  const [dateFilter, setDateFilter] = useState('all');
  const filteredForms = useMemo(() => {
    const needle = query.trim().toLowerCase();
    return forms.filter((form) => {
      const matchesQuery = !needle || String(form.id).includes(needle) || String(form.title || '').toLowerCase().includes(needle);
      if (!matchesQuery) {
        return false;
      }
      if (dateFilter === 'today') {
        return Number(form.entries_today || 0) > 0;
      }
      if (dateFilter === 'month' || dateFilter === 'last30') {
        return Number(form.entries_this_month || 0) > 0;
      }
      return true;
    });
  }, [forms, query, dateFilter]);
  const totalEntries = forms.reduce((sum, form) => sum + Number(form.entries_count || 0), 0);
  const monthEntries = forms.reduce((sum, form) => sum + Number(form.entries_this_month || 0), 0);
  const todayEntries = forms.reduce((sum, form) => sum + Number(form.entries_today || 0), 0);

  const actionLinks = (form) => [
    ['Edit', adminPageUrl('bs23-form-builder-add-new', { form_id: form.id })],
    ['Settings', adminPageUrl('bs23-form-builder-add-new', { form_id: form.id, inspector: 'email' })],
    ['Entries', adminPageUrl('bs23-form-builder-entries', { form_id: form.id })],
    ['Preview', adminPageUrl('bs23-form-builder-add-new', { form_id: form.id, preview: 1 })],
  ];

  return (
    <main className="bs23-forms-page">
      <header className="bs23-forms-hero">
        <div className="bs23-forms-hero__inner">
          <div className="bs23-forms-hero__title">
            <span className="bs23-forms-hero__mark" aria-hidden="true" />
            <div>
              <span>BS23 Forms Pro</span>
              <h1>All Forms</h1>
            </div>
          </div>
          <div className="bs23-forms-hero__actions">
            <a className="bs23-forms-hero__templates" href={adminPageUrl('bs23-form-builder-templates')}>Templates</a>
            <a className="bs23-forms-hero__add" href={adminPageUrl('bs23-form-builder-add-new')}>Add New Form</a>
          </div>
        </div>
      </header>

      <div className="bs23-forms-dashboard">
        <section className="bs23-forms-dashboard__stats" aria-label="Forms overview">
          <article>
            <StatIcon type="submissions" />
            <span>Total Submissions</span>
            <strong>{totalEntries.toLocaleString()}</strong>
            <small>{forms.length} forms collecting</small>
          </article>
          <article>
            <StatIcon type="month" />
            <span>This Month</span>
            <strong>{monthEntries.toLocaleString()}</strong>
            <small>Submissions in current month</small>
          </article>
          <article>
            <StatIcon type="today" />
            <span>Today</span>
            <strong>{todayEntries.toLocaleString()}</strong>
            <small>New submissions today</small>
          </article>
        </section>

        <section className="bs23-forms-dashboard__toolbar">
          <div className="bs23-forms-dashboard__filters" aria-label="Form filters">
            <button className={dateFilter === 'all' ? 'is-active' : ''} onClick={() => setDateFilter('all')} type="button">All time</button>
            <button className={dateFilter === 'today' ? 'is-active' : ''} onClick={() => setDateFilter('today')} type="button">Today</button>
            <button className={dateFilter === 'month' ? 'is-active' : ''} onClick={() => setDateFilter('month')} type="button">This month</button>
            <button className={dateFilter === 'last30' ? 'is-active' : ''} onClick={() => setDateFilter('last30')} type="button">Last 30 days</button>
          </div>
          <label className="bs23-forms-dashboard__search">
            <span>Search forms</span>
            <input
              aria-label="Search by form name or ID"
              onChange={(event) => setQuery(event.target.value)}
              placeholder="Search by form name or ID..."
              type="search"
              value={query}
            />
          </label>
          <a className="bs23-forms-dashboard__export" href={adminPageUrl('bs23-form-builder-entries')}>Export</a>
        </section>

        <section className="bs23-forms-table" aria-label="All forms">
          <header className="bs23-forms-table__row bs23-forms-table__row--head">
            <span>ID</span>
            <span>Title</span>
            <span>Shortcode</span>
            <span>Entries</span>
            <span>Actions</span>
          </header>
          {filteredForms.length === 0 ? (
            <div className="bs23-forms-table__empty">No forms found.</div>
          ) : filteredForms.map((form) => (
            <article className="bs23-forms-table__row" key={form.id}>
              <strong className="bs23-forms-table__id">#{form.id}</strong>
              <div className="bs23-forms-table__title">
                <strong>{form.title}</strong>
                <span className={`bs23-forms-table__status is-${form.status || 'publish'}`}>{form.status === 'draft' ? 'draft' : 'published'}</span>
                <small>Created {formatDate(form.created_at)}</small>
              </div>
              <code>{form.shortcode || `[bs23_form id="${form.id}"]`}</code>
              <a className="bs23-forms-table__entries" href={adminPageUrl('bs23-form-builder-entries', { form_id: form.id })}>
                <strong>{Number(form.entries_count || 0).toLocaleString()}</strong>
                <small>+{Number(form.entries_today || 0).toLocaleString()} today</small>
              </a>
              <div className="bs23-forms-table__actions">
                <button type="button" onClick={() => setOpenMenu(openMenu === form.id ? null : form.id)}>Actions</button>
                {openMenu === form.id && (
                  <div className="bs23-forms-table__menu">
                    {actionLinks(form).map(([label, href]) => (
                      <a href={href} key={label}>{label}</a>
                    ))}
                    <button
                      className="is-danger"
                      onClick={() => {
                        onDeleteForm(form.id);
                        setOpenMenu(null);
                      }}
                      type="button"
                    >
                      Delete
                    </button>
                  </div>
                )}
              </div>
            </article>
          ))}
          <footer className="bs23-forms-table__footer">
            <span>Showing <strong>1-{filteredForms.length}</strong> of <strong>{forms.length}</strong> forms</span>
            <div>
              <button type="button" disabled>Prev</button>
              <button className="is-active" type="button">1</button>
              <button type="button">2</button>
              <button type="button">Next</button>
            </div>
          </footer>
        </section>
      </div>
    </main>
  );
}
