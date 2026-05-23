import { render, screen, fireEvent } from '@testing-library/react';
import SummaryCards from '../components/SummaryCards';
import EntriesTable from '../components/EntriesTable';
import EntriesToolbar from '../components/EntriesToolbar';
import EntryDrawer from '../components/EntryDrawer';

test('summary cards render metrics', () => {
  render(<SummaryCards summary={{ total: 10, today: 2, week: 7, last_submission: '2026-05-22' }} loading={false} />);

  expect(screen.getByText('Total entries')).not.toBeNull();
  expect(screen.getByText('10')).not.toBeNull();
});

test('entries table renders empty state', () => {
  render(<EntriesTable entries={[]} loading={false} selected={[]} onSelect={() => {}} onOpen={() => {}} pagination={{ total: 0, page: 1, total_pages: 1 }} />);

  expect(screen.getByText('No entries match these filters.')).not.toBeNull();
});

test('toolbar emits search filter changes', () => {
  const onChange = jest.fn();
  render(<EntriesToolbar filters={{ search: '', form_id: '', date_from: '', date_to: '', order: 'DESC' }} forms={[]} onChange={onChange} onBulkDelete={() => {}} selectedCount={0} />);

  fireEvent.change(screen.getByLabelText('Search entries'), { target: { value: 'email' } });

  expect(onChange).toHaveBeenCalledWith({ search: 'email' });
});

test('drawer renders decoded entry data', () => {
  render(<EntryDrawer entry={{ id: 3, form_title: 'Contact', created_at: '2026-05-22', user_id: 0, user_ip: '127.0.0.1', user_agent: 'Test', entry_data: { email: 'a@example.com' } }} onClose={() => {}} onDelete={() => {}} />);

  expect(screen.getByText('Entry #3')).not.toBeNull();
  expect(screen.getByText('a@example.com')).not.toBeNull();
});

test('drawer renders uploaded file links', () => {
  render(<EntryDrawer entry={{ id: 4, form_title: 'Contact', created_at: '2026-05-22', user_id: 0, user_ip: '127.0.0.1', user_agent: 'Test', entry_data: { resume: { name: 'resume.pdf', url: 'https://example.com/resume.pdf', type: 'application/pdf' } } }} onClose={() => {}} onDelete={() => {}} />);

  const link = screen.getByText('resume.pdf');
  expect(link.getAttribute('href')).toBe('https://example.com/resume.pdf');
});
