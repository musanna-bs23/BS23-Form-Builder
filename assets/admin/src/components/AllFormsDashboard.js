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

export default function AllFormsDashboard({ forms, onDeleteForm }) {
  const [openMenu, setOpenMenu] = useState(null);
  const [query, setQuery] = useState('');
  const filteredForms = useMemo(() => {
    const needle = query.trim().toLowerCase();
    if (!needle) {
      return forms;
    }

    return forms.filter((form) => (
      String(form.id).includes(needle) || String(form.title || '').toLowerCase().includes(needle)
    ));
  }, [forms, query]);
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
              <span>BS23 FORMS PRO</span>
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
            <span>Total Submissions</span>
            <strong>{totalEntries.toLocaleString()}</strong>
            <small>{forms.length} forms collecting</small>
          </article>
          <article>
            <span>This Month</span>
            <strong>{monthEntries.toLocaleString()}</strong>
            <small>Submissions in current month</small>
          </article>
          <article>
            <span>Today</span>
            <strong>{todayEntries.toLocaleString()}</strong>
            <small>New submissions today</small>
          </article>
        </section>

        <section className="bs23-forms-dashboard__toolbar">
          <div className="bs23-forms-dashboard__filters" aria-label="Form filters">
            <button type="button">All time</button>
            <button type="button">Today</button>
            <button type="button">Last 7 days</button>
            <button type="button">Last 30 days</button>
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
