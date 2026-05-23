import { useEffect, useMemo, useState } from '@wordpress/element';
import { bulkDelete, deleteEntry, exportEntries, getEntry, getSummary, listEntries } from './api';
import SummaryCards from './components/SummaryCards';
import TrendChart from './components/TrendChart';
import FormBreakdown from './components/FormBreakdown';
import EntriesToolbar from './components/EntriesToolbar';
import EntriesTable from './components/EntriesTable';
import EntryDrawer from './components/EntryDrawer';

const initialFilters = {
  search: '',
  form_id: '',
  date_from: '',
  date_to: '',
  order: 'DESC',
  page: 1,
  per_page: 20,
};

export default function App() {
  const [filters, setFilters] = useState(initialFilters);
  const [summary, setSummary] = useState(null);
  const [entries, setEntries] = useState([]);
  const [pagination, setPagination] = useState({ total: 0, page: 1, total_pages: 1 });
  const [selected, setSelected] = useState([]);
  const [activeEntry, setActiveEntry] = useState(null);
  const [loading, setLoading] = useState(true);
  const [notice, setNotice] = useState('');
  const [error, setError] = useState('');

  const load = async () => {
    setLoading(true);
    setError('');
    try {
      const [nextSummary, entryResponse] = await Promise.all([
        getSummary(filters),
        listEntries(filters),
      ]);
      setSummary(nextSummary);
      setEntries(entryResponse.entries || []);
      setPagination({
        total: entryResponse.total || 0,
        page: entryResponse.page || 1,
        total_pages: entryResponse.total_pages || 1,
      });
      setSelected([]);
    } catch (requestError) {
      setError(requestError?.message || 'Could not load entries.');
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    load();
  }, [JSON.stringify(filters)]);

  const forms = useMemo(() => summary?.forms || [], [summary]);

  const updateFilters = (next) => {
    setFilters((current) => ({ ...current, ...next, page: next.page || 1 }));
  };

  const openEntry = async (id) => {
    setActiveEntry(await getEntry(id));
  };

  const removeEntry = async (id) => {
    await deleteEntry(id);
    setActiveEntry(null);
    setNotice('Entry deleted.');
    load();
  };

  const removeSelected = async () => {
    if (selected.length === 0) {
      return;
    }
    const response = await bulkDelete(selected);
    setNotice(`${response.deleted || 0} entries deleted.`);
    load();
  };

  const exportCsv = async () => {
    const response = await exportEntries(filters);
    const blob = new Blob([response.csv || ''], { type: 'text/csv;charset=utf-8' });
    const url = URL.createObjectURL(blob);
    const link = document.createElement('a');
    link.href = url;
    link.download = response.filename || 'bs23-form-entries.csv';
    link.click();
    URL.revokeObjectURL(url);
  };

  return (
    <div className="bs23-entries">
      <header className="bs23-entries__header">
        <div>
          <h1>Entries Command Center</h1>
          <p>Submission intelligence, fast filtering, and export-ready records.</p>
        </div>
        <div className="bs23-entries__actions">
          <button type="button" onClick={load}>Refresh</button>
          <button type="button" className="is-primary" onClick={exportCsv}>Export CSV</button>
        </div>
      </header>

      {notice && <div className="bs23-entries__notice">{notice}</div>}
      {error && <div className="bs23-entries__error">{error}</div>}

      <SummaryCards summary={summary} loading={loading} />

      <section className="bs23-entries__analytics">
        <TrendChart trend={summary?.trend || []} />
        <FormBreakdown forms={forms} />
      </section>

      <section className="bs23-entries__surface">
        <EntriesToolbar
          filters={filters}
          forms={forms}
          onBulkDelete={removeSelected}
          onChange={updateFilters}
          selectedCount={selected.length}
        />
        <EntriesTable
          entries={entries}
          loading={loading}
          onOpen={openEntry}
          onSelect={setSelected}
          pagination={pagination}
          selected={selected}
        />
      </section>

      <EntryDrawer entry={activeEntry} onClose={() => setActiveEntry(null)} onDelete={removeEntry} />
    </div>
  );
}
